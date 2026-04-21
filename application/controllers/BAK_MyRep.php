<?php
defined('BASEPATH') or exit('No direct script access allowed');

class BAK_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MBAK_MyRep');
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

        $data['title'] = 'BAK MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['isReady'] = $this->MBAK_MyRep->bakTablesReady();
        $data['docReady'] = $this->MBAK_MyRep->bakDocumentTablesReady();
        $data['canApprove'] = $this->isApprover();
        $data['cityOptions'] = $this->MBAK_MyRep->getCityOptions();
        $data['targetOptions'] = $this->MBAK_MyRep->getTargetOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MBAK_MyRep->getBakRows($selectedCity, $selectedStatus)
            : [];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('BAK_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveCluster()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MBAK_MyRep->bakTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel BAK MyRep belum tersedia. Jalankan query database flow baru terlebih dahulu.');
            redirect('BAK_MyRep');
            return;
        }

        $targetId = (int) $this->input->post('id_target');
        $clusterName = trim((string) $this->input->post('cluster_name'));
        $clusterCode = trim((string) $this->input->post('cluster_code'));
        $today = date('Y-m-d');
        $baOpenDate = $this->normalizeDate($this->input->post('ba_open_date')) ?: $today;
        $bakDate = $this->normalizeDate($this->input->post('bak_date')) ?: $today;
        $homepassBak = (int) $this->normalizeNumber($this->input->post('homepass_bak'));
        $remarkBak = trim((string) $this->input->post('remark_bak'));
        $docReady = $this->MBAK_MyRep->bakDocumentTablesReady();

        if ($targetId <= 0 || $clusterName === '' || $homepassBak <= 0) {
            $this->session->set_flashdata('error', 'Target, nama cluster, dan homepass BAK wajib diisi.');
            redirect('BAK_MyRep');
            return;
        }

        if ($docReady && empty($_FILES['create_file']['name']) && (int) $this->input->post('create_is_document_not_required') !== 1) {
            $this->session->set_flashdata('error', 'Dokumen BA OPEN wajib diupload saat input cluster BAK baru.');
            redirect('BAK_MyRep');
            return;
        }

        $target = $this->MBAK_MyRep->getTargetById($targetId);
        if (empty($target)) {
            $this->session->set_flashdata('error', 'Target kota MyRep tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        if ($this->MBAK_MyRep->clusterExists($clusterName, $targetId)) {
            $this->session->set_flashdata('error', 'Cluster dengan target yang sama sudah pernah dibuat di modul BAK.');
            redirect('BAK_MyRep');
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        $currentStatus = $this->buildCurrentStatus($baOpenDate, $bakDate, 'ON REVIEW');
        $statusBak = 'ON REVIEW';

        $clusterId = $this->MBAK_MyRep->createClusterAndBak(
            [
                'id_target' => $targetId,
                'cluster_name' => $clusterName,
                'cluster_code' => $clusterCode !== '' ? $clusterCode : null,
                'regional_name' => $target['regional_name'] ?? null,
                'province_name' => $target['province_name'] ?? null,
                'city_name' => $target['city_name'] ?? null,
                'team_name' => $target['team_name'] ?? null,
                'chief' => $target['chief'] ?? null,
                'rpm' => $target['rpm'] ?? null,
                'sm' => $target['sm'] ?? null,
                'spv' => $target['spv'] ?? null,
                'hp_plan' => $homepassBak,
                'status_current' => $currentStatus,
                'remark_general' => $remarkBak !== '' ? $remarkBak : null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ],
            [
                'ba_open_date' => $baOpenDate,
                'bak_date' => $bakDate,
                'homepass_bak' => $homepassBak,
                'status_bak' => $statusBak,
                'remark_bak' => $remarkBak !== '' ? $remarkBak : null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]
        );

        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data BAK cluster baru.');
            redirect('BAK_MyRep');
            return;
        }

        if ($docReady) {
            $context = $this->MBAK_MyRep->getBakDocumentContext($clusterId);
            if (empty($context['id_doc_item'])) {
                $this->MBAK_MyRep->deleteClusterAndBak($clusterId);
                $this->session->set_flashdata('error', 'Konfigurasi dokumen BA OPEN belum ditemukan.');
                redirect('BAK_MyRep');
                return;
            }

            $uploadResult = $this->storeBakUploadFile($clusterId, $context, 'create_file');
            if (!$uploadResult['status']) {
                $this->MBAK_MyRep->deleteClusterAndBak($clusterId);
                $this->session->set_flashdata('error', $uploadResult['message']);
                redirect('BAK_MyRep');
                return;
            }

            $fileId = $this->MBAK_MyRep->saveBakFileUpload($clusterId, [
                'file_name' => $uploadResult['file_name'],
                'file_path' => $uploadResult['file_path'],
                'is_document_not_required' => (int) $this->input->post('create_is_document_not_required') === 1 ? 1 : 0,
                'status_file' => 'UPLOADED',
                'remark' => trim((string) $this->input->post('create_doc_remark')),
                'uploaded_by' => $userId,
            ]);

            if ($fileId <= 0) {
                $this->deleteStoredFile($uploadResult['file_path']);
                $this->MBAK_MyRep->deleteClusterAndBak($clusterId);
                $this->session->set_flashdata('error', 'Dokumen BA OPEN gagal disimpan.');
                redirect('BAK_MyRep');
                return;
            }
        }

        $this->session->set_flashdata('success', 'Cluster BAK baru berhasil ditambahkan.');
        redirect('BAK_MyRep');
    }

    public function updateCluster()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MBAK_MyRep->bakTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel BAK MyRep belum tersedia. Jalankan query database flow baru terlebih dahulu.');
            redirect('BAK_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('id_myrep_cluster');
        $targetId = (int) $this->input->post('id_target');
        $clusterName = trim((string) $this->input->post('cluster_name'));
        $clusterCode = trim((string) $this->input->post('cluster_code'));
        $existing = $this->MBAK_MyRep->getClusterById($clusterId);
        if (empty($existing)) {
            $this->session->set_flashdata('error', 'Data cluster BAK tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        $baOpenDate = $this->normalizeDate($this->input->post('ba_open_date')) ?: date('Y-m-d');
        $bakDate = $this->normalizeDate($this->input->post('bak_date')) ?: date('Y-m-d');
        $homepassBak = (int) $this->normalizeNumber($this->input->post('homepass_bak'));
        $remarkBak = trim((string) $this->input->post('remark_bak'));

        if ($clusterId <= 0 || $targetId <= 0 || $clusterName === '' || $homepassBak <= 0) {
            $this->session->set_flashdata('error', 'Data update BAK belum lengkap.');
            redirect('BAK_MyRep');
            return;
        }

        $target = $this->MBAK_MyRep->getTargetById($targetId);
        if (empty($target)) {
            $this->session->set_flashdata('error', 'Target kota MyRep tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        $documentStatus = '';
        if ($this->MBAK_MyRep->bakDocumentTablesReady()) {
            $documentContext = $this->MBAK_MyRep->getBakDocumentContext($clusterId);
            $documentStatus = (string) ($documentContext['status_file'] ?? '');
        }

        $statusBak = $this->resolveBakStatus($documentStatus, (string) ($existing['status_bak'] ?? 'ON REVIEW'));
        $result = $this->MBAK_MyRep->updateClusterAndBak(
            $clusterId,
            [
                'id_target' => $targetId,
                'cluster_name' => $clusterName,
                'cluster_code' => $clusterCode !== '' ? $clusterCode : null,
                'regional_name' => $target['regional_name'] ?? null,
                'province_name' => $target['province_name'] ?? null,
                'city_name' => $target['city_name'] ?? null,
                'team_name' => $target['team_name'] ?? null,
                'chief' => $target['chief'] ?? null,
                'rpm' => $target['rpm'] ?? null,
                'sm' => $target['sm'] ?? null,
                'spv' => $target['spv'] ?? null,
                'hp_plan' => $homepassBak,
                'status_current' => $this->buildCurrentStatus($baOpenDate, $bakDate, $statusBak),
                'remark_general' => $remarkBak !== '' ? $remarkBak : null,
                'updated_by' => $userId,
            ],
            [
                'ba_open_date' => $baOpenDate,
                'bak_date' => $bakDate,
                'homepass_bak' => $homepassBak,
                'status_bak' => $statusBak,
                'remark_bak' => $remarkBak !== '' ? $remarkBak : null,
                'updated_by' => $userId,
            ]
        );

        if (!$result) {
            $this->session->set_flashdata('error', 'Gagal memperbarui data BAK.');
            redirect('BAK_MyRep');
            return;
        }

        $this->session->set_flashdata('success', 'Data BAK berhasil diperbarui.');
        redirect('BAK_MyRep');
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

        if (!$this->MBAK_MyRep->bakTablesReady() || !$this->MBAK_MyRep->bakDocumentTablesReady()) {
            $this->handleUploadError('Tabel dokumen BAK belum tersedia.', 'BAK_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $context = $this->MBAK_MyRep->getBakDocumentContext($clusterId);
        if ($clusterId <= 0 || empty($context['id_doc_item'])) {
            $this->handleUploadError('Konfigurasi dokumen BA OPEN belum ditemukan.', 'BAK_MyRep');
            return;
        }

        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;
        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->handleUploadError('File BA OPEN wajib dipilih.', 'BAK_MyRep');
            return;
        }

        $uploadResult = $this->storeBakUploadFile($clusterId, $context, 'file');
        if (!$uploadResult['status']) {
            $this->handleUploadError($uploadResult['message'], 'BAK_MyRep');
            return;
        }

        $fileId = $this->MBAK_MyRep->saveBakFileUpload($clusterId, [
            'file_name' => $uploadResult['file_name'],
            'file_path' => $uploadResult['file_path'],
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($fileId <= 0) {
            $this->handleUploadError('Dokumen BA OPEN gagal disimpan.', 'BAK_MyRep');
            return;
        }

        $this->MBAK_MyRep->updateBakStatusByCluster($clusterId, 'ON REVIEW', 'BA OPEN', (int) $this->session->userdata('id_user'));

        $this->handleUploadSuccess(
            $isNoDocumentRequired ? 'Dokumen BA OPEN ditandai tidak dibutuhkan dan dikirim ke review.' : 'Dokumen BA OPEN berhasil diupload.',
            'BAK_MyRep'
        );
    }

    public function approveDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen BAK.');
            redirect('BAK_MyRep');
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        if ($fileId <= 0) {
            $this->session->set_flashdata('error', 'Dokumen BAK tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        $file = $this->MBAK_MyRep->getBakFileById($fileId);
        if (empty($file['id_myrep_cluster'])) {
            $this->session->set_flashdata('error', 'Data cluster dokumen BAK tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        $result = $this->MBAK_MyRep->updateBakFileStatus($fileId, [
            'status_file' => 'APPROVED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($result) {
            $this->MBAK_MyRep->updateBakStatusByCluster((int) $file['id_myrep_cluster'], 'DONE', 'BAK', (int) $this->session->userdata('id_user'));
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen BA OPEN berhasil di-approve.' : 'Gagal approve dokumen BA OPEN.');
        redirect('BAK_MyRep');
    }

    public function rejectDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen BAK.');
            redirect('BAK_MyRep');
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        if ($fileId <= 0) {
            $this->session->set_flashdata('error', 'Dokumen BAK tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        $file = $this->MBAK_MyRep->getBakFileById($fileId);
        if (empty($file['id_myrep_cluster'])) {
            $this->session->set_flashdata('error', 'Data cluster dokumen BAK tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        $result = $this->MBAK_MyRep->updateBakFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($result) {
            $this->MBAK_MyRep->updateBakStatusByCluster((int) $file['id_myrep_cluster'], 'REJECTED', 'REJECTED', (int) $this->session->userdata('id_user'));
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen BA OPEN berhasil di-reject.' : 'Gagal reject dokumen BA OPEN.');
        redirect('BAK_MyRep');
    }

    public function previewDocument($fileId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $file = $this->MBAK_MyRep->getBakFileById((int) $fileId);
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

    private function buildCurrentStatus($baOpenDate, $bakDate, $statusBak)
    {
        $statusBak = strtoupper(trim((string) $statusBak));

        if ($statusBak === 'REJECTED') {
            return 'REJECTED';
        }

        if ($statusBak === 'DONE' || $statusBak === 'APPROVED') {
            return 'BAK';
        }

        if (!empty($baOpenDate)) {
            return 'BA OPEN';
        }

        return 'DRAFT';
    }

    private function resolveBakStatus($documentStatus, $fallbackStatus = 'ON REVIEW')
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

    private function storeBakUploadFile($clusterId, $context, $fieldName)
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
                'message' => 'File BA OPEN wajib dipilih.',
                'file_name' => '',
                'file_path' => '',
            ];
        }

        $uploadDir = './uploads/myrep_bak/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
        $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($context['doc_name'] ?? 'BA_OPEN'));
        $fileName = 'BAK_' . (int) $clusterId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

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
            'file_path' => 'uploads/myrep_bak/' . $fileData['file_name'],
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
