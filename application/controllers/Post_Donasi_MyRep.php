<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Post_Donasi_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MPost_Donasi_MyRep');
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

        $data['title'] = 'Post Donasi MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['isReady'] = $this->MPost_Donasi_MyRep->tablesReady();
        $data['docReady'] = $this->MPost_Donasi_MyRep->documentTablesReady();
        $data['cityOptions'] = $this->MPost_Donasi_MyRep->getCityOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MPost_Donasi_MyRep->getRows($selectedCity, $selectedStatus)
            : [];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Post_Donasi_MyRep/index', $data);
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
            redirect('Post_Donasi_MyRep');
            return;
        }

        $cluster = $this->MPost_Donasi_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data cluster post donasi tidak ditemukan.');
            redirect('Post_Donasi_MyRep');
            return;
        }

        $data['title'] = 'Detail Post Donasi MyRep';
        $data['cluster'] = $cluster;
        $data['docReady'] = $this->MPost_Donasi_MyRep->documentTablesReady();
        $data['canApprove'] = $this->isApprover();
        $data['documentRows'] = $data['docReady']
            ? $this->MPost_Donasi_MyRep->getDocumentRows($clusterId)
            : [];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Post_Donasi_MyRep/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function uploadDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $redirectPath = $this->resolveRedirectPath($clusterId);
        $docItemId = (int) $this->input->post('id_doc_item');
        $detail = $this->MPost_Donasi_MyRep->getDocumentDetail($clusterId, $docItemId);
        if (empty($detail)) {
            $this->session->set_flashdata('error', 'Konfigurasi dokumen post donasi tidak ditemukan.');
            redirect($redirectPath);
            return;
        }

        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;
        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->session->set_flashdata('error', 'File dokumen wajib dipilih.');
            redirect($redirectPath);
            return;
        }

        $uploadDir = './uploads/myrep_post_donasi/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = '';
        $filePath = '';
        if (!$isNoDocumentRequired) {
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($detail['doc_name'] ?? 'POST_DONASI'));
            $fileName = 'POST_DONASI_' . $clusterId . '_' . $docItemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;
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
                redirect($redirectPath);
                return;
            }

            $fileData = $this->upload->data();
            $fileName = $fileData['file_name'];
            $filePath = 'uploads/myrep_post_donasi/' . $fileData['file_name'];
        }

        $fileId = $this->MPost_Donasi_MyRep->saveFileUpload($clusterId, $docItemId, [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($fileId > 0 ? 'success' : 'error', $fileId > 0 ? 'Dokumen post donasi berhasil diupload.' : 'Dokumen post donasi gagal disimpan.');
        redirect($redirectPath);
    }

    public function approveDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $redirectPath = $this->resolveRedirectPath($clusterId);

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen post donasi.');
            redirect($redirectPath);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $result = $this->MPost_Donasi_MyRep->updateFileStatus($fileId, [
            'status_file' => 'APPROVED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen berhasil di-approve.' : 'Gagal approve dokumen.');
        redirect($redirectPath);
    }

    public function rejectDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $redirectPath = $this->resolveRedirectPath($clusterId);

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen post donasi.');
            redirect($redirectPath);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $result = $this->MPost_Donasi_MyRep->updateFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen berhasil di-reject.' : 'Gagal reject dokumen.');
        redirect($redirectPath);
    }

    public function previewDocument($fileId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $file = $this->MPost_Donasi_MyRep->getFileById((int) $fileId);
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

    private function isApprover()
    {
        return $this->session->userdata('lokasi_user') === 'HO'
            || $this->session->userdata('nama_level') === 'Super Admin';
    }

    private function resolveRedirectPath($clusterId)
    {
        if ((int) $this->input->post('redirect_to_batch_detail') === 1 && (int) $clusterId > 0) {
            return 'Batch_Approval_MyRep/detail/' . (int) $clusterId;
        }

        return 'Post_Donasi_MyRep/detail/' . (int) $clusterId;
    }
}
