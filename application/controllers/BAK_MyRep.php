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
        $baOpenDate = $this->normalizeDate($this->input->post('ba_open_date'));
        $bakDate = $this->normalizeDate($this->input->post('bak_date'));
        $homepassBak = (int) $this->normalizeNumber($this->input->post('homepass_bak'));
        $remarkBak = trim((string) $this->input->post('remark_bak'));

        if ($targetId <= 0 || $clusterName === '' || $homepassBak <= 0) {
            $this->session->set_flashdata('error', 'Target, nama cluster, dan homepass BAK wajib diisi.');
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
        $currentStatus = $this->buildCurrentStatus($baOpenDate, $bakDate, 'DRAFT');
        $statusBak = $bakDate ? 'DONE' : ($baOpenDate ? 'SUBMITTED' : 'DRAFT');

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
        $baOpenDate = $this->normalizeDate($this->input->post('ba_open_date'));
        $bakDate = $this->normalizeDate($this->input->post('bak_date'));
        $homepassBak = (int) $this->normalizeNumber($this->input->post('homepass_bak'));
        $statusBak = strtoupper(trim((string) $this->input->post('status_bak')));
        $remarkBak = trim((string) $this->input->post('remark_bak'));

        if ($clusterId <= 0 || $targetId <= 0 || $clusterName === '' || $homepassBak <= 0) {
            $this->session->set_flashdata('error', 'Data update BAK belum lengkap.');
            redirect('BAK_MyRep');
            return;
        }

        $allowedStatuses = ['DRAFT', 'SUBMITTED', 'ON REVIEW', 'APPROVED', 'REJECTED', 'DONE'];
        if (!in_array($statusBak, $allowedStatuses, true)) {
            $statusBak = $bakDate ? 'DONE' : ($baOpenDate ? 'SUBMITTED' : 'DRAFT');
        }

        $target = $this->MBAK_MyRep->getTargetById($targetId);
        if (empty($target)) {
            $this->session->set_flashdata('error', 'Target kota MyRep tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
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

        $uploadDir = './uploads/myrep_bak/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = '';
        $filePath = '';
        if (!$isNoDocumentRequired) {
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($context['doc_name'] ?? 'BA_OPEN'));
            $fileName = 'BAK_' . $clusterId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
                'max_size' => 30720,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $this->handleUploadError(strip_tags($this->upload->display_errors()), 'BAK_MyRep');
                return;
            }

            $fileData = $this->upload->data();
            $fileName = $fileData['file_name'];
            $filePath = 'uploads/myrep_bak/' . $fileData['file_name'];
        }

        $fileId = $this->MBAK_MyRep->saveBakFileUpload($clusterId, [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($fileId <= 0) {
            $this->handleUploadError('Dokumen BA OPEN gagal disimpan.', 'BAK_MyRep');
            return;
        }

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

        $result = $this->MBAK_MyRep->updateBakFileStatus($fileId, [
            'status_file' => 'APPROVED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

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

        $result = $this->MBAK_MyRep->updateBakFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

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

        if (!empty($bakDate) || $statusBak === 'DONE' || $statusBak === 'APPROVED') {
            return 'BAK';
        }

        if (!empty($baOpenDate)) {
            return 'BA OPEN';
        }

        return 'DRAFT';
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
