<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Checklist_Dokument_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MChecklist_Dokument_MyRep');
        $this->load->library('upload');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedRegional = strtoupper(trim((string) $this->input->get('regional')));

        $data['title'] = 'Checklist Dokument';
        $data['selectedCity'] = $selectedCity;
        $data['selectedRegional'] = $selectedRegional;
        $data['cityOptions'] = $this->MChecklist_Dokument_MyRep->getCityOptions();
        $data['regionalOptions'] = $this->MChecklist_Dokument_MyRep->getRegionalOptions();
        $data['clusterList'] = $this->MChecklist_Dokument_MyRep->getFullRfsClusters($selectedCity, $selectedRegional);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Checklist_Dokument_MyRep/index', $data);
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
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $cluster = $this->MChecklist_Dokument_MyRep->getClusterDetail($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data cluster tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $this->MChecklist_Dokument_MyRep->ensureClusterPackages($clusterId, $cluster['tanggal_rfs'] ?? null);
        $cluster = $this->MChecklist_Dokument_MyRep->getClusterDetail($clusterId);

        $data['title'] = 'Checklist Dokument Detail';
        $data['cluster'] = $cluster;
        $data['scopeTabs'] = $this->MChecklist_Dokument_MyRep->getClusterScopeTabs($clusterId);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Checklist_Dokument_MyRep/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveTimeline()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $packageId = (int) $this->input->post('id_doc_package');
        if ($clusterId <= 0 || $packageId <= 0) {
            $this->session->set_flashdata('error', 'Data timeline tidak valid.');
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $payload = [
            'tanggal_rfs' => $this->normalizeDateInput($this->input->post('tanggal_rfs')),
            'actual_atp_date' => $this->normalizeDateInput($this->input->post('actual_atp_date')),
            'remarks' => trim((string) $this->input->post('remarks')),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ];

        $this->MChecklist_Dokument_MyRep->updatePackageTimeline($packageId, $payload);
        $this->session->set_flashdata('success', 'Timeline berhasil diperbarui.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function uploadDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $packageId = (int) $this->input->post('id_doc_package');
        $itemId = (int) $this->input->post('id_doc_item');
        $docName = trim((string) $this->input->post('doc_name'));
        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;

        if ($clusterId <= 0 || $packageId <= 0 || $itemId <= 0) {
            $this->session->set_flashdata('error', 'Data upload dokumen belum lengkap.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->session->set_flashdata('error', 'File wajib dipilih jika dokumen dibutuhkan.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $uploadDir = './uploads/checklist_myrep/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = '';
        $filePath = '';

        if (!$isNoDocumentRequired) {
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName);
            $fileName = 'DOC_' . $clusterId . '_' . $packageId . '_' . $itemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
                'max_size' => 10240,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);

            if (!$this->upload->do_upload('file')) {
                $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
                redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
                return;
            }

            $fileData = $this->upload->data();
            $fileName = $fileData['file_name'];
            $filePath = 'uploads/checklist_myrep/' . $fileData['file_name'];
        }

        $payload = [
            'id_doc_package' => $packageId,
            'id_doc_item' => $itemId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
        ];

        $this->MChecklist_Dokument_MyRep->saveFileUpload($payload);
        $this->session->set_flashdata('success', $isNoDocumentRequired ? 'Dokumen ditandai tidak dibutuhkan dan dikirim ke review.' : 'Dokumen berhasil diupload.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function bulkUploadDocuments()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $packageId = (int) $this->input->post('id_doc_package');
        $itemIds = $this->input->post('id_doc_item');
        $docNames = $this->input->post('doc_name');

        if ($clusterId <= 0 || $packageId <= 0 || !is_array($itemIds)) {
            $this->session->set_flashdata('error', 'Data bulk upload tidak valid.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $uploadDir = './uploads/checklist_myrep/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $successCount = 0;

        foreach ($itemIds as $index => $itemId) {
            $itemId = (int) $itemId;
            $docName = trim((string) ($docNames[$index] ?? ''));
            $inputName = 'bulk_file_' . $itemId;

            if ($itemId <= 0 || empty($_FILES[$inputName]['name'])) {
                continue;
            }

            $extension = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName);
            $fileName = 'DOC_' . $clusterId . '_' . $packageId . '_' . $itemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
                'max_size' => 10240,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $_FILES['single_bulk_file'] = $_FILES[$inputName];
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('single_bulk_file')) {
                continue;
            }

            $fileData = $this->upload->data();
            $payload = [
                'id_doc_package' => $packageId,
                'id_doc_item' => $itemId,
                'file_name' => $fileData['file_name'],
                'file_path' => 'uploads/checklist_myrep/' . $fileData['file_name'],
                'status_file' => 'UPLOADED',
                'remark' => '',
                'uploaded_by' => (int) $this->session->userdata('id_user'),
            ];

            $this->MChecklist_Dokument_MyRep->saveFileUpload($payload);
            $successCount++;
        }

        if ($successCount > 0) {
            $this->session->set_flashdata('success', $successCount . ' dokumen berhasil diupload.');
        } else {
            $this->session->set_flashdata('error', 'Tidak ada file yang berhasil diupload.');
        }

        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function approveDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $clusterId = (int) $this->input->post('cluster_id');

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        if ($fileId <= 0 || $clusterId <= 0) {
            $this->session->set_flashdata('error', 'Dokumen tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $this->MChecklist_Dokument_MyRep->updateFileStatus($fileId, [
            'status_file' => 'APPROVED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata('success', 'Dokumen berhasil di-approve.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function rejectDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $remark = trim((string) $this->input->post('remark'));
        if ($fileId <= 0) {
            $this->session->set_flashdata('error', 'Dokumen tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $this->MChecklist_Dokument_MyRep->updateFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => $remark,
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata('success', 'Dokumen berhasil di-reject.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function saveAstriStatus()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $fileId = (int) $this->input->post('id_doc_file');
        $astriStatus = trim((string) $this->input->post('astri_status'));
        $astriSubmittedDate = $this->normalizeDateInput($this->input->post('astri_submitted_date'));
        $astriRemark = trim((string) $this->input->post('astri_remark'));

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses update status ASTRI.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        if ($clusterId <= 0 || $fileId <= 0) {
            $this->session->set_flashdata('error', 'Data ASTRI tidak valid.');
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $allowedStatuses = ['NY', 'ON REVIEW', 'REJECTED', 'APPROVED'];
        if (!in_array($astriStatus, $allowedStatuses, true)) {
            $this->session->set_flashdata('error', 'Status ASTRI tidak dikenali.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $file = $this->MChecklist_Dokument_MyRep->getFileById($fileId);
        if (empty($file)) {
            $this->session->set_flashdata('error', 'Dokumen tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        if ($file['status_file'] !== 'APPROVED' && $astriStatus !== 'NY') {
            $this->session->set_flashdata('error', 'Dokumen internal harus APPROVED sebelum di-submit ke ASTRI.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        if ($astriStatus !== 'NY' && empty($astriSubmittedDate)) {
            $this->session->set_flashdata('error', 'Tanggal submit ASTRI wajib diisi untuk status selain NY.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $this->MChecklist_Dokument_MyRep->updateAstriStatus($fileId, [
            'astri_submitted_date' => $astriStatus === 'NY' ? null : $astriSubmittedDate,
            'astri_status' => $astriStatus,
            'astri_remark' => $astriRemark,
        ]);

        $this->session->set_flashdata('success', 'Status ASTRI berhasil diperbarui.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function previewDocument($fileId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $file = $this->MChecklist_Dokument_MyRep->getFileById((int) $fileId);
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
        $mimeType = isset($mimeMap[$extension]) ? $mimeMap[$extension] : 'application/octet-stream';

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    private function normalizeDateInput($date)
    {
        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        return $date;
    }

    private function isApprover()
    {
        return $this->session->userdata('lokasi_user') === 'HO'
            || $this->session->userdata('nama_level') === 'Super Admin';
    }
}
