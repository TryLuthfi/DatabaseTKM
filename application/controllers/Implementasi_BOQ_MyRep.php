<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Implementasi_BOQ_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MImplementasi_BOQ_MyRep');
        $this->load->library('upload');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedStatus = strtoupper(trim((string) $this->input->get('status')));

        $data['title'] = 'Implementasi BOQ MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['isReady'] = $this->MImplementasi_BOQ_MyRep->tablesReady();
        $data['cityOptions'] = $this->MImplementasi_BOQ_MyRep->getCityOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MImplementasi_BOQ_MyRep->getRows($selectedCity, $selectedStatus)
            : [];
        $data['summary'] = $this->MImplementasi_BOQ_MyRep->getDashboardSummary($data['clusterRows']);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Implementasi_BOQ_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function detail($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $cluster = $this->MImplementasi_BOQ_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data implementasi cluster tidak ditemukan.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $compareRows = $this->MImplementasi_BOQ_MyRep->getBaselineCompareRows($clusterId);
        $historyMap = $this->MImplementasi_BOQ_MyRep->getProgressHistoryMap($clusterId);

        $data['title'] = 'Detail Implementasi BOQ MyRep';
        $data['cluster'] = $cluster;
        $data['compareRows'] = $compareRows;
        $data['historyMap'] = $historyMap;

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Implementasi_BOQ_MyRep/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveProgress()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MImplementasi_BOQ_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel implementasi BOQ belum tersedia.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $baselineItemId = (int) $this->input->post('id_boq_baseline_item');
        $progressDate = $this->normalizeDate($this->input->post('progress_date'));
        $qtyProgress = $this->normalizeNumber($this->input->post('qty_progress'));
        $statusProgress = strtoupper(trim((string) $this->input->post('status_progress')));
        $remarkProgress = trim((string) $this->input->post('remark_progress'));

        if ($clusterId <= 0 || $baselineItemId <= 0) {
            $this->session->set_flashdata('error', 'Cluster atau item implementasi tidak valid.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        if ($progressDate === null || $qtyProgress <= 0) {
            $this->session->set_flashdata('error', 'Tanggal progress dan qty progress wajib diisi.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        if (!in_array($statusProgress, ['ON PROGRESS', 'DONE'], true)) {
            $statusProgress = 'ON PROGRESS';
        }

        if (empty($_FILES['photos']['name']) || !is_array($_FILES['photos']['name'])) {
            $this->session->set_flashdata('error', 'Minimal 1 foto evidence wajib diupload.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $photoRows = $this->uploadProgressPhotos($clusterId, $baselineItemId);
        if ($photoRows === false) {
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        if (empty($photoRows)) {
            $this->session->set_flashdata('error', 'Foto evidence tidak berhasil diupload.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        $result = $this->MImplementasi_BOQ_MyRep->createProgressEntry($clusterId, $baselineItemId, [
            'progress_date' => $progressDate,
            'qty_progress' => $qtyProgress,
            'status_progress' => $statusProgress,
            'remark_progress' => $remarkProgress,
            'created_by' => $userId,
            'updated_by' => $userId,
        ], $photoRows);

        $this->session->set_flashdata($result > 0 ? 'success' : 'error', $result > 0 ? 'Progress implementasi berhasil disimpan.' : 'Gagal menyimpan progress implementasi.');
        redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
    }

    private function uploadProgressPhotos($clusterId, $baselineItemId)
    {
        $clusterId = (int) $clusterId;
        $baselineItemId = (int) $baselineItemId;
        $files = $_FILES['photos'] ?? null;
        if (empty($files) || !is_array($files['name'])) {
            return [];
        }

        $uploadDir = './uploads/myrep_boq_progress/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedRows = [];
        $totalFiles = count($files['name']);

        for ($i = 0; $i < $totalFiles; $i++) {
            if (empty($files['name'][$i])) {
                continue;
            }

            $_FILES['single_progress_photo'] = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];

            $extension = pathinfo((string) $files['name'][$i], PATHINFO_EXTENSION);
            $fileName = 'BOQ_PROGRESS_' . $clusterId . '_' . $baselineItemId . '_' . date('YmdHis') . '_' . ($i + 1) . '.' . $extension;
            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size' => 30720,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('single_progress_photo')) {
                $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
                return false;
            }

            $fileData = $this->upload->data();
            $uploadedRows[] = [
                'file_name' => $fileData['file_name'],
                'file_path' => 'uploads/myrep_boq_progress/' . $fileData['file_name'],
                'caption' => '',
            ];
        }

        return $uploadedRows;
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function normalizeNumber($value)
    {
        $normalized = preg_replace('/[^0-9.,-]/', '', (string) $value);
        if ($normalized === '' || $normalized === null) {
            return 0;
        }

        $hasComma = strpos($normalized, ',') !== false;
        $dotCount = substr_count($normalized, '.');

        if ($hasComma) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif ($dotCount > 1) {
            $normalized = str_replace('.', '', $normalized);
        }

        return (float) $normalized;
    }
}
