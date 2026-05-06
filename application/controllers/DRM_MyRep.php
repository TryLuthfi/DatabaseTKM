<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DRM_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MDRM_MyRep');
        $this->load->model('MMyRep_Cleanup');
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

        $data['title'] = 'DRM MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['isReady'] = $this->MDRM_MyRep->drmTablesReady();
        $data['docReady'] = $this->MDRM_MyRep->drmDocumentTablesReady();
        $data['boqReady'] = $this->MDRM_MyRep->drmBoqTablesReady();
        $data['cityOptions'] = $this->MDRM_MyRep->getCityOptions();
        $data['eligibleClusterOptions'] = $this->MDRM_MyRep->getEligibleClusterOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MDRM_MyRep->getDrmRows($selectedCity, $selectedStatus)
            : [];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('DRM_MyRep/index', $data);
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
            redirect('DRM_MyRep');
            return;
        }

        $cluster = $this->MDRM_MyRep->getDrmByClusterId($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data cluster DRM tidak ditemukan.');
            redirect('DRM_MyRep');
            return;
        }

        $data['title'] = 'Detail DRM MyRep';
        $data['cluster'] = $cluster;
        $data['docReady'] = $this->MDRM_MyRep->drmDocumentTablesReady();
        $data['boqReady'] = $this->MDRM_MyRep->drmBoqTablesReady();
        $data['canApprove'] = $this->isApprover();
        $data['documentRows'] = $data['docReady']
            ? $this->MDRM_MyRep->getDrmDocumentRows($clusterId)
            : [];
        $data['boqHeader'] = $data['boqReady'] ? $this->MDRM_MyRep->getDrmBoqHeader($clusterId) : [];
        $data['boqItems'] = $data['boqReady'] ? $this->MDRM_MyRep->getDrmBoqItems($clusterId) : [];
        $data['boqBaselineHeader'] = $data['boqReady'] ? $this->MDRM_MyRep->getBoqBaselineHeader($clusterId) : [];
        $data['boqBaselineItems'] = $data['boqReady'] ? $this->MDRM_MyRep->getBoqBaselineItems($clusterId) : [];
        $data['apdBoqFile'] = $data['docReady'] ? $this->MDRM_MyRep->getApdBoqDocumentFile($clusterId) : [];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('DRM_MyRep/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveDrm()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MDRM_MyRep->drmTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel DRM MyRep belum tersedia.');
            redirect('DRM_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $drmDate = $this->normalizeDate($this->input->post('drm_date'));
        $homepassDrm = (int) $this->normalizeNumber($this->input->post('homepass_drm'));
        $statusDrm = strtoupper(trim((string) $this->input->post('status_drm')));
        $remark = trim((string) $this->input->post('remark_drm'));

        if ($clusterId <= 0 || $homepassDrm <= 0) {
            $this->session->set_flashdata('error', 'Cluster dan homepass DRM wajib diisi.');
            redirect('DRM_MyRep');
            return;
        }

        $cluster = $this->MDRM_MyRep->getDrmCandidateById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Cluster belum memenuhi syarat untuk proses DRM.');
            redirect('DRM_MyRep');
            return;
        }

        if (!empty($cluster['id_drm'])) {
            $this->session->set_flashdata('error', 'Cluster ini sudah pernah diproses di modul DRM.');
            redirect('DRM_MyRep');
            return;
        }

        $allowedStatuses = ['WAITING DOC', 'WAITING APPROVE', 'COMPLETE', 'REJECTED'];
        if (!in_array($statusDrm, $allowedStatuses, true)) {
            $statusDrm = $drmDate ? 'WAITING DOC' : 'DRAFT';
        }

        $userId = (int) $this->session->userdata('id_user');
        $result = $this->MDRM_MyRep->createDrm($clusterId, [
            'drm_date' => $drmDate,
            'homepass_drm' => $homepassDrm,
            'status_drm' => $statusDrm,
            'remark_drm' => $remark !== '' ? $remark : null,
            'created_by' => $userId,
            'updated_by' => $userId,
        ], [
            'status_current' => $this->buildCurrentStatus($drmDate, $statusDrm),
            'updated_by' => $userId,
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Data DRM berhasil ditambahkan.' : 'Gagal menyimpan data DRM.');
        redirect($result ? ('DRM_MyRep/detail/' . $clusterId) : 'DRM_MyRep');
    }

    public function updateDrm()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MDRM_MyRep->drmTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel DRM MyRep belum tersedia.');
            redirect('DRM_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $drmId = (int) $this->input->post('id_drm');
        $drmDate = $this->normalizeDate($this->input->post('drm_date'));
        $homepassDrm = (int) $this->normalizeNumber($this->input->post('homepass_drm'));
        $statusDrm = strtoupper(trim((string) $this->input->post('status_drm')));
        $remark = trim((string) $this->input->post('remark_drm'));

        if ($clusterId <= 0 || $drmId <= 0 || $homepassDrm <= 0) {
            $this->session->set_flashdata('error', 'Data update DRM belum lengkap.');
            redirect('DRM_MyRep');
            return;
        }

        $allowedStatuses = ['WAITING DOC', 'WAITING APPROVE', 'COMPLETE', 'REJECTED'];
        if (!in_array($statusDrm, $allowedStatuses, true)) {
            $statusDrm = $drmDate ? 'WAITING DOC' : 'DRAFT';
        }

        $userId = (int) $this->session->userdata('id_user');
        $result = $this->MDRM_MyRep->updateDrm($clusterId, $drmId, [
            'drm_date' => $drmDate,
            'homepass_drm' => $homepassDrm,
            'status_drm' => $statusDrm,
            'remark_drm' => $remark !== '' ? $remark : null,
            'updated_by' => $userId,
        ], [
            'status_current' => $this->buildCurrentStatus($drmDate, $statusDrm),
            'updated_by' => $userId,
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Data DRM berhasil diperbarui.' : 'Gagal memperbarui data DRM.');
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function uploadDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MDRM_MyRep->drmDocumentTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel dokumen DRM belum tersedia.');
            redirect('DRM_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $docItemId = (int) $this->input->post('id_doc_item');
        $detail = $this->MDRM_MyRep->getDrmDocumentDetail($clusterId, $docItemId);
        if ($clusterId <= 0 || $docItemId <= 0 || empty($detail)) {
            $this->session->set_flashdata('error', 'Konfigurasi dokumen DRM tidak ditemukan.');
            redirect('DRM_MyRep');
            return;
        }

        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;
        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->session->set_flashdata('error', 'File dokumen wajib dipilih.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $uploadDir = './uploads/myrep_drm/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = '';
        $filePath = '';
        if (!$isNoDocumentRequired) {
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($detail['doc_name'] ?? 'DRM'));
            $fileName = 'DRM_' . $clusterId . '_' . $docItemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
                'max_size' => 30720,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
                redirect('DRM_MyRep/detail/' . $clusterId);
                return;
            }

            $fileData = $this->upload->data();
            $fileName = $fileData['file_name'];
            $filePath = 'uploads/myrep_drm/' . $fileData['file_name'];
        }

        $fileId = $this->MDRM_MyRep->saveDrmFileUpload($clusterId, $docItemId, [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($fileId > 0 ? 'success' : 'error', $fileId > 0 ? 'Dokumen DRM berhasil diupload.' : 'Dokumen DRM gagal disimpan.');
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function approveDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen DRM.');
            redirect('DRM_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $fileId = (int) $this->input->post('id_doc_file');
        $result = $this->MDRM_MyRep->updateDrmFileStatus($fileId, [
            'status_file' => 'APPROVED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen DRM berhasil di-approve.' : 'Gagal approve dokumen DRM.');
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function rejectDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen DRM.');
            redirect('DRM_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $fileId = (int) $this->input->post('id_doc_file');
        $result = $this->MDRM_MyRep->updateDrmFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen DRM berhasil di-reject.' : 'Gagal reject dokumen DRM.');
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function previewDocument($fileId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $file = $this->MDRM_MyRep->getDrmFileById((int) $fileId);
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

    public function saveBoqDraft()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if (!$this->MDRM_MyRep->drmBoqTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel BOQ DRM belum tersedia.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $cluster = $this->MDRM_MyRep->getDrmByClusterId($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data cluster DRM tidak ditemukan.');
            redirect('DRM_MyRep');
            return;
        }

        $submitToHo = (int) $this->input->post('submit_to_ho') === 1;
        $items = $this->collectBoqItemsFromPost();
        if (empty($items)) {
            $this->session->set_flashdata('error', 'Minimal isi satu item BOQ dengan qty lebih dari nol.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $apdBoqFile = $this->MDRM_MyRep->getApdBoqDocumentFile($clusterId);
        $result = $this->MDRM_MyRep->saveDrmBoqDraft(
            $clusterId,
            (int) ($cluster['id_drm'] ?? 0),
            (int) ($apdBoqFile['id_doc_file'] ?? 0),
            $items,
            (int) $this->session->userdata('id_user'),
            $submitToHo
        );

        $message = $submitToHo ? 'Draft BOQ DRM berhasil dikirim ke review HO.' : 'Draft BOQ DRM berhasil disimpan.';
        $this->session->set_flashdata($result ? 'success' : 'error', $result ? $message : 'Gagal menyimpan draft BOQ DRM.');
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function saveApdBoqPackage()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if (!$this->MDRM_MyRep->drmDocumentTablesReady() || !$this->MDRM_MyRep->drmBoqTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel dokumen atau BOQ DRM belum tersedia.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $cluster = $this->MDRM_MyRep->getDrmByClusterId($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data cluster DRM tidak ditemukan.');
            redirect('DRM_MyRep');
            return;
        }

        $docDetail = $this->MDRM_MyRep->getDrmDocumentDetailByName($clusterId, 'APD BOQ');
        if (empty($docDetail['id_doc_item'])) {
            $this->session->set_flashdata('error', 'Dokumen APD BOQ tidak ditemukan.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $items = $this->collectBoqItemsFromPost();
        if (empty($items)) {
            $this->session->set_flashdata('error', 'BOQ manual wajib diisi minimal satu item dengan qty lebih dari nol.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $hasExistingFile = !empty($docDetail['id_doc_file']);
        $hasNewFile = !empty($_FILES['apd_boq_file']['name']);
        if (!$hasExistingFile && !$hasNewFile) {
            $this->session->set_flashdata('error', 'File APD BOQ wajib diupload.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        $sourceDocFileId = (int) ($docDetail['id_doc_file'] ?? 0);

        if ($hasNewFile) {
            $uploadDir = './uploads/myrep_drm/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['apd_boq_file']['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($docDetail['doc_name'] ?? 'APD_BOQ'));
            $fileName = 'DRM_' . $clusterId . '_' . (int) $docDetail['id_doc_item'] . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
                'max_size' => 30720,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('apd_boq_file')) {
                $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
                redirect('DRM_MyRep/detail/' . $clusterId);
                return;
            }

            $fileData = $this->upload->data();
            $sourceDocFileId = $this->MDRM_MyRep->saveDrmFileUpload($clusterId, (int) $docDetail['id_doc_item'], [
                'file_name' => (string) $fileData['file_name'],
                'file_path' => 'uploads/myrep_drm/' . $fileData['file_name'],
                'is_document_not_required' => 0,
                'status_file' => 'UPLOADED',
                'remark' => trim((string) $this->input->post('apd_boq_remark')),
                'uploaded_by' => $userId,
            ]);

            if ($sourceDocFileId <= 0) {
                $this->session->set_flashdata('error', 'File APD BOQ gagal disimpan.');
                redirect('DRM_MyRep/detail/' . $clusterId);
                return;
            }
        }

        $submitToHo = (int) $this->input->post('submit_to_ho') === 1;
        $result = $this->MDRM_MyRep->saveDrmBoqDraft(
            $clusterId,
            (int) ($cluster['id_drm'] ?? 0),
            $sourceDocFileId,
            $items,
            $userId,
            $submitToHo
        );

        $message = $submitToHo ? 'APD BOQ dan BOQ manual berhasil dikirim ke review HO.' : 'APD BOQ dan BOQ manual berhasil disimpan.';
        $this->session->set_flashdata($result ? 'success' : 'error', $result ? $message : 'Gagal menyimpan paket APD BOQ.');
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function approveBoq()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve BOQ DRM.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $result = $this->MDRM_MyRep->approveDrmBoq(
            $clusterId,
            (int) $this->session->userdata('id_user'),
            trim((string) $this->input->post('remark'))
        );

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'BOQ DRM berhasil di-approve, dijadikan baseline implementasi, dan dokumen DRM yang sudah ter-upload ikut di-approve.' : 'Gagal approve BOQ DRM.');
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function rejectBoq()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject BOQ DRM.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $remark = trim((string) $this->input->post('remark'));
        if ($remark === '') {
            $this->session->set_flashdata('error', 'Alasan reject BOQ wajib diisi.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $result = $this->MDRM_MyRep->rejectDrmBoq($clusterId, (int) $this->session->userdata('id_user'), $remark);
        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'BOQ DRM berhasil di-reject.' : 'Gagal reject BOQ DRM.');
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function deleteCluster()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Cluster MyRep tidak valid.');
            redirect('DRM_MyRep');
            return;
        }

        $deleted = $this->MMyRep_Cleanup->deleteWholeCluster($clusterId);
        $this->session->set_flashdata($deleted ? 'success' : 'error', $deleted ? 'Cluster MyRep beserta flow DRM dan seluruh tahap sebelumnya berhasil dihapus bersih.' : 'Gagal menghapus cluster MyRep.');
        redirect('DRM_MyRep');
    }

    private function buildCurrentStatus($drmDate, $statusDrm)
    {
        $statusDrm = strtoupper(trim((string) $statusDrm));
        if ($statusDrm === 'REJECTED') {
            return 'REJECTED';
        }

        if (!empty($drmDate) || in_array($statusDrm, ['DONE', 'APPROVED', 'ON REVIEW', 'SUBMITTED'], true)) {
            return 'DRM';
        }

        return 'RELEASED';
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

    private function collectBoqItemsFromPost()
    {
        $qtyRows = (array) $this->input->post('boq_qty');
        $masterItems = $this->MDRM_MyRep->getBoqMasterItems();
        $masterMap = [];
        foreach ($masterItems as $masterItem) {
            $masterMap[(int) ($masterItem['id_boq_item'] ?? 0)] = $masterItem;
        }
        $items = [];

        foreach ($qtyRows as $boqItemId => $qtyValue) {
            $boqItemId = (int) $boqItemId;
            if ($boqItemId <= 0 || empty($masterMap[$boqItemId])) {
                continue;
            }

            $qty = $this->normalizeNumber($qtyValue);
            $photoQty = (int) ($masterMap[$boqItemId]['default_photo_qty'] ?? 0);
            $remarksRule = strtoupper(trim((string) ($masterMap[$boqItemId]['remarks_rule'] ?? 'SESUAI ITEM')));
            $remarksRule = $remarksRule === 'SAMPLING' ? 'SAMPLING' : 'SESUAI ITEM';
            $targetFoto = $remarksRule === 'SAMPLING'
                ? $photoQty
                : (int) round($qty * $photoQty);

            if ($qty <= 0) {
                continue;
            }

            $items[] = [
                'id_boq_item' => $boqItemId,
                'qty_boq' => $qty,
                'jumlah_foto' => max($photoQty, 0),
                'remarks_rule' => $remarksRule,
                'target_foto_required' => max($targetFoto, 0),
                'item_note' => null,
            ];
        }

        return $items;
    }

    private function isApprover()
    {
        return $this->session->userdata('lokasi_user') === 'HO'
            || $this->session->userdata('nama_level') === 'Super Admin';
    }
}
