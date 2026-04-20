<?php
defined('BASEPATH') or exit('No direct script access allowed');

class VALSAL_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MVALSAL_MyRep');
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

        $data['title'] = 'VALSAL MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['isReady'] = $this->MVALSAL_MyRep->valsalTablesReady();
        $data['docReady'] = $this->MVALSAL_MyRep->valsalDocumentTablesReady();
        $data['canApprove'] = $this->isApprover();
        $data['cityOptions'] = $this->MVALSAL_MyRep->getCityOptions();
        $data['eligibleClusterOptions'] = $this->MVALSAL_MyRep->getEligibleClusterOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MVALSAL_MyRep->getValsalRows($selectedCity, $selectedStatus)
            : [];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('VALSAL_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveValsal()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MVALSAL_MyRep->valsalTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel VALSAL MyRep belum tersedia. Jalankan query database flow baru terlebih dahulu.');
            redirect('VALSAL_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $valsalDate = $this->normalizeDate($this->input->post('valsal_date'));
        $homepassValsal = (int) $this->normalizeNumber($this->input->post('homepass_valsal'));
        $remarkValsal = trim((string) $this->input->post('remark_valsal'));

        if ($clusterId <= 0 || $homepassValsal <= 0) {
            $this->session->set_flashdata('error', 'Cluster dan homepass VALSAL wajib diisi.');
            redirect('VALSAL_MyRep');
            return;
        }

        $cluster = $this->MVALSAL_MyRep->getValsalCandidateById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Cluster belum memenuhi syarat untuk proses VALSAL.');
            redirect('VALSAL_MyRep');
            return;
        }

        if (!empty($cluster['id_valsal'])) {
            $this->session->set_flashdata('error', 'Cluster ini sudah pernah diproses di modul VALSAL.');
            redirect('VALSAL_MyRep');
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        $statusValsal = $valsalDate ? 'DONE' : 'DRAFT';
        $result = $this->MVALSAL_MyRep->createValsal($clusterId, [
            'valsal_date' => $valsalDate,
            'homepass_valsal' => $homepassValsal,
            'status_valsal' => $statusValsal,
            'remark_valsal' => $remarkValsal !== '' ? $remarkValsal : null,
            'created_by' => $userId,
            'updated_by' => $userId,
        ], [
            'status_current' => $this->buildCurrentStatus($valsalDate, $statusValsal),
            'updated_by' => $userId,
        ]);

        if (!$result) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data VALSAL.');
            redirect('VALSAL_MyRep');
            return;
        }

        $this->session->set_flashdata('success', 'Data VALSAL berhasil ditambahkan.');
        redirect('VALSAL_MyRep');
    }

    public function updateValsal()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MVALSAL_MyRep->valsalTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel VALSAL MyRep belum tersedia. Jalankan query database flow baru terlebih dahulu.');
            redirect('VALSAL_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $valsalDate = $this->normalizeDate($this->input->post('valsal_date'));
        $homepassValsal = (int) $this->normalizeNumber($this->input->post('homepass_valsal'));
        $statusValsal = strtoupper(trim((string) $this->input->post('status_valsal')));
        $remarkValsal = trim((string) $this->input->post('remark_valsal'));

        if ($clusterId <= 0 || $homepassValsal <= 0) {
            $this->session->set_flashdata('error', 'Data update VALSAL belum lengkap.');
            redirect('VALSAL_MyRep');
            return;
        }

        $allowedStatuses = ['DRAFT', 'SUBMITTED', 'ON REVIEW', 'APPROVED', 'REJECTED', 'DONE'];
        if (!in_array($statusValsal, $allowedStatuses, true)) {
            $statusValsal = $valsalDate ? 'DONE' : 'DRAFT';
        }

        $userId = (int) $this->session->userdata('id_user');
        $result = $this->MVALSAL_MyRep->updateValsal($clusterId, [
            'valsal_date' => $valsalDate,
            'homepass_valsal' => $homepassValsal,
            'status_valsal' => $statusValsal,
            'remark_valsal' => $remarkValsal !== '' ? $remarkValsal : null,
            'updated_by' => $userId,
        ], [
            'status_current' => $this->buildCurrentStatus($valsalDate, $statusValsal),
            'updated_by' => $userId,
        ]);

        if (!$result) {
            $this->session->set_flashdata('error', 'Gagal memperbarui data VALSAL.');
            redirect('VALSAL_MyRep');
            return;
        }

        $this->session->set_flashdata('success', 'Data VALSAL berhasil diperbarui.');
        redirect('VALSAL_MyRep');
    }

    public function uploadDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Session habis. Silakan login ulang.');
                return;
            }
            redirect('Auth');
            return;
        }

        if (!$this->MVALSAL_MyRep->valsalTablesReady() || !$this->MVALSAL_MyRep->valsalDocumentTablesReady()) {
            $this->handleUploadError('Tabel dokumen VALSAL belum tersedia.', 'VALSAL_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $context = $this->MVALSAL_MyRep->getValsalDocumentContext($clusterId);
        if ($clusterId <= 0 || empty($context['id_doc_item'])) {
            $this->handleUploadError('Konfigurasi dokumen SND KASAR belum ditemukan.', 'VALSAL_MyRep');
            return;
        }

        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;
        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->handleUploadError('File SND KASAR wajib dipilih.', 'VALSAL_MyRep');
            return;
        }

        $uploadDir = './uploads/myrep_valsal/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = '';
        $filePath = '';
        if (!$isNoDocumentRequired) {
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($context['doc_name'] ?? 'SND_KASAR'));
            $fileName = 'VALSAL_' . $clusterId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
                'max_size' => 30720,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $this->handleUploadError(strip_tags($this->upload->display_errors()), 'VALSAL_MyRep');
                return;
            }

            $fileData = $this->upload->data();
            $fileName = $fileData['file_name'];
            $filePath = 'uploads/myrep_valsal/' . $fileData['file_name'];
        }

        $fileId = $this->MVALSAL_MyRep->saveValsalFileUpload($clusterId, [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($fileId <= 0) {
            $this->handleUploadError('Dokumen SND KASAR gagal disimpan.', 'VALSAL_MyRep');
            return;
        }

        $this->handleUploadSuccess(
            $isNoDocumentRequired ? 'Dokumen SND KASAR ditandai tidak dibutuhkan dan dikirim ke review.' : 'Dokumen SND KASAR berhasil diupload.',
            'VALSAL_MyRep'
        );
    }

    public function approveDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen VALSAL.');
            redirect('VALSAL_MyRep');
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        if ($fileId <= 0) {
            $this->session->set_flashdata('error', 'Dokumen VALSAL tidak ditemukan.');
            redirect('VALSAL_MyRep');
            return;
        }

        $result = $this->MVALSAL_MyRep->updateValsalFileStatus($fileId, [
            'status_file' => 'APPROVED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen SND KASAR berhasil di-approve.' : 'Gagal approve dokumen SND KASAR.');
        redirect('VALSAL_MyRep');
    }

    public function rejectDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen VALSAL.');
            redirect('VALSAL_MyRep');
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        if ($fileId <= 0) {
            $this->session->set_flashdata('error', 'Dokumen VALSAL tidak ditemukan.');
            redirect('VALSAL_MyRep');
            return;
        }

        $result = $this->MVALSAL_MyRep->updateValsalFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen SND KASAR berhasil di-reject.' : 'Gagal reject dokumen SND KASAR.');
        redirect('VALSAL_MyRep');
    }

    public function previewDocument($fileId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $file = $this->MVALSAL_MyRep->getValsalFileById((int) $fileId);
        if (empty($file) || empty($file['file_path'])) {
            show_404();
            return;
        }

        $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file['file_path']);
        if (!is_file($fullPath)) {
            show_404();
            return;
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        header('Content-Type: ' . ($mimeMap[$extension] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    private function buildCurrentStatus($valsalDate, $statusValsal)
    {
        $statusValsal = strtoupper(trim((string) $statusValsal));

        if ($statusValsal === 'REJECTED') {
            return 'REJECTED';
        }

        if (!empty($valsalDate) || $statusValsal === 'DONE' || $statusValsal === 'APPROVED') {
            return 'VALSAL';
        }

        return 'BAK';
    }

    private function normalizeDate($date)
    {
        $date = trim((string) $date);
        return $date !== '' ? $date : null;
    }

    private function normalizeNumber($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^\d,.\-]/', '', (string) $value);
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }

    private function isApprover()
    {
        return $this->session->userdata('lokasi_user') === 'HO'
            || $this->session->userdata('nama_level') === 'Super Admin';
    }

    private function isAjaxRequest()
    {
        return $this->input->is_ajax_request()
            || strtolower((string) $this->input->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    private function jsonResponse($status, $message, $redirectUrl = '')
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => $status,
                'message' => $message,
                'redirect_url' => $redirectUrl,
            ]));
    }

    private function handleUploadSuccess($message, $redirectPath)
    {
        if ($this->isAjaxRequest()) {
            $this->jsonResponse(true, $message, base_url($redirectPath));
            return;
        }

        $this->session->set_flashdata('success', $message);
        redirect($redirectPath);
    }

    private function handleUploadError($message, $redirectPath)
    {
        if ($this->isAjaxRequest()) {
            $this->jsonResponse(false, $message, base_url($redirectPath));
            return;
        }

        $this->session->set_flashdata('error', $message);
        redirect($redirectPath);
    }
}
