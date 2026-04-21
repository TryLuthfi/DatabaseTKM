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
        $valsalDate = $this->normalizeDate($this->input->post('valsal_date')) ?: date('Y-m-d');
        $homepassValsal = (int) $this->normalizeNumber($this->input->post('homepass_valsal'));
        $remarkValsal = trim((string) $this->input->post('remark_valsal'));
        $docReady = $this->MVALSAL_MyRep->valsalDocumentTablesReady();

        if ($clusterId <= 0 || $homepassValsal <= 0) {
            $this->session->set_flashdata('error', 'Cluster dan homepass VALSAL wajib diisi.');
            redirect('VALSAL_MyRep');
            return;
        }

        if ($docReady && empty($_FILES['create_file']['name']) && (int) $this->input->post('create_is_document_not_required') !== 1) {
            $this->session->set_flashdata('error', 'Dokumen SND KASAR wajib diupload saat input VALSAL baru.');
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
        $statusValsal = 'ON REVIEW';
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

        if ($docReady) {
            $context = $this->MVALSAL_MyRep->getValsalDocumentContext($clusterId);
            if (empty($context['id_doc_item'])) {
                $this->MVALSAL_MyRep->deleteValsalByCluster($clusterId);
                $this->MVALSAL_MyRep->updateClusterStatusOnly($clusterId, 'BAK', $userId);
                $this->session->set_flashdata('error', 'Konfigurasi dokumen SND KASAR belum ditemukan.');
                redirect('VALSAL_MyRep');
                return;
            }

            $uploadResult = $this->storeValsalUploadFile($clusterId, $context, 'create_file');
            if (!$uploadResult['status']) {
                $this->MVALSAL_MyRep->deleteValsalByCluster($clusterId);
                $this->MVALSAL_MyRep->updateClusterStatusOnly($clusterId, 'BAK', $userId);
                $this->session->set_flashdata('error', $uploadResult['message']);
                redirect('VALSAL_MyRep');
                return;
            }

            $fileId = $this->MVALSAL_MyRep->saveValsalFileUpload($clusterId, [
                'file_name' => $uploadResult['file_name'],
                'file_path' => $uploadResult['file_path'],
                'is_document_not_required' => (int) $this->input->post('create_is_document_not_required') === 1 ? 1 : 0,
                'status_file' => 'UPLOADED',
                'remark' => trim((string) $this->input->post('create_doc_remark')),
                'uploaded_by' => $userId,
            ]);

            if ($fileId <= 0) {
                $this->deleteStoredFile($uploadResult['file_path']);
                $this->MVALSAL_MyRep->deleteValsalByCluster($clusterId);
                $this->MVALSAL_MyRep->updateClusterStatusOnly($clusterId, 'BAK', $userId);
                $this->session->set_flashdata('error', 'Dokumen SND KASAR gagal disimpan.');
                redirect('VALSAL_MyRep');
                return;
            }
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
        $existing = $this->MVALSAL_MyRep->getValsalByClusterId($clusterId);
        if (empty($existing)) {
            $this->session->set_flashdata('error', 'Data cluster VALSAL tidak ditemukan.');
            redirect('VALSAL_MyRep');
            return;
        }

        $valsalDate = $this->normalizeDate($this->input->post('valsal_date')) ?: date('Y-m-d');
        $homepassValsal = (int) $this->normalizeNumber($this->input->post('homepass_valsal'));
        $remarkValsal = trim((string) $this->input->post('remark_valsal'));

        if ($clusterId <= 0 || $homepassValsal <= 0) {
            $this->session->set_flashdata('error', 'Data update VALSAL belum lengkap.');
            redirect('VALSAL_MyRep');
            return;
        }

        $documentStatus = '';
        if ($this->MVALSAL_MyRep->valsalDocumentTablesReady()) {
            $documentContext = $this->MVALSAL_MyRep->getValsalDocumentContext($clusterId);
            $documentStatus = (string) ($documentContext['status_file'] ?? '');
        }
        $statusValsal = $this->resolveValsalStatus($documentStatus, (string) ($existing['status_valsal'] ?? 'ON REVIEW'));

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

        $uploadResult = $this->storeValsalUploadFile($clusterId, $context, 'file');
        if (!$uploadResult['status']) {
            $this->handleUploadError($uploadResult['message'], 'VALSAL_MyRep');
            return;
        }

        $fileId = $this->MVALSAL_MyRep->saveValsalFileUpload($clusterId, [
            'file_name' => $uploadResult['file_name'],
            'file_path' => $uploadResult['file_path'],
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($fileId <= 0) {
            $this->handleUploadError('Dokumen SND KASAR gagal disimpan.', 'VALSAL_MyRep');
            return;
        }

        $this->MVALSAL_MyRep->updateValsalStatusByCluster($clusterId, 'ON REVIEW', 'BAK', (int) $this->session->userdata('id_user'));

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

        $file = $this->MVALSAL_MyRep->getValsalFileById($fileId);
        if (empty($file['id_myrep_cluster'])) {
            $this->session->set_flashdata('error', 'Data cluster dokumen VALSAL tidak ditemukan.');
            redirect('VALSAL_MyRep');
            return;
        }

        $result = $this->MVALSAL_MyRep->updateValsalFileStatus($fileId, [
            'status_file' => 'APPROVED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($result) {
            $this->MVALSAL_MyRep->updateValsalStatusByCluster((int) $file['id_myrep_cluster'], 'DONE', 'VALSAL', (int) $this->session->userdata('id_user'));
        }

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

        $file = $this->MVALSAL_MyRep->getValsalFileById($fileId);
        if (empty($file['id_myrep_cluster'])) {
            $this->session->set_flashdata('error', 'Data cluster dokumen VALSAL tidak ditemukan.');
            redirect('VALSAL_MyRep');
            return;
        }

        $result = $this->MVALSAL_MyRep->updateValsalFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($result) {
            $this->MVALSAL_MyRep->updateValsalStatusByCluster((int) $file['id_myrep_cluster'], 'REJECTED', 'REJECTED', (int) $this->session->userdata('id_user'));
        }

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

        if ($statusValsal === 'DONE' || $statusValsal === 'APPROVED') {
            return 'VALSAL';
        }

        return 'BAK';
    }

    private function resolveValsalStatus($documentStatus, $fallbackStatus = 'ON REVIEW')
    {
        $documentStatus = strtoupper(trim((string) $documentStatus));
        $fallbackStatus = strtoupper(trim((string) $fallbackStatus));

        if ($documentStatus === 'APPROVED') {
            return 'DONE';
        }

        if ($documentStatus === 'REJECTED') {
            return 'REJECTED';
        }

        if ($documentStatus === 'UPLOADED') {
            return 'ON REVIEW';
        }

        return $fallbackStatus !== '' ? $fallbackStatus : 'ON REVIEW';
    }

    private function storeValsalUploadFile($clusterId, $context, $fieldName)
    {
        $isNoDocumentRequired = (int) $this->input->post($fieldName === 'create_file' ? 'create_is_document_not_required' : 'is_document_not_required') === 1;
        if ($isNoDocumentRequired) {
            return [
                'status' => true,
                'message' => '',
                'file_name' => '',
                'file_path' => '',
            ];
        }

        if (empty($_FILES[$fieldName]['name'])) {
            return [
                'status' => false,
                'message' => 'File SND KASAR wajib dipilih.',
                'file_name' => '',
                'file_path' => '',
            ];
        }

        $uploadDir = './uploads/myrep_valsal/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
        $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($context['doc_name'] ?? 'SND_KASAR'));
        $fileName = 'VALSAL_' . (int) $clusterId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

        $config = [
            'upload_path' => $uploadDir,
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
            'max_size' => 30720,
            'file_name' => $fileName,
            'overwrite' => true,
        ];

        $this->upload->initialize($config);
        if (!$this->upload->do_upload($fieldName)) {
            return [
                'status' => false,
                'message' => strip_tags($this->upload->display_errors()),
                'file_name' => '',
                'file_path' => '',
            ];
        }

        $fileData = $this->upload->data();
        return [
            'status' => true,
            'message' => '',
            'file_name' => (string) $fileData['file_name'],
            'file_path' => 'uploads/myrep_valsal/' . $fileData['file_name'],
        ];
    }

    private function deleteStoredFile($filePath)
    {
        $filePath = trim((string) $filePath);
        if ($filePath === '') {
            return;
        }

        $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
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
