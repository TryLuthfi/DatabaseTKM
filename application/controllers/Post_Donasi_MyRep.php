<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Post_Donasi_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MPost_Donasi_MyRep');
        $this->load->model('MBatch_Approval_MyRep');
        $this->load->library('upload');
        $this->load->library('Myrep_notification_service', null, 'myrepNotifier');
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
        $isReupload = !empty($detail['id_doc_file']);
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

        if ($fileId > 0) {
            $this->sendPostDonasiNotificationAfterUpload($clusterId, $isReupload, (string) ($detail['doc_name'] ?? ''));
        }

        $this->session->set_flashdata($fileId > 0 ? 'success' : 'error', $fileId > 0 ? 'Dokumen post donasi berhasil diupload.' : 'Dokumen post donasi gagal disimpan.');
        redirect($redirectPath);
    }

    public function uploadBulkDocuments()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $redirectPath = $this->resolveRedirectPath($clusterId);
        $docItemIds = (array) $this->input->post('bulk_doc_item_ids');

        if ($clusterId <= 0 || empty($docItemIds)) {
            $this->session->set_flashdata('error', 'Data bulk upload tidak lengkap.');
            redirect($redirectPath);
            return;
        }

        $uploadDir = './uploads/myrep_post_donasi/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedCount = 0;
        $hasReupload = false;
        $reuploadDocNames = [];
        $errors = [];
        $userId = (int) $this->session->userdata('id_user');

        foreach ($docItemIds as $docItemIdRaw) {
            $docItemId = (int) $docItemIdRaw;
            if ($docItemId <= 0) {
                continue;
            }

            $fieldName = 'bulk_file_' . $docItemId;
            if (empty($_FILES[$fieldName]['name'])) {
                continue;
            }

            $detail = $this->MPost_Donasi_MyRep->getDocumentDetail($clusterId, $docItemId);
            if (empty($detail)) {
                $errors[] = 'Konfigurasi dokumen item #' . $docItemId . ' tidak ditemukan.';
                continue;
            }

            $extension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
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
            if (!$this->upload->do_upload($fieldName)) {
                $errors[] = ($detail['doc_name'] ?? ('Item #' . $docItemId)) . ': ' . strip_tags($this->upload->display_errors());
                continue;
            }

            $fileData = $this->upload->data();
            $savedFileId = $this->MPost_Donasi_MyRep->saveFileUpload($clusterId, $docItemId, [
                'file_name' => (string) $fileData['file_name'],
                'file_path' => 'uploads/myrep_post_donasi/' . $fileData['file_name'],
                'is_document_not_required' => 0,
                'status_file' => 'UPLOADED',
                'remark' => trim((string) $this->input->post('bulk_remark_' . $docItemId)),
                'uploaded_by' => $userId,
            ]);

            if ($savedFileId > 0) {
                $uploadedCount++;
                if (!empty($detail['id_doc_file'])) {
                    $hasReupload = true;
                    $reuploadDocNames[] = (string) ($detail['doc_name'] ?? '');
                }
            } else {
                $errors[] = ($detail['doc_name'] ?? ('Item #' . $docItemId)) . ': gagal disimpan.';
            }
        }

        if ($uploadedCount <= 0) {
            $this->session->set_flashdata('error', !empty($errors) ? implode(' | ', $errors) : 'Tidak ada dokumen yang diupload.');
            redirect($redirectPath);
            return;
        }

        $reuploadDocNames = array_values(array_unique(array_filter($reuploadDocNames)));
        $this->sendPostDonasiNotificationAfterUpload(
            $clusterId,
            $hasReupload,
            count($reuploadDocNames) === 1 ? $reuploadDocNames[0] : ''
        );

        $message = $uploadedCount . ' dokumen post donasi berhasil diupload.';
        if (!empty($errors)) {
            $message .= ' Beberapa item gagal: ' . implode(' | ', $errors);
        }
        $this->session->set_flashdata('success', $message);
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
        $docItemId = (int) $this->input->post('id_doc_item');
        $remark = trim((string) $this->input->post('remark'));
        $approvedBy = (int) $this->session->userdata('id_user');
        $result = false;

        if ($fileId > 0) {
            $result = $this->MPost_Donasi_MyRep->updateFileStatus($fileId, [
                'status_file' => 'APPROVED',
                'remark' => $remark,
                'approved_by' => $approvedBy,
            ]);
        } elseif ($docItemId > 0) {
            $result = $this->MPost_Donasi_MyRep->saveLinkedReviewDecision($clusterId, $docItemId, [
                'status_file' => 'APPROVED',
                'remark' => $remark,
                'approved_by' => $approvedBy,
                'file_name' => trim((string) $this->input->post('linked_file_name')),
            ]) > 0;
        }

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
        $docItemId = (int) $this->input->post('id_doc_item');
        $remark = trim((string) $this->input->post('remark'));
        $approvedBy = (int) $this->session->userdata('id_user');
        $result = false;

        if ($fileId > 0) {
            $result = $this->MPost_Donasi_MyRep->updateFileStatus($fileId, [
                'status_file' => 'REJECTED',
                'remark' => $remark,
                'approved_by' => $approvedBy,
            ]);
        } elseif ($docItemId > 0) {
            $result = $this->MPost_Donasi_MyRep->saveLinkedReviewDecision($clusterId, $docItemId, [
                'status_file' => 'REJECTED',
                'remark' => $remark,
                'approved_by' => $approvedBy,
                'file_name' => trim((string) $this->input->post('linked_file_name')),
            ]) > 0;
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen berhasil di-reject.' : 'Gagal reject dokumen.');
        redirect($redirectPath);
    }

    public function approveAllDocuments()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $redirectPath = $this->resolveRedirectPath($clusterId);

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve semua dokumen post donasi.');
            redirect($redirectPath);
            return;
        }

        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Cluster post donasi tidak valid.');
            redirect($redirectPath);
            return;
        }

        $documentRows = $this->MPost_Donasi_MyRep->getDocumentRows($clusterId);
        if (empty($documentRows)) {
            $this->session->set_flashdata('error', 'Dokumen post donasi untuk cluster ini belum tersedia.');
            redirect($redirectPath);
            return;
        }

        $linkedSupportDocumentMap = $this->MBatch_Approval_MyRep->batchDocumentTablesReady()
            ? $this->MBatch_Approval_MyRep->getAutoLinkedSupportDocumentMap($clusterId)
            : [];
        foreach ($documentRows as &$documentRow) {
            $normalizedDocName = strtoupper(trim((string) ($documentRow['doc_name'] ?? '')));
            if (isset($linkedSupportDocumentMap[$normalizedDocName])) {
                $documentRow = array_merge($documentRow, $linkedSupportDocumentMap[$normalizedDocName]);
            }
        }
        unset($documentRow);

        $approvedBy = (int) $this->session->userdata('id_user');
        $updatedCount = 0;
        foreach ($documentRows as $documentRow) {
            $fileId = (int) ($documentRow['id_doc_file'] ?? 0);
            $status = strtoupper(trim((string) ($documentRow['status_file'] ?? '')));
            $result = false;

            if ($fileId > 0 && in_array($status, ['UPLOADED', 'REJECTED'], true)) {
                $result = $this->MPost_Donasi_MyRep->updateFileStatus($fileId, [
                    'status_file' => 'APPROVED',
                    'remark' => trim((string) ($documentRow['remark'] ?? '')),
                    'approved_by' => $approvedBy,
                ]);
            } elseif ($fileId <= 0 && !empty($documentRow['linked_source_file_id'])) {
                $result = $this->MPost_Donasi_MyRep->saveLinkedReviewDecision($clusterId, (int) ($documentRow['id_doc_item'] ?? 0), [
                    'status_file' => 'APPROVED',
                    'remark' => trim((string) ($documentRow['remark'] ?? '')),
                    'approved_by' => $approvedBy,
                    'file_name' => trim((string) ($documentRow['linked_source_file_name'] ?? '')),
                ]) > 0;
            }

            if ($result) {
                $updatedCount++;
            }
        }

        $this->session->set_flashdata(
            $updatedCount > 0 ? 'success' : 'error',
            $updatedCount > 0
                ? ($updatedCount . ' dokumen post donasi berhasil di-approve sekaligus.')
                : 'Tidak ada dokumen post donasi yang bisa di-approve sekaligus.'
        );
        redirect($redirectPath);
    }

    public function downloadDocumentBundle($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0 || !$this->MPost_Donasi_MyRep->documentTablesReady()) {
            show_404();
            return;
        }

        $cluster = $this->MPost_Donasi_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            show_404();
            return;
        }

        $documentRows = $this->MPost_Donasi_MyRep->getDocumentRows($clusterId);
        if (empty($documentRows)) {
            $this->session->set_flashdata('error', 'Dokumen post donasi tidak ditemukan.');
            redirect($this->resolveRedirectPath($clusterId));
            return;
        }

        $zip = new ZipArchive();
        $tempZip = tempnam(sys_get_temp_dir(), 'post_donasi_bundle_');
        if ($tempZip === false) {
            $this->session->set_flashdata('error', 'Gagal menyiapkan file download gabungan.');
            redirect($this->resolveRedirectPath($clusterId));
            return;
        }

        $zipFile = $tempZip . '.zip';
        @rename($tempZip, $zipFile);

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            if (is_file($zipFile)) {
                @unlink($zipFile);
            }
            $this->session->set_flashdata('error', 'Gagal membuat file download gabungan.');
            redirect($this->resolveRedirectPath($clusterId));
            return;
        }

        $addedCount = 0;
        foreach ($documentRows as $documentRow) {
            $filePath = trim((string) ($documentRow['file_path'] ?? ''));
            if ($filePath === '') {
                continue;
            }

            $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
            if (!is_file($fullPath)) {
                continue;
            }

            $docName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($documentRow['doc_name'] ?? 'DOKUMEN'));
            $originalName = basename($fullPath);
            $zip->addFile($fullPath, $docName . '_' . $originalName);
            $addedCount++;
        }

        $zip->close();

        if ($addedCount <= 0) {
            if (is_file($zipFile)) {
                @unlink($zipFile);
            }
            $this->session->set_flashdata('error', 'Tidak ada file post donasi yang bisa didownload.');
            redirect($this->resolveRedirectPath($clusterId));
            return;
        }

        $safeClusterName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($cluster['cluster_name'] ?? 'CLUSTER'));
        $downloadName = 'POST_DONASI_' . $safeClusterName . '_gabungan_' . date('Ymd_His') . '.zip';

        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($zipFile));
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        readfile($zipFile);
        @unlink($zipFile);
        exit;
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

    private function sendPostDonasiNotificationAfterUpload($clusterId, $hasReupload, $documentLabel = '')
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return;
        }

        $cluster = $this->MPost_Donasi_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            return;
        }

        $summary = $this->MPost_Donasi_MyRep->getDocumentSummary($clusterId);
        $eventName = $hasReupload
            ? 'batch_revised'
            : (($summary['total'] > 0 && $summary['uploaded'] >= $summary['total']) ? 'full_upload' : '');

        if ($eventName === '') {
            return;
        }

        $this->myrepNotifier->notify('Batch_Approval_MyRep', $eventName, [
            'module_label' => 'Batch Approval',
            'document_label' => (string) $documentLabel,
            'regional_name' => (string) ($cluster['regional_name'] ?? ''),
            'city_name' => (string) ($cluster['city_name'] ?? ''),
            'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
            'sender_name' => (string) $this->session->userdata('nama_user'),
            'detail_url' => base_url('Batch_Approval_MyRep/detail/' . $clusterId),
        ]);
    }
}
