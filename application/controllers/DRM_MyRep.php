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
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('DRM_MyRep');
            $this->myrepAccess->enforceByMethod('DRM_MyRep', (string) $this->router->fetch_method(), [
                'approveBoq' => 'APPROVAL',
                'rejectBoq' => 'APPROVAL',
                'previewDrmImport' => 'TAMBAH',
            ]);
        }
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
        $data['regionalOptions'] = $this->MDRM_MyRep->getRegionalOptions();
        $data['cityOptionsByRegional'] = $this->MDRM_MyRep->getCityOptionsByRegional();
        $data['regionalOptionsByCity'] = $this->MDRM_MyRep->getRegionalOptionsByCity();
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

    public function downloadReport()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MDRM_MyRep->drmTablesReady()) {
            show_404();
            return;
        }

        $rawCity = $this->input->get('city');
        $selectedCity = is_array($rawCity) ? '' : strtoupper(trim((string) $rawCity));
        $selectedStatus = strtoupper(trim((string) $this->input->get('status')));
        $rawRegional = $this->input->get('regional');
        $selectedRegional = is_array($rawRegional) ? '' : strtoupper(trim((string) $rawRegional));
        $regionalList = $this->normalizeUpperList($this->input->get('regional'));
        $cityList = $this->normalizeUpperList($this->input->get('city'));
        $drmDateStart = $this->normalizeDate($this->input->get('drm_date_start')) ?: '';
        $drmDateEnd = $this->normalizeDate($this->input->get('drm_date_end')) ?: '';

        $rows = $this->MDRM_MyRep->getDrmRows($selectedCity, $selectedStatus, $selectedRegional, $cityList, $regionalList, $drmDateStart, $drmDateEnd);

        $filename = 'report_drm_myrep_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Cluster',
            'Kode Cluster',
            'Regional',
            'Kota',
            'Periode Target',
            'HP Donasi',
            'HP DRM',
            'Tanggal Released',
            'Tanggal DRM',
            'Status DRM',
            'Status Flow',
            'Nama OLT',
            'Remark DRM',
        ]);

        foreach ($rows as $row) {
            $periodLabel = !empty($row['year_num']) && !empty($row['month_num'])
                ? sprintf('%02d/%04d', (int) $row['month_num'], (int) $row['year_num'])
                : '-';

            fputcsv($output, [
                (string) ($row['cluster_name'] ?? ''),
                (string) ($row['cluster_code'] ?? ''),
                (string) ($row['regional_name'] ?? ''),
                (string) ($row['city_name'] ?? ''),
                $periodLabel,
                (string) (int) ($row['hp_donasi'] ?? 0),
                (string) (int) ($row['homepass_drm'] ?? 0),
                (string) ($row['released_at'] ?? ''),
                (string) ($row['drm_date'] ?? ''),
                (string) ($row['display_status_drm'] ?? $row['status_drm'] ?? ''),
                (string) ($row['status_current'] ?? ''),
                (string) ($row['nama_olt'] ?? ''),
                (string) ($row['remark_drm'] ?? ''),
            ]);
        }

        fclose($output);
        exit;
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

        if (empty($_FILES['screenshot_astri']['name'])) {
            $this->session->set_flashdata('error', 'Screenshoot Astri wajib dilampirkan.');
            redirect('DRM_MyRep');
            return;
        }

        if (
            !$this->db->field_exists('screenshot_astri_path', 'tb_myrep_drm')
            || !$this->db->field_exists('screenshot_astri_name', 'tb_myrep_drm')
        ) {
            $this->session->set_flashdata('error', 'Kolom screenshot Astri belum tersedia. Jalankan patch database terlebih dahulu.');
            redirect('DRM_MyRep');
            return;
        }

        $uploadResult = $this->uploadDrmAstriScreenshot();
        if (!$uploadResult['status']) {
            $this->session->set_flashdata('error', $uploadResult['message']);
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
            'screenshot_astri_path' => $uploadResult['file_path'],
            'screenshot_astri_name' => $uploadResult['file_name'],
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

    private function uploadDrmAstriScreenshot()
    {
        $targetDir = FCPATH . 'uploads/myrep_drm_astri/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        $config = [
            'upload_path' => $targetDir,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size' => 4096,
            'encrypt_name' => true
        ];

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('screenshot_astri')) {
            return [
                'status' => false,
                'message' => strip_tags($this->upload->display_errors('', ''))
            ];
        }

        $fileData = $this->upload->data();

        return [
            'status' => true,
            'file_name' => (string) ($fileData['file_name'] ?? ''),
            'file_path' => 'uploads/myrep_drm_astri/' . (string) ($fileData['file_name'] ?? '')
        ];
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
                'allowed_types' => '*',
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
                    'allowed_types' => '*',
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
                'allowed_types' => '*',
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

            $uploadedFilePath = FCPATH . 'uploads/myrep_drm/' . $fileData['file_name'];
            $parsedFromFile = $this->collectBoqItemsFromUploadedFile($uploadedFilePath, (string) $fileData['file_name'], $scopeType);
            if (!empty($parsedFromFile['items'])) {
                $items = $parsedFromFile['items'];
                if (!empty($parsedFromFile['warnings'])) {
                    $this->session->set_flashdata('warning', implode(' ', $parsedFromFile['warnings']));
                }
            }
        }

        if (empty($items)) {
            $this->session->set_flashdata('error', 'BOQ manual wajib diisi minimal satu item dengan qty lebih dari nol (input manual atau hasil parsing file APD BOQ).');
            redirect('DRM_MyRep/detail/' . $clusterId);
            return;
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

    public function previewApdBoqParse()
    {
        $bufferLevel = ob_get_level();
        ob_start();
        if (empty($this->session->userdata('id_user'))) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            $this->jsonResponse(false, 'Session login tidak ditemukan.');
            return;
        }
        if (!$this->MDRM_MyRep->drmBoqTablesReady()) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            $this->jsonResponse(false, 'Tabel BOQ DRM belum tersedia.');
            return;
        }
        if (empty($_FILES['apd_boq_file']['name']) || empty($_FILES['apd_boq_file']['tmp_name'])) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            $this->jsonResponse(false, 'File APD BOQ belum dipilih.');
            return;
        }

        $scopeType = $this->normalizeScopeType($this->input->post('scope_type'));
        $result = $this->collectBoqItemsFromUploadedFile(
            (string) $_FILES['apd_boq_file']['tmp_name'],
            (string) $_FILES['apd_boq_file']['name'],
            $scopeType
        );

        $strayOutput = '';
        while (ob_get_level() > $bufferLevel) {
            $strayOutput .= ob_get_clean();
        }

        $status = !empty($result['items']);
        $warnings = array_values((array) ($result['warnings'] ?? []));
        if (trim($strayOutput) !== '') {
            $warnings[] = 'Terdapat output non-JSON dari library saat parsing dan sudah di-sanitize oleh server.';
            log_message('error', '[DRM_MyRep][previewApdBoqParse] stray output: ' . substr(strip_tags($strayOutput), 0, 1000));
        }

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => $status,
            'message' => $status
                ? 'Parsing berhasil: ' . count($result['items']) . ' item terpetakan.'
                : 'Parsing tidak menghasilkan item yang bisa dipakai.',
            'items' => array_values((array) ($result['items'] ?? [])),
            'warnings' => $warnings,
            'debug' => array_values((array) ($result['debug'] ?? [])),
        ]));
    }

    private function collectBoqItemsFromUploadedFile($fullPath, $originalName = '', $scopeType = 'CLUSTER')
    {
        $fullPath = trim((string) $fullPath);
        if ($fullPath === '' || !is_file($fullPath)) {
            return ['items' => [], 'warnings' => ['File APD BOQ tidak ditemukan untuk diparsing.']];
        }

        $scopeType = $this->normalizeScopeType($scopeType);
        $extensionSource = $originalName !== '' ? $originalName : $fullPath;
        $extension = strtolower(pathinfo($extensionSource, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xls', 'xlsx', 'csv'], true)) {
            return ['items' => [], 'warnings' => ['Format APD BOQ bukan xls/xlsx/csv, parsing manual BOQ dilewati.']];
        }
        if ($extension === 'xlsx' && !class_exists('ZipArchive')) {
            return [
                'items' => [],
                'warnings' => [
                    'Server belum mendukung parsing file .xlsx (ekstensi PHP ZipArchive belum aktif). Gunakan .xls/.csv atau aktifkan ekstensi zip di PHP.'
                ],
            ];
        }

        $sheetData = [];
        $sheetParseCandidates = [];
        $previousErrorReporting = error_reporting();
        try {
            error_reporting($previousErrorReporting & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_USER_WARNING & ~E_USER_NOTICE);
            $this->loadPHPExcel();
            if ($extension === 'csv') {
                $sheetData = $this->readCsvSheetData($fullPath);
            } else {
                $excel = PHPExcel_IOFactory::load($fullPath);
                $worksheets = $excel->getAllSheets();
                foreach ($worksheets as $worksheet) {
                    $sheetParseCandidates[] = [
                        'name' => (string) $worksheet->getTitle(),
                        'state' => (string) $worksheet->getSheetState(),
                        'rows' => $this->worksheetToArraySafe($worksheet),
                    ];
                }
            }
        } catch (Exception $e) {
            log_message('error', '[DRM_MyRep] gagal parsing APD BOQ: ' . $e->getMessage());
            return ['items' => [], 'warnings' => ['File APD BOQ gagal diparsing, silakan isi manual BOQ seperti biasa.']];
        } catch (Error $e) {
            log_message('error', '[DRM_MyRep] gagal parsing APD BOQ (error): ' . $e->getMessage());
            return ['items' => [], 'warnings' => ['File APD BOQ gagal diparsing karena dependency server belum lengkap.']];
        } finally {
            error_reporting($previousErrorReporting);
        }

        if ($extension === 'csv' && (empty($sheetData) || !is_array($sheetData))) {
            return ['items' => [], 'warnings' => ['Sheet APD BOQ kosong, parsing manual BOQ dilewati.']];
        }

        $masterItems = $this->MDRM_MyRep->getBoqMasterItems();
        $masterMap = [];
        $masterMeta = [];
        $masterById = [];
        foreach ($masterItems as $masterItem) {
            $id = (int) ($masterItem['id_boq_item'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $masterById[$id] = $masterItem;
            $excelKey = $this->normalizeBoqItemName((string) ($masterItem['excel_item_name'] ?? ''));
            $itemKey = $this->normalizeBoqItemName((string) ($masterItem['item_name'] ?? ''));
            $excelFlat = $this->normalizeBoqItemNameFlat((string) ($masterItem['excel_item_name'] ?? ''));
            $itemFlat = $this->normalizeBoqItemNameFlat((string) ($masterItem['item_name'] ?? ''));
            if ($excelKey !== '') {
                $masterMap[$excelKey] = $id;
            }
            if ($itemKey !== '' && !isset($masterMap[$itemKey])) {
                $masterMap[$itemKey] = $id;
            }
            $masterMeta[$id] = [
                'excel_key' => $excelKey,
                'item_key' => $itemKey,
                'excel_flat' => $excelFlat,
                'item_flat' => $itemFlat,
            ];
        }

        $parseSheetGroups = [];
        if ($extension === 'csv') {
            $parseSheetGroups[] = [[
                'name' => 'CSV',
                'rows' => $sheetData,
            ]];
        } else {
            $preferredSheetName = $scopeType === 'SUBFEEDER' ? 'BoQ NRO All Feeder' : 'BoQ NRO Cluster';
            $preferred = [];
            $visibleOthers = [];
            $hiddenOthers = [];
            foreach ($sheetParseCandidates as $candidate) {
                $candidateName = trim((string) ($candidate['name'] ?? ''));
                if (strcasecmp($candidateName, $preferredSheetName) === 0) {
                    $preferred[] = $candidate;
                    continue;
                }

                $sheetState = (string) ($candidate['state'] ?? PHPExcel_Worksheet::SHEETSTATE_VISIBLE);
                if ($sheetState === PHPExcel_Worksheet::SHEETSTATE_VISIBLE) {
                    $visibleOthers[] = $candidate;
                } else {
                    $hiddenOthers[] = $candidate;
                }
            }
            $fallbackSheets = array_merge($visibleOthers, $hiddenOthers);
            if (!empty($preferred)) {
                $parseSheetGroups[] = $preferred;
                if (!empty($fallbackSheets)) {
                    $parseSheetGroups[] = $fallbackSheets;
                }
            } elseif (!empty($fallbackSheets)) {
                $parseSheetGroups[] = $fallbackSheets;
            }
        }

        if (empty($parseSheetGroups)) {
            return ['items' => [], 'warnings' => ['Tidak ada sheet yang bisa diproses dari file APD BOQ.']];
        }

        $bestResult = [
            'sheet_name' => '',
            'aggregates' => [],
            'unknown_rows' => [],
            'item_warnings' => [],
            'mapped_row_count' => 0,
            'debug' => [],
        ];
        foreach ($parseSheetGroups as $parseSheets) {
            $groupBestResult = [
                'sheet_name' => '',
                'aggregates' => [],
                'unknown_rows' => [],
                'item_warnings' => [],
                'mapped_row_count' => 0,
                'debug' => [],
            ];

            foreach ($parseSheets as $sheet) {
                $sheetRows = (array) ($sheet['rows'] ?? []);
                $sheetName = (string) ($sheet['name'] ?? 'Sheet');
                $columnCandidates = [['item' => 'B', 'qty' => 'E']];
                $detectedColumns = $this->detectBoqColumnsFromSheet($sheetRows);
                if (!empty($detectedColumns)) {
                    $alreadyExists = false;
                    foreach ($columnCandidates as $candidate) {
                        if ($candidate['item'] === $detectedColumns['item'] && $candidate['qty'] === $detectedColumns['qty']) {
                            $alreadyExists = true;
                            break;
                        }
                    }
                    if (!$alreadyExists) {
                        $columnCandidates[] = $detectedColumns;
                    }
                }

                $aggregates = [];
                $unknownRows = [];
                $itemWarnings = [];
                $mappedRowCount = 0;
                $sheetDebug = [];

                foreach ($columnCandidates as $columnCandidate) {
                    $currentAggregates = [];
                    $currentUnknownRows = [];
                    $currentItemWarnings = [];
                    $currentMappedRowCount = 0;
                    $currentDebug = [];
                    $itemColumn = (string) ($columnCandidate['item'] ?? 'B');
                    $qtyColumn = (string) ($columnCandidate['qty'] ?? 'E');

                    foreach ($sheetRows as $rowNumber => $row) {
                        $itemNameRaw = trim((string) ($row[$itemColumn] ?? ''));
                        if ($itemNameRaw === '') {
                            continue;
                        }

                        $qtyResult = $this->resolveBoqQtyFromRow((array) $row, $qtyColumn);
                        $qty = (float) ($qtyResult['qty'] ?? 0);
                        if ($qty <= 0) {
                            continue;
                        }

                        $normalizedName = $this->normalizeBoqItemName($itemNameRaw);
                        if ($normalizedName === '') {
                            continue;
                        }

                        $boqItemId = $this->resolveBoqItemIdFromName($normalizedName, $masterMap, $masterMeta);
                        if ($boqItemId <= 0) {
                            $currentUnknownRows[] = 'Sheet ' . $sheetName . ' [' . $itemColumn . '/' . $qtyColumn . '] baris ' . (int) $rowNumber . ' item "' . $itemNameRaw . '" tidak cocok dengan master BOQ.';
                            if (count($currentDebug) < 8) {
                                $currentDebug[] = [
                                    'row' => (int) $rowNumber,
                                    'item_raw' => $itemNameRaw,
                                    'item_normalized' => $normalizedName,
                                    'qty' => $qty,
                                    'matched_id' => 0,
                                    'columns' => $itemColumn . '/' . $qtyColumn,
                                ];
                            }
                            continue;
                        }

                        $currentMappedRowCount++;
                        $qtyWarning = trim((string) ($qtyResult['warning'] ?? ''));
                        if ($qtyWarning !== '') {
                            if (!isset($currentItemWarnings[$boqItemId])) {
                                $currentItemWarnings[$boqItemId] = [];
                            }
                            $currentItemWarnings[$boqItemId][] = 'Baris ' . (int) $rowNumber . ': ' . $qtyWarning;
                        }
                        if (!isset($currentAggregates[$boqItemId])) {
                            $currentAggregates[$boqItemId] = 0;
                        }
                        $currentAggregates[$boqItemId] += (float) $qty;
                        if (count($currentDebug) < 8) {
                            $currentDebug[] = [
                                'row' => (int) $rowNumber,
                                'item_raw' => $itemNameRaw,
                                'item_normalized' => $normalizedName,
                                'qty' => $qty,
                                'matched_id' => (int) $boqItemId,
                                'columns' => $itemColumn . '/' . $qtyColumn,
                            ];
                        }
                    }

                    if ($currentMappedRowCount > $mappedRowCount) {
                        $aggregates = $currentAggregates;
                        $unknownRows = $currentUnknownRows;
                        $itemWarnings = $currentItemWarnings;
                        $mappedRowCount = $currentMappedRowCount;
                        $sheetDebug = $currentDebug;
                    }
                }

                if ($mappedRowCount > $groupBestResult['mapped_row_count']) {
                    $groupBestResult = [
                        'sheet_name' => $sheetName,
                        'aggregates' => $aggregates,
                        'unknown_rows' => $unknownRows,
                        'item_warnings' => $itemWarnings,
                        'mapped_row_count' => $mappedRowCount,
                        'debug' => $sheetDebug,
                    ];
                }
            }

            if ($groupBestResult['mapped_row_count'] > $bestResult['mapped_row_count']) {
                $bestResult = $groupBestResult;
            }
            if ($groupBestResult['mapped_row_count'] > 0) {
                break;
            }
        }

        $aggregates = (array) $bestResult['aggregates'];
        $unknownRows = (array) $bestResult['unknown_rows'];
        $itemWarnings = (array) $bestResult['item_warnings'];

        if (empty($aggregates)) {
            return ['items' => [], 'warnings' => $unknownRows];
        }

        $items = [];
        foreach ($aggregates as $boqItemId => $qty) {
            $masterItem = $masterById[(int) $boqItemId] ?? [];
            if (empty($masterItem)) {
                continue;
            }
            $photoQty = (int) ($masterItem['default_photo_qty'] ?? 0);
            $remarksRule = strtoupper(trim((string) ($masterItem['remarks_rule'] ?? 'SESUAI ITEM')));
            $remarksRule = $remarksRule === 'SAMPLING' ? 'SAMPLING' : 'SESUAI ITEM';
            $targetFoto = $remarksRule === 'SAMPLING'
                ? $photoQty
                : (int) round(((float) $qty) * $photoQty);

            $items[] = [
                'id_boq_item' => (int) $boqItemId,
                'qty_boq' => (float) $qty,
                'jumlah_foto' => max($photoQty, 0),
                'remarks_rule' => $remarksRule,
                'target_foto_required' => max($targetFoto, 0),
                'item_note' => null,
                'warnings' => array_values((array) ($itemWarnings[(int) $boqItemId] ?? [])),
            ];
        }

        $warnings = $unknownRows;
        if ($bestResult['sheet_name'] !== '') {
            $preferredSheetName = $scopeType === 'SUBFEEDER' ? 'BoQ NRO All Feeder' : 'BoQ NRO Cluster';
            array_unshift($warnings, 'Sheet parsing terpilih: ' . $bestResult['sheet_name'] . ' (' . (int) $bestResult['mapped_row_count'] . ' baris termapping). Target scope: ' . $scopeType . ' (prefer "' . $preferredSheetName . '").');
        }

        return ['items' => $items, 'warnings' => $warnings, 'debug' => (array) ($bestResult['debug'] ?? [])];
    }

    private function worksheetToArraySafe(PHPExcel_Worksheet $worksheet)
    {
        $rows = [];
        $highestRow = (int) $worksheet->getHighestRow();
        $highestColumn = (string) $worksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($colIndex = 0; $colIndex < $highestColumnIndex; $colIndex++) {
                $column = PHPExcel_Cell::stringFromColumnIndex($colIndex);
                $cell = $worksheet->getCell($column . $row);
                if ($cell === null) {
                    $rowData[$column] = '';
                    continue;
                }

                $value = $cell->getValue();
                if (is_string($value) && strlen($value) > 0 && $value[0] === '=') {
                    $cachedValue = $cell->getOldCalculatedValue();
                    if ($cachedValue !== null && $cachedValue !== '') {
                        $value = $cachedValue;
                    } else {
                        // Jangan paksa evaluasi formula di server agar parsing tidak gagal total.
                        $value = '';
                    }
                }

                $rowData[$column] = $value;
            }
            $rows[$row] = $rowData;
        }

        return $rows;
    }

    private function normalizeBoqItemName($value)
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/^\s*\d+\s*[.)-]?\s*/', '', $value);
        $value = str_replace(['“', '”', '’', '`'], "'", $value);
        $value = str_replace(['—', '–'], '-', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = str_replace([' ,', ' .'], [',', '.'], $value);
        $value = str_replace([' ,', ', ', ' .', '. '], [',', ',', '.', '.'], $value);
        $value = str_replace([' "', '" ', " '", "' "], ['"', '"', "'", "'"], $value);
        $value = str_replace('STEL-L003', 'STEL L-003', $value);
        $value = str_replace('STEL -003', 'STEL L-003', $value);
        $value = preg_replace('/\s*\/\s*/', '/', $value);
        return trim((string) $value);
    }

    private function normalizeBoqItemNameFlat($value)
    {
        $value = $this->normalizeBoqItemName($value);
        if ($value === '') {
            return '';
        }
        $value = preg_replace('/[^A-Z0-9]/', '', $value);
        return trim((string) $value);
    }

    private function resolveBoqItemIdFromName($normalizedName, array $masterMap, array $masterMeta)
    {
        $normalizedName = trim((string) $normalizedName);
        if ($normalizedName === '') {
            return 0;
        }

        if (isset($masterMap[$normalizedName])) {
            return (int) $masterMap[$normalizedName];
        }

        $flat = $this->normalizeBoqItemNameFlat($normalizedName);
        if ($flat === '') {
            return 0;
        }

        foreach ($masterMeta as $id => $meta) {
            $excelFlat = (string) ($meta['excel_flat'] ?? '');
            $itemFlat = (string) ($meta['item_flat'] ?? '');
            if ($flat !== '' && ($flat === $excelFlat || $flat === $itemFlat)) {
                return (int) $id;
            }
        }

        foreach ($masterMeta as $id => $meta) {
            $excelKey = (string) ($meta['excel_key'] ?? '');
            $itemKey = (string) ($meta['item_key'] ?? '');
            $excelFlat = (string) ($meta['excel_flat'] ?? '');
            $itemFlat = (string) ($meta['item_flat'] ?? '');
            if (
                ($excelFlat !== '' && $this->isBoqFuzzyNameMatch($normalizedName, $excelKey, $flat, $excelFlat)) ||
                ($itemFlat !== '' && $this->isBoqFuzzyNameMatch($normalizedName, $itemKey, $flat, $itemFlat))
            ) {
                return (int) $id;
            }
        }

        return 0;
    }

    private function resolveBoqQtyFromRow(array $row, $qtyColumn = 'E')
    {
        $qtyColumn = strtoupper(trim((string) $qtyColumn));
        if ($qtyColumn === 'D' || $qtyColumn === 'E') {
            $qtyD = $this->normalizeNumber($row['D'] ?? '');
            $qtyE = $this->normalizeNumber($row['E'] ?? '');

            if ($qtyD <= 0 && $qtyE <= 0) {
                return ['qty' => 0, 'warning' => ''];
            }
            if ($qtyD > 0 && $qtyE > 0) {
                if (abs($qtyD - $qtyE) < 0.00001) {
                    return ['qty' => (float) $qtyE, 'warning' => ''];
                }

                return [
                    'qty' => (float) $qtyE,
                    'warning' => 'Qty tidak match: kolom D=' . $this->formatBoqWarningNumber($qtyD) . ', kolom E=' . $this->formatBoqWarningNumber($qtyE) . '. Qty dipakai=' . $this->formatBoqWarningNumber($qtyE) . ' (kolom E).',
                ];
            }

            return ['qty' => (float) ($qtyE > 0 ? $qtyE : $qtyD), 'warning' => ''];
        }

        return ['qty' => $this->normalizeNumber($row[$qtyColumn] ?? ''), 'warning' => ''];
    }

    private function formatBoqWarningNumber($value)
    {
        $value = (float) $value;
        return rtrim(rtrim(number_format($value, 6, '.', ''), '0'), '.');
    }

    private function isBoqFuzzyNameMatch($sourceName, $masterName, $sourceFlat, $masterFlat)
    {
        $sourceFlat = (string) $sourceFlat;
        $masterFlat = (string) $masterFlat;
        if ($sourceFlat === '' || $masterFlat === '') {
            return false;
        }

        if (strpos($sourceFlat, $masterFlat) === false && strpos($masterFlat, $sourceFlat) === false) {
            return false;
        }

        $sourceLength = strlen($sourceFlat);
        $masterLength = strlen($masterFlat);
        $longestLength = max($sourceLength, $masterLength);
        if ($longestLength <= 0 || (min($sourceLength, $masterLength) / $longestLength) < 0.85) {
            return false;
        }

        return $this->getBoqNameWorkSide($sourceName) === $this->getBoqNameWorkSide($masterName);
    }

    private function getBoqNameWorkSide($name)
    {
        $name = strtoupper(trim((string) $name));
        if (preg_match('/^(INSTAL|INSTALL|INSTALLATION|PEMASANGAN|PASANG)\b/', $name)) {
            return 'SERVICE';
        }

        return 'MATERIAL';
    }

    private function detectBoqColumnsFromSheet(array $sheetRows)
    {
        $maxHeaderRow = 25;
        $itemKeywords = ['ITEM', 'URAIAN', 'PEKERJAAN', 'DESKRIPSI', 'MATERIAL'];
        $qtyKeywords = ['QTY', 'QUANTITY', 'VOLUME', 'VOL', 'JUMLAH'];

        foreach ($sheetRows as $rowNumber => $row) {
            if ((int) $rowNumber > $maxHeaderRow) {
                break;
            }
            if (!is_array($row)) {
                continue;
            }
            $itemColumn = '';
            $qtyColumn = '';
            foreach ($row as $column => $value) {
                $text = strtoupper(trim((string) $value));
                if ($text === '') {
                    continue;
                }
                foreach ($itemKeywords as $keyword) {
                    if (strpos($text, $keyword) !== false) {
                        $itemColumn = (string) $column;
                        break;
                    }
                }
                foreach ($qtyKeywords as $keyword) {
                    if (strpos($text, $keyword) !== false) {
                        $qtyColumn = (string) $column;
                        break;
                    }
                }
            }
            if ($itemColumn !== '' && $qtyColumn !== '') {
                return ['item' => $itemColumn, 'qty' => $qtyColumn];
            }
        }

        return [];
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

        $raw = trim((string) $value);
        if ($raw === '') {
            return 0;
        }

        // Format Indonesia ribuan: 1.276 atau 12.345.678 => 1276 / 12345678
        if (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $raw)) {
            return (float) str_replace('.', '', $raw);
        }

        // Format EN ribuan: 1,276 atau 12,345,678 => 1276 / 12345678
        if (preg_match('/^-?\d{1,3}(,\d{3})+$/', $raw)) {
            return (float) str_replace(',', '', $raw);
        }

        // Format Indonesia desimal dengan ribuan: 1.276,50 => 1276.50
        if (preg_match('/^-?\d{1,3}(\.\d{3})+,\d+$/', $raw)) {
            $normalized = str_replace('.', '', $raw);
            $normalized = str_replace(',', '.', $normalized);
            return (float) $normalized;
        }

        // Format EN desimal dengan ribuan: 1,276.50 => 1276.50
        if (preg_match('/^-?\d{1,3}(,\d{3})+\.\d+$/', $raw)) {
            $normalized = str_replace(',', '', $raw);
            return (float) $normalized;
        }

        // Format desimal koma tanpa ribuan: 12,5 => 12.5
        if (preg_match('/^-?\d+,\d+$/', $raw)) {
            return (float) str_replace(',', '.', $raw);
        }

        // Fallback: bersihkan karakter non numeric.
        $normalized = preg_replace('/[^\d,.\-]/', '', $raw);
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }

    private function normalizeUpperList($value)
    {
        $items = is_array($value) ? $value : [$value];
        $normalized = [];
        foreach ($items as $item) {
            $label = strtoupper(trim((string) $item));
            if ($label !== '') {
                $normalized[] = $label;
            }
        }
        return array_values(array_unique($normalized));
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
