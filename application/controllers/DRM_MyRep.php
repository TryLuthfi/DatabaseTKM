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
        $data['subfeederReady'] = $this->MDRM_MyRep->drmSubfeederReady();
        $data['canApprove'] = $this->isApprover();
        $data['drmScopes'] = [
            'CLUSTER' => [
                'key' => 'CLUSTER',
                'label' => 'Doc Cluster',
                'documentRows' => $data['docReady'] ? $this->MDRM_MyRep->getDrmDocumentRows($clusterId, 'CLUSTER') : [],
                'boqHeader' => $data['boqReady'] ? $this->MDRM_MyRep->getDrmBoqHeader($clusterId, 'CLUSTER') : [],
                'boqItems' => $data['boqReady'] ? $this->MDRM_MyRep->getDrmBoqItems($clusterId, 'CLUSTER') : [],
                'boqBaselineHeader' => $data['boqReady'] ? $this->MDRM_MyRep->getBoqBaselineHeader($clusterId, 'CLUSTER') : [],
                'boqBaselineItems' => $data['boqReady'] ? $this->MDRM_MyRep->getBoqBaselineItems($clusterId, 'CLUSTER') : [],
                'apdBoqFile' => $data['docReady'] ? $this->MDRM_MyRep->getApdBoqDocumentFile($clusterId, 'CLUSTER') : [],
                'isReady' => true,
            ],
            'SUBFEEDER' => [
                'key' => 'SUBFEEDER',
                'label' => 'Doc Subfeeder',
                'documentRows' => $data['subfeederReady'] ? $this->MDRM_MyRep->getDrmDocumentRows($clusterId, 'SUBFEEDER') : [],
                'boqHeader' => $data['subfeederReady'] ? $this->MDRM_MyRep->getDrmBoqHeader($clusterId, 'SUBFEEDER') : [],
                'boqItems' => $data['subfeederReady'] ? $this->MDRM_MyRep->getDrmBoqItems($clusterId, 'SUBFEEDER') : [],
                'boqBaselineHeader' => $data['subfeederReady'] ? $this->MDRM_MyRep->getBoqBaselineHeader($clusterId, 'SUBFEEDER') : [],
                'boqBaselineItems' => $data['subfeederReady'] ? $this->MDRM_MyRep->getBoqBaselineItems($clusterId, 'SUBFEEDER') : [],
                'apdBoqFile' => $data['subfeederReady'] ? $this->MDRM_MyRep->getApdBoqDocumentFile($clusterId, 'SUBFEEDER') : [],
                'isReady' => $data['subfeederReady'],
            ],
        ];

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
        $namaOlt = trim((string) $this->input->post('nama_olt'));
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
            'nama_olt' => $namaOlt !== '' ? $namaOlt : null,
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
        $namaOlt = trim((string) $this->input->post('nama_olt'));
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
            'nama_olt' => $namaOlt !== '' ? $namaOlt : null,
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
        $scopeType = $this->normalizeScopeType($this->input->post('scope_type'));
        $docItemId = (int) $this->input->post('id_doc_item');
        $detail = $this->MDRM_MyRep->getDrmDocumentDetail($clusterId, $docItemId, $scopeType);
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
            $fileName = 'DRM_' . $scopeType . '_' . $clusterId . '_' . $docItemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png|rar|zip',
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
        ], $scopeType);

        if ($fileId > 0) {
            $clusterDetail = $this->MDRM_MyRep->getDrmByClusterId($clusterId);
            $notificationContext = $this->buildDrmFullUploadNotificationContext($clusterId, $scopeType);
            if (!empty($notificationContext['should_notify'])) {
                $this->sendDrmNotification(
                    (string) $notificationContext['event_name'],
                    $clusterDetail,
                    (string) ($detail['doc_name'] ?? 'DRM'),
                    (string) $notificationContext['module_label']
                );
            }
        }

        $this->session->set_flashdata($fileId > 0 ? 'success' : 'error', $fileId > 0 ? 'Dokumen DRM berhasil diupload.' : 'Dokumen DRM gagal disimpan.');
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function uploadBulkDocuments()
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
        $scopeType = $this->normalizeScopeType($this->input->post('scope_type'));
        $docItemIds = (array) $this->input->post('bulk_doc_item_ids');
        if ($clusterId <= 0 || empty($docItemIds)) {
            $this->session->set_flashdata('error', 'Data bulk upload DRM tidak lengkap.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $uploadDir = './uploads/myrep_drm/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedCount = 0;
        $errors = [];
        $userId = (int) $this->session->userdata('id_user');

        foreach ($docItemIds as $docItemIdRaw) {
            $docItemId = (int) $docItemIdRaw;
            if ($docItemId <= 0) {
                continue;
            }

            $fieldName = 'bulk_file_' . $docItemId;
            $isNoDocumentRequired = (int) $this->input->post('bulk_not_required_' . $docItemId) === 1;
            $hasFile = !empty($_FILES[$fieldName]['name']);
            if (!$isNoDocumentRequired && !$hasFile) {
                continue;
            }

            $detail = $this->MDRM_MyRep->getDrmDocumentDetail($clusterId, $docItemId, $scopeType);
            if (empty($detail)) {
                $errors[] = 'Konfigurasi dokumen item #' . $docItemId . ' tidak ditemukan.';
                continue;
            }

            $docName = strtoupper(trim((string) ($detail['doc_name'] ?? '')));
            if ($docName === 'APD BOQ') {
                continue;
            }

            $fileName = '';
            $filePath = '';
            if (!$isNoDocumentRequired) {
                $extension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
                $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($detail['doc_name'] ?? 'DRM'));
                $fileName = 'DRM_' . $scopeType . '_' . $clusterId . '_' . $docItemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;
                $config = [
                    'upload_path' => $uploadDir,
                    'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png|rar|zip',
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
                $fileName = (string) $fileData['file_name'];
                $filePath = 'uploads/myrep_drm/' . $fileData['file_name'];
            }

            $savedFileId = $this->MDRM_MyRep->saveDrmFileUpload($clusterId, $docItemId, [
                'file_name' => $fileName,
                'file_path' => $filePath,
                'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
                'status_file' => 'UPLOADED',
                'remark' => trim((string) $this->input->post('bulk_remark_' . $docItemId)),
                'uploaded_by' => $userId,
            ], $scopeType);

            if ($savedFileId > 0) {
                $uploadedCount++;
            } else {
                $errors[] = ($detail['doc_name'] ?? ('Item #' . $docItemId)) . ': gagal disimpan.';
            }
        }

        if ($uploadedCount <= 0) {
            $this->session->set_flashdata('error', !empty($errors) ? implode(' | ', $errors) : 'Tidak ada dokumen yang diupload.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $notificationContext = $this->buildDrmFullUploadNotificationContext($clusterId, $scopeType);
        if (!empty($notificationContext['should_notify'])) {
            $clusterDetail = $this->MDRM_MyRep->getDrmByClusterId($clusterId);
            $this->sendDrmNotification(
                (string) $notificationContext['event_name'],
                $clusterDetail,
                'Bulk Upload',
                (string) $notificationContext['module_label']
            );
        }

        $message = $uploadedCount . ' dokumen DRM berhasil diupload.';
        if (!empty($errors)) {
            $message .= ' Beberapa item gagal: ' . implode(' | ', $errors);
        }
        $this->session->set_flashdata('success', $message);
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function approveAllDocuments()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve semua dokumen DRM.');
            redirect('DRM_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $scopeType = $this->normalizeScopeType($this->input->post('scope_type'));
        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Cluster DRM tidak valid.');
            redirect('DRM_MyRep');
            return;
        }

        $documentRows = $this->MDRM_MyRep->getDrmDocumentRows($clusterId, $scopeType);
        if (empty($documentRows)) {
            $this->session->set_flashdata('error', 'Dokumen DRM untuk scope ini belum tersedia.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $approvedBy = (int) $this->session->userdata('id_user');
        $updatedCount = 0;
        foreach ($documentRows as $documentRow) {
            $docName = strtoupper(trim((string) ($documentRow['doc_name'] ?? '')));
            if ($docName === 'APD BOQ') {
                continue;
            }

            $fileId = (int) ($documentRow['id_doc_file'] ?? 0);
            $status = strtoupper(trim((string) ($documentRow['status_file'] ?? '')));
            if ($fileId <= 0 || !in_array($status, ['UPLOADED', 'REJECTED'], true)) {
                continue;
            }

            $result = $this->MDRM_MyRep->updateDrmFileStatus($fileId, [
                'status_file' => 'APPROVED',
                'remark' => trim((string) ($documentRow['remark'] ?? '')),
                'approved_by' => $approvedBy,
            ]);
            if ($result) {
                $updatedCount++;
            }
        }

        $this->session->set_flashdata(
            $updatedCount > 0 ? 'success' : 'error',
            $updatedCount > 0
                ? ($updatedCount . ' dokumen DRM berhasil di-approve sekaligus (kecuali APD BOQ/Manual BOQ).')
                : 'Tidak ada dokumen DRM yang bisa di-approve sekaligus.'
        );
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function downloadDocumentBundle($clusterId = 0, $scopeType = 'CLUSTER')
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        $scopeType = $this->normalizeScopeType($scopeType);
        if ($clusterId <= 0 || !$this->MDRM_MyRep->drmDocumentTablesReady()) {
            show_404();
            return;
        }

        $cluster = $this->MDRM_MyRep->getDrmByClusterId($clusterId);
        if (empty($cluster)) {
            show_404();
            return;
        }

        $documentRows = $this->MDRM_MyRep->getDrmDocumentRows($clusterId, $scopeType);
        if (empty($documentRows)) {
            $this->session->set_flashdata('error', 'Dokumen DRM tidak ditemukan.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $scopeLabel = $scopeType === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
        $safeClusterName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($cluster['cluster_name'] ?? 'CLUSTER'));
        $downloadName = 'DRM_' . $scopeLabel . '_' . $safeClusterName . '_RAR_' . date('Ymd_His') . '.zip';
        $files = [];
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
            $entryName = $docName . '_' . basename($fullPath);
            $files[] = [
                'entry_name' => $entryName,
                'full_path' => $fullPath,
            ];
        }

        if (empty($files)) {
            $this->session->set_flashdata('error', 'Tidak ada file DRM yang bisa didownload.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            $tempZip = tempnam(sys_get_temp_dir(), 'drm_bundle_');
            if ($tempZip === false) {
                $this->session->set_flashdata('error', 'Gagal menyiapkan file download gabungan.');
                redirect('DRM_MyRep/detail/' . $clusterId);
                return;
            }

            $zipFile = $tempZip . '.zip';
            @rename($tempZip, $zipFile);
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                if (is_file($zipFile)) {
                    @unlink($zipFile);
                }
                $this->session->set_flashdata('error', 'Gagal membuat file download gabungan.');
                redirect('DRM_MyRep/detail/' . $clusterId);
                return;
            }

            foreach ($files as $file) {
                $zip->addFile($file['full_path'], $file['entry_name']);
            }
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Length: ' . filesize($zipFile));
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            header('Pragma: public');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            readfile($zipFile);
            @unlink($zipFile);
            exit;
        }

        $this->load->library('zip');
        foreach ($files as $file) {
            $content = @file_get_contents($file['full_path']);
            if ($content === false) {
                continue;
            }
            $this->zip->add_data($file['entry_name'], $content);
        }

        $archiveData = $this->zip->get_zip();
        if ($archiveData === false || $archiveData === '') {
            $this->session->set_flashdata('error', 'Gagal membuat file download gabungan.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        header('Content-Type: application/zip');
        header('Content-Length: ' . strlen($archiveData));
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        echo $archiveData;
        exit;
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
        $scopeType = $this->normalizeScopeType($this->input->post('scope_type'));
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
        $scopeType = $this->normalizeScopeType($this->input->post('scope_type'));
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
        $scopeType = $this->normalizeScopeType($this->input->post('scope_type'));
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

        $apdBoqFile = $this->MDRM_MyRep->getApdBoqDocumentFile($clusterId, $scopeType);
        $result = $this->MDRM_MyRep->saveDrmBoqDraft(
            $clusterId,
            (int) ($cluster['id_drm'] ?? 0),
            (int) ($apdBoqFile['id_doc_file'] ?? 0),
            $items,
            (int) $this->session->userdata('id_user'),
            $submitToHo,
            $scopeType
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
        $scopeType = $this->normalizeScopeType($this->input->post('scope_type'));
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

        $docDetail = $this->MDRM_MyRep->getDrmDocumentDetailByName($clusterId, 'APD BOQ', $scopeType);
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
            $fileName = 'DRM_' . $scopeType . '_' . $clusterId . '_' . (int) $docDetail['id_doc_item'] . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png|rar|zip',
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
            ], $scopeType);

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
            $submitToHo,
            $scopeType
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
        $scopeType = $this->normalizeScopeType($this->input->post('scope_type'));
        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve BOQ DRM.');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
        }

        $result = $this->MDRM_MyRep->approveDrmBoq(
            $clusterId,
            (int) $this->session->userdata('id_user'),
            trim((string) $this->input->post('remark')),
            $scopeType
        );

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'BOQ DRM berhasil di-approve. Baseline implementasi akan terbentuk otomatis setelah BOQ CLUSTER dan SUBFEEDER sama-sama approved.' : 'Gagal approve BOQ DRM.');
        redirect('DRM_MyRep/detail/' . $clusterId);
    }

    public function rejectBoq()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $scopeType = $this->normalizeScopeType($this->input->post('scope_type'));
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

        $result = $this->MDRM_MyRep->rejectDrmBoq($clusterId, (int) $this->session->userdata('id_user'), $remark, $scopeType);
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

    public function downloadDrmImportTemplate()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $headers = [
            'cluster_id', 'id_target', 'city_name', 'cluster_name', 'cluster_code',
            'homepass_drm', 'drm_date', 'nama_olt', 'status_drm', 'remark_drm',
        ];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=drm_import_template_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        $exampleRows = [
            ['', '', 'MALANG', 'Cluster A', 'CL-A', '100', date('Y-m-d'), 'OLT-MAL-01', 'WAITING DOC', 'Contoh status WAITING DOC'],
            ['', '', 'MALANG', 'Cluster B', 'CL-B', '120', date('Y-m-d'), 'OLT-MAL-02', 'WAITING APPROVE', 'Contoh status WAITING APPROVE'],
            ['', '', 'MALANG', 'Cluster C', 'CL-C', '90', date('Y-m-d'), 'OLT-MAL-03', 'COMPLETE', 'Contoh status COMPLETE'],
            ['', '', 'MALANG', 'Cluster D', 'CL-D', '110', date('Y-m-d'), 'OLT-MAL-04', 'REJECTED', 'Contoh status REJECTED'],
            ['', '', 'MALANG', 'Cluster E', 'CL-E', '130', date('Y-m-d'), 'OLT-MAL-05', 'WAITING DOC', 'Contoh tambahan import'],
        ];
        foreach ($exampleRows as $exampleRow) {
            fputcsv($output, $exampleRow);
        }
        fclose($output);
        exit;
    }

    public function previewDrmImport()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session login tidak ditemukan.');
            return;
        }
        if (!$this->MDRM_MyRep->drmTablesReady()) {
            $this->jsonResponse(false, 'Tabel DRM MyRep belum tersedia.');
            return;
        }
        if (empty($_FILES['file_excel']['name'])) {
            $this->jsonResponse(false, 'File import belum dipilih.');
            return;
        }

        $uploadDir = FCPATH . 'uploads/temp_drm_import/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['xls', 'xlsx', 'csv'], true)) {
            $this->jsonResponse(false, 'Format file harus xls/xlsx/csv.');
            return;
        }

        $tempPath = $uploadDir . 'drm_import_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $extension;
        if (!move_uploaded_file($_FILES['file_excel']['tmp_name'], $tempPath)) {
            $this->jsonResponse(false, 'Gagal upload file import.');
            return;
        }

        $sheetData = [];
        if ($extension === 'csv') {
            $this->loadPHPExcel();
            $sheetData = $this->readCsvSheetData($tempPath);
        } else {
            $this->loadPHPExcel();
            $excel = PHPExcel_IOFactory::load($tempPath);
            $sheetData = $excel->getActiveSheet()->toArray(null, true, true, true);
        }
        @unlink($tempPath);

        if (empty($sheetData) || !is_array($sheetData)) {
            $this->jsonResponse(false, 'Isi file import kosong.');
            return;
        }

        $headerRow = [];
        foreach ($sheetData as $row) { $headerRow = $row; break; }
        $mappedHeader = [];
        foreach ($headerRow as $columnKey => $columnName) {
            $key = $this->parseDrmImportHeader((string) $columnName);
            if ($key !== null) { $mappedHeader[$columnKey] = $key; }
        }

        $rows = [];
        $rowIndex = 0;
        foreach ($sheetData as $row) {
            $rowIndex++;
            if ($rowIndex === 1) { continue; }
            $item = [];
            $isBlank = true;
            foreach ($mappedHeader as $columnKey => $fieldName) {
                $value = isset($row[$columnKey]) ? trim((string) $row[$columnKey]) : '';
                if ($value !== '') { $isBlank = false; }
                $item[$fieldName] = $value;
            }
            if (!$isBlank) { $rows[] = $item; }
        }

        $validated = $this->validateDrmImportRows($rows);
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => true,
            'message' => count($validated['valid_rows']) . ' data valid dari ' . count($validated['rows']) . ' baris',
            'rows' => $validated['rows'],
            'valid_rows' => $validated['valid_rows'],
            'error_rows' => $validated['errors'],
        ]));
    }

    public function saveImportedDrm()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session login tidak ditemukan.');
            return;
        }
        if (!$this->MDRM_MyRep->drmTablesReady()) {
            $this->jsonResponse(false, 'Tabel DRM MyRep belum tersedia.');
            return;
        }

        $rows = json_decode((string) $this->input->post('rows_json'), true);
        if (empty($rows) || !is_array($rows)) {
            $this->jsonResponse(false, 'Tidak ada data import yang siap disimpan.');
            return;
        }

        $validated = $this->validateDrmImportRows($rows);
        if (empty($validated['valid_rows'])) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status' => false,
                'message' => 'Semua data import tidak valid.',
                'errors' => $validated['errors'],
            ]));
            return;
        }

        $inserted = 0;
        $userId = (int) $this->session->userdata('id_user');
        foreach ($validated['valid_rows'] as $row) {
            $clusterId = (int) ($row['cluster_id'] ?? 0);
            if ($clusterId <= 0 && (int) ($row['is_new_cluster'] ?? 0) === 1) {
                $clusterId = $this->MDRM_MyRep->createClusterForDrmImport(
                    (int) ($row['id_target'] ?? 0),
                    (string) ($row['cluster_name'] ?? ''),
                    (string) ($row['cluster_code'] ?? ''),
                    (int) ($row['homepass_drm'] ?? 0),
                    $userId
                );
            }
            if ($clusterId <= 0) { continue; }

            $readyPrev = $this->MDRM_MyRep->upsertBakValsalBatchForDrmImport(
                $clusterId,
                (int) ($row['homepass_drm'] ?? 0),
                (string) ($row['drm_date'] ?? date('Y-m-d')),
                $userId,
                (string) ($row['remark_drm'] ?? '')
            );
            if (!$readyPrev) { continue; }

            $result = $this->MDRM_MyRep->createDrm($clusterId, [
                'drm_date' => (string) ($row['drm_date'] ?? date('Y-m-d')),
                'homepass_drm' => (int) ($row['homepass_drm'] ?? 0),
                'nama_olt' => trim((string) ($row['nama_olt'] ?? '')) ?: null,
                'status_drm' => (string) ($row['status_drm'] ?? 'WAITING DOC'),
                'remark_drm' => trim((string) ($row['remark_drm'] ?? '')) ?: null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ], [
                'status_current' => $this->buildCurrentStatus((string) ($row['drm_date'] ?? ''), (string) ($row['status_drm'] ?? 'WAITING DOC')),
                'updated_by' => $userId,
            ]);

            if ($result) { $inserted++; }
        }

        if ($inserted <= 0) {
            $this->jsonResponse(false, 'Gagal menyimpan hasil import DRM.');
            return;
        }

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => true,
            'message' => $inserted . ' data DRM berhasil diimport.',
        ]));
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

    private function parseDrmImportHeader($header)
    {
        $header = strtolower(trim((string) $header));
        if ($header === '') { return null; }
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        $header = trim($header, '_');
        $aliases = [
            'cluster_id' => ['cluster_id', 'id_myrep_cluster', 'id_cluster'],
            'id_target' => ['id_target', 'target_id'],
            'city_name' => ['city_name', 'city', 'kota'],
            'cluster_name' => ['cluster_name', 'nama_cluster', 'cluster'],
            'cluster_code' => ['cluster_code', 'kode_cluster'],
            'homepass_drm' => ['homepass_drm', 'hp_drm', 'homepass', 'hp'],
            'drm_date' => ['drm_date', 'tanggal_drm'],
            'nama_olt' => ['nama_olt', 'olt'],
            'status_drm' => ['status_drm', 'status'],
            'remark_drm' => ['remark_drm', 'remark', 'catatan'],
        ];
        foreach ($aliases as $field => $opts) {
            if (in_array($header, $opts, true)) { return $field; }
        }
        return null;
    }

    private function validateDrmImportRows(array $rawRows)
    {
        $preparedRows = [];
        $errors = [];
        foreach ($rawRows as $index => $rawRow) {
            $rowNumber = $index + 1;
            $clusterId = (int) ($rawRow['cluster_id'] ?? 0);
            $targetId = (int) ($rawRow['id_target'] ?? 0);
            $cityName = strtoupper(trim((string) ($rawRow['city_name'] ?? '')));
            $clusterName = trim((string) ($rawRow['cluster_name'] ?? ''));
            $clusterCode = trim((string) ($rawRow['cluster_code'] ?? ''));
            $homepassDrm = (int) $this->normalizeNumber($rawRow['homepass_drm'] ?? 0);
            $drmDate = $this->normalizeDate((string) ($rawRow['drm_date'] ?? '')) ?: date('Y-m-d');
            $statusDrm = strtoupper(trim((string) ($rawRow['status_drm'] ?? 'WAITING DOC')));
            $remarkDrm = trim((string) ($rawRow['remark_drm'] ?? ''));
            $rowErrors = [];

            $candidate = [];
            if ($clusterId > 0) {
                $candidate = $this->MDRM_MyRep->getClusterForDrmImportById($clusterId);
            }
            if (empty($candidate) && $clusterName !== '') {
                if ($targetId <= 0 && $cityName !== '') {
                    $target = $this->MDRM_MyRep->getTargetByCity($cityName);
                    $targetId = (int) ($target['id_target'] ?? 0);
                }
                $candidate = $this->MDRM_MyRep->getClusterForDrmImportByName($clusterName, $cityName, $targetId);
                $clusterId = (int) ($candidate['id_myrep_cluster'] ?? 0);
            }

            $isNewCluster = false;
            if (empty($candidate) || $clusterId <= 0) {
                if ($targetId <= 0 && $cityName !== '') {
                    $target = $this->MDRM_MyRep->getTargetByCity($cityName);
                    $targetId = (int) ($target['id_target'] ?? 0);
                }
                if ($clusterName === '') { $rowErrors[] = 'Cluster name wajib diisi jika cluster belum ada'; }
                if ($targetId <= 0) { $rowErrors[] = 'id_target / city_name wajib valid untuk cluster baru'; }
                $isNewCluster = empty($rowErrors);
            } else {
                if (!empty($candidate['id_drm'])) { $rowErrors[] = 'Cluster sudah punya data DRM'; }
            }

            if ($homepassDrm <= 0) { $rowErrors[] = 'homepass_drm wajib > 0'; }
            if (!in_array($statusDrm, ['WAITING DOC', 'WAITING APPROVE', 'COMPLETE', 'REJECTED'], true)) {
                $statusDrm = 'WAITING DOC';
            }

            $preparedRows[] = [
                'row_number' => $rowNumber,
                'cluster_id' => $clusterId,
                'id_target' => $targetId,
                'is_new_cluster' => $isNewCluster ? 1 : 0,
                'city_name' => $cityName !== '' ? $cityName : (string) ($candidate['city_name'] ?? ''),
                'cluster_name' => $clusterName !== '' ? $clusterName : (string) ($candidate['cluster_name'] ?? ''),
                'cluster_code' => $clusterCode !== '' ? $clusterCode : (string) ($candidate['cluster_code'] ?? ''),
                'homepass_drm' => $homepassDrm,
                'drm_date' => $drmDate,
                'nama_olt' => trim((string) ($rawRow['nama_olt'] ?? '')),
                'status_drm' => $statusDrm,
                'remark_drm' => $remarkDrm,
                'status' => empty($rowErrors) ? 'valid' : 'invalid',
                'message' => empty($rowErrors) ? 'Siap diimport' : implode(', ', array_unique($rowErrors)),
                'errors' => $rowErrors,
            ];
        }

        foreach ($preparedRows as $r) {
            if (!empty($r['errors'])) {
                $errors[] = ['row' => $r['row_number'], 'message' => implode(', ', array_unique($r['errors']))];
            }
        }
        $validRows = array_values(array_filter($preparedRows, static function ($r) { return empty($r['errors']); }));
        return ['rows' => $preparedRows, 'valid_rows' => $validRows, 'errors' => $errors];
    }

    private function loadPHPExcel()
    {
        if (!class_exists('PHPExcel')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        }
    }

    private function readCsvSheetData($filePath)
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if ($handle === false) { return $rows; }
        $firstLine = fgets($handle);
        if ($firstLine === false) { fclose($handle); return $rows; }
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!empty($data)) {
                if (isset($data[0])) { $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]); }
                $rows[] = $data;
            }
        }
        fclose($handle);
        $sheetData = [];
        foreach ($rows as $rowIndex => $row) {
            $sheetRow = [];
            foreach ($row as $colIndex => $value) {
                $sheetRow[PHPExcel_Cell::stringFromColumnIndex($colIndex)] = $value;
            }
            $sheetData[$rowIndex + 1] = $sheetRow;
        }
        return $sheetData;
    }

    private function jsonResponse($status, $message)
    {
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => $status,
            'message' => $message,
        ]));
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

    private function normalizeScopeType($scopeType)
    {
        return strtoupper(trim((string) $scopeType)) === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
    }

    private function isApprover()
    {
        return $this->session->userdata('lokasi_user') === 'HO'
            || $this->session->userdata('nama_level') === 'Super Admin';
    }

    private function sendDrmNotification($eventName, array $cluster, $documentLabel, $moduleLabel = 'DRM')
    {
        $clusterId = (int) ($cluster['id_myrep_cluster'] ?? 0);
        if ($clusterId <= 0) {
            return;
        }

        $this->myrepNotifier->notify('DRM_MyRep', $eventName, [
            'module_label' => (string) $moduleLabel,
            'document_label' => (string) $documentLabel,
            'regional_name' => (string) ($cluster['regional_name'] ?? ''),
            'city_name' => (string) ($cluster['city_name'] ?? ''),
            'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
            'sender_name' => (string) $this->session->userdata('nama_user'),
            'detail_url' => base_url('DRM_MyRep/detail/' . $clusterId),
        ]);
    }

    private function buildDrmFullUploadNotificationContext($clusterId, $scopeType)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return ['should_notify' => false];
        }

        $normalizedScopeType = $this->normalizeScopeType($scopeType);
        $documentRows = $this->MDRM_MyRep->getDrmDocumentRows($clusterId, $normalizedScopeType);
        if (empty($documentRows)) {
            return ['should_notify' => false];
        }

        $total = 0;
        $uploaded = 0;
        foreach ($documentRows as $documentRow) {
            $total++;
            if ((int) ($documentRow['id_doc_file'] ?? 0) > 0) {
                $uploaded++;
            }
        }

        if ($total <= 0 || $uploaded < $total) {
            return ['should_notify' => false];
        }

        return [
            'should_notify' => true,
            'event_name' => 'full_upload',
            'module_label' => $normalizedScopeType === 'SUBFEEDER' ? 'DRM SUBFEEDER' : 'DRM CLUSTER',
        ];
    }
}
