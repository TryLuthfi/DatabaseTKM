<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DRM_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MDRM_MyRep');
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
        $data['canApprove'] = $this->isApprover();
        $data['documentRows'] = $data['docReady']
            ? $this->MDRM_MyRep->getDrmDocumentRows($clusterId)
            : [];

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

        $allowedStatuses = ['DRAFT', 'SUBMITTED', 'ON REVIEW', 'APPROVED', 'REJECTED', 'DONE'];
        if (!in_array($statusDrm, $allowedStatuses, true)) {
            $statusDrm = $drmDate ? 'DONE' : 'DRAFT';
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
        redirect('DRM_MyRep');
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

        $allowedStatuses = ['DRAFT', 'SUBMITTED', 'ON REVIEW', 'APPROVED', 'REJECTED', 'DONE'];
        if (!in_array($statusDrm, $allowedStatuses, true)) {
            $statusDrm = $drmDate ? 'DONE' : 'DRAFT';
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
        redirect('DRM_MyRep');
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

    private function isApprover()
    {
        return $this->session->userdata('lokasi_user') === 'HO'
            || $this->session->userdata('nama_level') === 'Super Admin';
    }
}
