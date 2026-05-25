<?php
defined('BASEPATH') or exit('No direct script access allowed');

class VALSAL_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MVALSAL_MyRep');
        $this->load->model('MMyRep_Cleanup');
        $this->load->library('upload');
        $this->load->library('Myrep_notification_service', null, 'myrepNotifier');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('VALSAL_MyRep');
            $this->myrepAccess->enforceByMethod('VALSAL_MyRep', (string) $this->router->fetch_method(), [
                'previewValsalImport' => 'TAMBAH',
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

        $data['title'] = 'VALSAL MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['isReady'] = $this->MVALSAL_MyRep->valsalTablesReady();
        $data['docReady'] = $this->MVALSAL_MyRep->valsalDocumentTablesReady();
        if ($data['docReady']) {
            $this->MVALSAL_MyRep->ensureValsalDocumentSetup();
        }
        $data['canApprove'] = $this->isApprover();
        $data['cityOptions'] = $this->MVALSAL_MyRep->getCityOptions();
        $data['regionalOptions'] = $this->MVALSAL_MyRep->getRegionalOptions();
        $data['cityOptionsByRegional'] = $this->MVALSAL_MyRep->getCityOptionsByRegional();
        $data['regionalOptionsByCity'] = $this->MVALSAL_MyRep->getRegionalOptionsByCity();
        $data['eligibleClusterOptions'] = $this->MVALSAL_MyRep->getEligibleClusterOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MVALSAL_MyRep->getValsalRows($selectedCity, $selectedStatus)
            : [];
        $clusterIds = array_values(array_filter(array_map(static function ($row) {
            return (int) ($row['id_myrep_cluster'] ?? 0);
        }, $data['clusterRows'])));
        $data['valsalDocumentDefinitions'] = $data['docReady'] ? $this->MVALSAL_MyRep->getValsalDocumentDefinitions() : [];
        $data['valsalDocumentMap'] = ($data['docReady'] && !empty($clusterIds))
            ? $this->MVALSAL_MyRep->getValsalDocumentItemsByClusterIds($clusterIds)
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
        if ($docReady) {
            $this->MVALSAL_MyRep->ensureValsalDocumentSetup();
        }
        $documentDefinitions = $docReady ? $this->MVALSAL_MyRep->getValsalDocumentDefinitions() : [];

        if ($clusterId <= 0 || $homepassValsal <= 0) {
            $this->session->set_flashdata('error', 'Cluster dan homepass VALSAL wajib diisi.');
            redirect('VALSAL_MyRep');
            return;
        }

        if ($docReady && empty($documentDefinitions)) {
            $this->session->set_flashdata('error', 'Konfigurasi dokumen VALSAL belum ditemukan.');
            redirect('VALSAL_MyRep');
            return;
        }

        if ($docReady) {
            foreach ($documentDefinitions as $documentDefinition) {
                $docItemId = (int) $documentDefinition['id_doc_item'];
                $fieldName = 'create_file_' . $docItemId;
                $isNoDocumentRequired = (int) $this->input->post('create_is_document_not_required_' . $docItemId) === 1;
                if (!$isNoDocumentRequired && empty($_FILES[$fieldName]['name'])) {
                    $this->session->set_flashdata('error', 'Dokumen ' . ($documentDefinition['doc_name'] ?? 'VALSAL') . ' wajib diupload atau tandai tidak dibutuhkan saat input VALSAL baru.');
                    redirect('VALSAL_MyRep');
                    return;
                }
            }
        }

        if ($docReady && count($documentDefinitions) < 3) {
            $this->session->set_flashdata('error', 'Konfigurasi 3 dokumen VALSAL belum lengkap.');
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
            foreach ($documentDefinitions as $documentDefinition) {
                $docItemId = (int) $documentDefinition['id_doc_item'];
                $context = $this->MVALSAL_MyRep->getValsalDocumentContext($clusterId, $docItemId);
                $isNoDocumentRequired = (int) $this->input->post('create_is_document_not_required_' . $docItemId) === 1;
                if (empty($context['id_doc_item'])) {
                    $this->MMyRep_Cleanup->deleteWholeCluster($clusterId);
                    $this->session->set_flashdata('error', 'Konfigurasi dokumen ' . ($documentDefinition['doc_name'] ?? 'VALSAL') . ' belum ditemukan.');
                    redirect('VALSAL_MyRep');
                    return;
                }

                $uploadResult = $isNoDocumentRequired
                    ? [
                        'status' => true,
                        'message' => '',
                        'file_name' => '',
                        'file_path' => '',
                    ]
                    : $this->storeValsalUploadFile($clusterId, $context, 'create_file_' . $docItemId);
                if (!$uploadResult['status']) {
                    $this->MMyRep_Cleanup->deleteWholeCluster($clusterId);
                    $this->session->set_flashdata('error', $uploadResult['message']);
                    redirect('VALSAL_MyRep');
                    return;
                }

                $fileId = $this->MVALSAL_MyRep->saveValsalFileUpload($clusterId, $docItemId, [
                    'file_name' => $uploadResult['file_name'],
                    'file_path' => $uploadResult['file_path'],
                    'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
                    'status_file' => 'UPLOADED',
                    'remark' => trim((string) $this->input->post('create_doc_remark_' . $docItemId)),
                    'uploaded_by' => $userId,
                ]);

                if ($fileId <= 0) {
                    $this->deleteStoredFile($uploadResult['file_path']);
                    $this->MMyRep_Cleanup->deleteWholeCluster($clusterId);
                    $this->session->set_flashdata('error', 'Dokumen ' . ($documentDefinition['doc_name'] ?? 'VALSAL') . ' gagal disimpan.');
                    redirect('VALSAL_MyRep');
                    return;
                }
            }

            $this->MVALSAL_MyRep->syncValsalStatusByCluster($clusterId, $userId);
            $this->sendValsalNotification('cluster_masuk', [
                'id_myrep_cluster' => $clusterId,
                'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
                'regional_name' => (string) ($cluster['regional_name'] ?? ''),
                'city_name' => (string) ($cluster['city_name'] ?? ''),
                'homepass_valsal' => $homepassValsal,
            ], 'VALSAL');
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
            $this->MVALSAL_MyRep->ensureValsalDocumentSetup();
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

        if ($this->MVALSAL_MyRep->valsalDocumentTablesReady()) {
            $this->MVALSAL_MyRep->syncValsalStatusByCluster($clusterId, $userId);
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

        $this->MVALSAL_MyRep->ensureValsalDocumentSetup();

        $clusterId = (int) $this->input->post('cluster_id');
        $docItemId = (int) $this->input->post('doc_item_id');
        $context = $this->MVALSAL_MyRep->getValsalDocumentContext($clusterId, $docItemId);
        if ($clusterId <= 0 || empty($context['id_doc_item'])) {
            $this->handleUploadError('Konfigurasi dokumen VALSAL belum ditemukan.', 'VALSAL_MyRep');
            return;
        }
        $notificationEvent = !empty($context['id_doc_file']) ? 'document_revised' : 'document_masuk';

        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;
        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->handleUploadError('File ' . ($context['doc_name'] ?? 'dokumen') . ' wajib dipilih.', 'VALSAL_MyRep');
            return;
        }

        $uploadResult = $this->storeValsalUploadFile($clusterId, $context, 'file');
        if (!$uploadResult['status']) {
            $this->handleUploadError($uploadResult['message'], 'VALSAL_MyRep');
            return;
        }

        $fileId = $this->MVALSAL_MyRep->saveValsalFileUpload($clusterId, $docItemId, [
            'file_name' => $uploadResult['file_name'],
            'file_path' => $uploadResult['file_path'],
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($fileId <= 0) {
            $this->handleUploadError('Dokumen ' . ($context['doc_name'] ?? 'VALSAL') . ' gagal disimpan.', 'VALSAL_MyRep');
            return;
        }

        $this->MVALSAL_MyRep->syncValsalStatusByCluster($clusterId, (int) $this->session->userdata('id_user'));
        $cluster = $this->MVALSAL_MyRep->getValsalByClusterId($clusterId);
        $this->sendValsalNotification($notificationEvent, $cluster, (string) ($context['doc_name'] ?? 'VALSAL'));

        $this->handleUploadSuccess(
            $isNoDocumentRequired ? 'Dokumen ' . ($context['doc_name'] ?? 'VALSAL') . ' ditandai tidak dibutuhkan dan dikirim ke review.' : 'Dokumen ' . ($context['doc_name'] ?? 'VALSAL') . ' berhasil diupload.',
            'VALSAL_MyRep'
        );
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
            redirect('VALSAL_MyRep');
            return;
        }

        $deleted = $this->MMyRep_Cleanup->deleteWholeCluster($clusterId);
        $this->session->set_flashdata($deleted ? 'success' : 'error', $deleted ? 'Cluster MyRep beserta flow VALSAL dan seluruh tahap sebelumnya berhasil dihapus bersih.' : 'Gagal menghapus cluster MyRep.');
        redirect('VALSAL_MyRep');
    }

    public function approveDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Session habis. Silakan login ulang.');
                return;
            }
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Anda tidak memiliki akses approve dokumen VALSAL.');
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen VALSAL.');
            redirect('VALSAL_MyRep');
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        if ($fileId <= 0) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Dokumen VALSAL tidak ditemukan.');
                return;
            }
            $this->session->set_flashdata('error', 'Dokumen VALSAL tidak ditemukan.');
            redirect('VALSAL_MyRep');
            return;
        }

        $file = $this->MVALSAL_MyRep->getValsalFileById($fileId);
        if (empty($file['id_myrep_cluster'])) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Data cluster dokumen VALSAL tidak ditemukan.');
                return;
            }
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
            $this->MVALSAL_MyRep->syncValsalStatusByCluster((int) $file['id_myrep_cluster'], (int) $this->session->userdata('id_user'));
        }

        $docName = (string) ($file['doc_name'] ?? 'VALSAL');
        if ($this->isAjaxRequest()) {
            if ($result) {
                $response = $this->buildClusterDocumentResponse((int) $file['id_myrep_cluster']);
                $response['message'] = 'Dokumen ' . $docName . ' berhasil di-approve.';
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => true,
                        'message' => $response['message'],
                        'data' => $response,
                    ]));
                return;
            }

            $this->jsonResponse(false, 'Gagal approve dokumen ' . $docName . '.');
            return;
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen ' . $docName . ' berhasil di-approve.' : 'Gagal approve dokumen ' . $docName . '.');
        redirect('VALSAL_MyRep');
    }

    public function rejectDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Session habis. Silakan login ulang.');
                return;
            }
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Anda tidak memiliki akses reject dokumen VALSAL.');
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen VALSAL.');
            redirect('VALSAL_MyRep');
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        if ($fileId <= 0) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Dokumen VALSAL tidak ditemukan.');
                return;
            }
            $this->session->set_flashdata('error', 'Dokumen VALSAL tidak ditemukan.');
            redirect('VALSAL_MyRep');
            return;
        }

        $file = $this->MVALSAL_MyRep->getValsalFileById($fileId);
        if (empty($file['id_myrep_cluster'])) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Data cluster dokumen VALSAL tidak ditemukan.');
                return;
            }
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
            $this->MVALSAL_MyRep->syncValsalStatusByCluster((int) $file['id_myrep_cluster'], (int) $this->session->userdata('id_user'));
        }

        $docName = (string) ($file['doc_name'] ?? 'VALSAL');
        if ($this->isAjaxRequest()) {
            if ($result) {
                $response = $this->buildClusterDocumentResponse((int) $file['id_myrep_cluster']);
                $response['message'] = 'Dokumen ' . $docName . ' berhasil di-reject.';
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => true,
                        'message' => $response['message'],
                        'data' => $response,
                    ]));
                return;
            }

            $this->jsonResponse(false, 'Gagal reject dokumen ' . $docName . '.');
            return;
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen ' . $docName . ' berhasil di-reject.' : 'Gagal reject dokumen ' . $docName . '.');
        redirect('VALSAL_MyRep');
    }

    public function approveAllDocuments()
    {
        if (empty($this->session->userdata('id_user'))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Session habis. Silakan login ulang.');
                return;
            }
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Anda tidak memiliki akses approve semua dokumen VALSAL.');
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve semua dokumen VALSAL.');
            redirect('VALSAL_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if ($clusterId <= 0) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Cluster dokumen VALSAL tidak valid.');
                return;
            }
            $this->session->set_flashdata('error', 'Cluster dokumen VALSAL tidak valid.');
            redirect('VALSAL_MyRep');
            return;
        }

        $documentMap = $this->MVALSAL_MyRep->getValsalDocumentItemsByClusterIds([$clusterId]);
        $documentRows = array_values($documentMap[$clusterId] ?? []);
        if (empty($documentRows)) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Dokumen VALSAL untuk cluster ini belum tersedia.');
                return;
            }
            $this->session->set_flashdata('error', 'Dokumen VALSAL untuk cluster ini belum tersedia.');
            redirect('VALSAL_MyRep');
            return;
        }

        $approvedBy = (int) $this->session->userdata('id_user');
        $updatedCount = 0;
        foreach ($documentRows as $documentRow) {
            $fileId = (int) ($documentRow['id_doc_file'] ?? 0);
            $status = strtoupper(trim((string) ($documentRow['status_file'] ?? '')));
            if ($fileId <= 0 || !in_array($status, ['UPLOADED', 'REJECTED'], true)) {
                continue;
            }

            $result = $this->MVALSAL_MyRep->updateValsalFileStatus($fileId, [
                'status_file' => 'APPROVED',
                'remark' => trim((string) ($documentRow['remark'] ?? '')),
                'approved_by' => $approvedBy,
            ]);

            if ($result) {
                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            $this->MVALSAL_MyRep->syncValsalStatusByCluster($clusterId, $approvedBy);
        }

        if ($this->isAjaxRequest()) {
            if ($updatedCount > 0) {
                $response = $this->buildClusterDocumentResponse($clusterId);
                $response['message'] = $updatedCount . ' dokumen berhasil di-approve sekaligus.';
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => true,
                        'message' => $response['message'],
                        'data' => $response,
                    ]));
                return;
            }

            $this->jsonResponse(false, 'Tidak ada dokumen yang bisa di-approve sekaligus.');
            return;
        }

        $this->session->set_flashdata($updatedCount > 0 ? 'success' : 'error', $updatedCount > 0 ? ($updatedCount . ' dokumen berhasil di-approve sekaligus.') : 'Tidak ada dokumen yang bisa di-approve sekaligus.');
        redirect('VALSAL_MyRep');
    }

    public function downloadReport()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MVALSAL_MyRep->valsalTablesReady()) {
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
        $valsalDateStart = $this->normalizeDate($this->input->get('valsal_date_start')) ?: '';
        $valsalDateEnd = $this->normalizeDate($this->input->get('valsal_date_end')) ?: '';

        $rows = $this->MVALSAL_MyRep->getValsalRows($selectedCity, $selectedStatus, $selectedRegional, $cityList, $regionalList, $valsalDateStart, $valsalDateEnd);

        $documentDefinitions = [];
        $documentMap = [];
        if ($this->MVALSAL_MyRep->valsalDocumentTablesReady()) {
            $this->MVALSAL_MyRep->ensureValsalDocumentSetup();
            $documentDefinitions = $this->MVALSAL_MyRep->getValsalDocumentDefinitions();
            $clusterIds = array_values(array_filter(array_map(static function ($row) {
                return (int) ($row['id_myrep_cluster'] ?? 0);
            }, $rows)));
            if (!empty($clusterIds)) {
                $documentMap = $this->MVALSAL_MyRep->getValsalDocumentItemsByClusterIds($clusterIds);
            }
        }

        $filename = 'report_valsal_myrep_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array_merge([
            'Cluster',
            'Kode Cluster',
            'Regional',
            'Provinsi',
            'Kota',
            'Periode Target',
            'HP BAK',
            'HP VALSAL',
            'Tanggal BAK',
            'Tanggal VALSAL',
            'Status VALSAL',
            'Status Flow',
            'Remark VALSAL',
        ], $this->buildReportDocumentHeaders($documentDefinitions)));

        foreach ($rows as $row) {
            $clusterDocs = $documentMap[(int) ($row['id_myrep_cluster'] ?? 0)] ?? [];
            $docIndexed = [];
            foreach ($clusterDocs as $docRow) {
                $docIndexed[(int) ($docRow['id_doc_item'] ?? 0)] = $docRow;
            }

            $periodLabel = !empty($row['year_num']) && !empty($row['month_num'])
                ? sprintf('%02d/%04d', (int) $row['month_num'], (int) $row['year_num'])
                : '-';

            $csvRow = [
                (string) ($row['cluster_name'] ?? ''),
                (string) ($row['cluster_code'] ?? ''),
                (string) ($row['regional_name'] ?? ''),
                (string) ($row['province_name'] ?? ''),
                (string) ($row['city_name'] ?? ''),
                $periodLabel,
                (string) (int) ($row['homepass_bak'] ?? 0),
                (string) (int) ($row['homepass_valsal'] ?? 0),
                (string) ($row['bak_date'] ?? ''),
                (string) ($row['valsal_date'] ?? ''),
                (string) ($row['status_valsal'] ?? ''),
                (string) ($row['status_current'] ?? ''),
                (string) ($row['remark_valsal'] ?? ''),
            ];

            foreach ($documentDefinitions as $documentDefinition) {
                $docRow = $docIndexed[(int) $documentDefinition['id_doc_item']] ?? [];
                $csvRow[] = (string) $this->resolveDocumentStatusLabel($docRow);
                $csvRow[] = (string) ($docRow['file_name'] ?? '');
                $csvRow[] = (string) ($docRow['remark'] ?? '');
            }

            fputcsv($output, $csvRow);
        }

        fclose($output);
        exit;
    }

    public function downloadValsalImportTemplate()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $filename = 'format_import_valsal_myrep_' . date('Ymd_His') . '.csv';
        $headers = [
            'cluster_id',
            'id_target',
            'city_name',
            'cluster_name',
            'cluster_code',
            'homepass_valsal',
            'valsal_date',
            'status_valsal',
            'remark_valsal',
        ];

        $exampleRows = [
            ['', '', 'MALANG', 'Cluster A', 'CL-A', '100', date('Y-m-d'), 'DRAFT', 'Contoh status DRAFT'],
            ['', '', 'MALANG', 'Cluster B', 'CL-B', '120', date('Y-m-d'), 'ON REVIEW', 'Contoh status ON REVIEW'],
            ['', '', 'MALANG', 'Cluster C', 'CL-C', '90', date('Y-m-d'), 'REJECTED', 'Contoh status REJECTED'],
            ['', '', 'MALANG', 'Cluster D', 'CL-D', '110', date('Y-m-d'), 'DONE', 'Contoh status DONE'],
            ['', '', 'MALANG', 'Cluster E', 'CL-E', '130', date('Y-m-d'), 'APPROVED', 'Contoh status APPROVED'],
        ];

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $headers);
        foreach ($exampleRows as $exampleRow) {
            fputcsv($output, $exampleRow);
        }
        fclose($output);
        exit;
    }

    public function previewValsalImport()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session login tidak ditemukan.');
            return;
        }

        if (!$this->MVALSAL_MyRep->valsalTablesReady()) {
            $this->jsonResponse(false, 'Tabel VALSAL MyRep belum tersedia.');
            return;
        }

        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size'] = 4096;
        $config['encrypt_name'] = true;

        if (!is_dir($config['upload_path'])) {
            @mkdir($config['upload_path'], 0777, true);
        }

        $this->upload->initialize($config);
        if (!$this->upload->do_upload('file_excel')) {
            $this->jsonResponse(false, strip_tags($this->upload->display_errors('', '')));
            return;
        }

        $fileData = $this->upload->data();
        $filePath = $fileData['full_path'];

        try {
            $extension = strtolower(pathinfo($fileData['file_name'], PATHINFO_EXTENSION));
            if ($extension === 'csv') {
                $this->loadPHPExcel();
                $sheetData = $this->readCsvSheetData($filePath);
            } else {
                $this->loadPHPExcel();
                $objPHPExcel = PHPExcel_IOFactory::load($filePath);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            }
        } catch (Exception $e) {
            @unlink($filePath);
            $this->jsonResponse(false, 'File import VALSAL tidak bisa dibaca.');
            return;
        }

        @unlink($filePath);

        if (count($sheetData) < 2) {
            $this->jsonResponse(false, 'File import VALSAL tidak memiliki data.');
            return;
        }

        $headerRow = reset($sheetData);
        $headerMap = [];
        foreach ($headerRow as $column => $header) {
            $mappedField = $this->parseValsalImportHeader($header);
            if ($mappedField) {
                $headerMap[$column] = $mappedField;
            }
        }

        foreach (['homepass_valsal', 'status_valsal'] as $requiredField) {
            if (!in_array($requiredField, $headerMap, true)) {
                $this->jsonResponse(false, 'Header file wajib memuat ' . $requiredField . '.');
                return;
            }
        }

        if (
            !in_array('cluster_id', $headerMap, true)
            && !in_array('cluster_name', $headerMap, true)
        ) {
            $this->jsonResponse(false, 'Header file wajib memuat cluster_id atau cluster_name.');
            return;
        }

        $rows = [];
        foreach ($sheetData as $rowIndex => $excelRow) {
            if ($rowIndex === 1) {
                continue;
            }

            $row = [];
            foreach ($headerMap as $column => $field) {
                $row[$field] = isset($excelRow[$column]) ? trim((string) $excelRow[$column]) : '';
            }

            $isBlank = true;
            foreach ($row as $value) {
                if (trim((string) $value) !== '') {
                    $isBlank = false;
                    break;
                }
            }

            if (!$isBlank) {
                $rows[] = $row;
            }
        }

        $validated = $this->validateValsalImportRows($rows);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'message' => count($validated['valid_rows']) . ' data valid dari ' . count($validated['rows']) . ' baris',
                'rows' => $validated['rows'],
                'valid_rows' => $validated['valid_rows'],
                'error_rows' => $validated['errors'],
            ]));
    }

    public function saveImportedValsal()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session login tidak ditemukan.');
            return;
        }

        if (!$this->MVALSAL_MyRep->valsalTablesReady()) {
            $this->jsonResponse(false, 'Tabel VALSAL MyRep belum tersedia.');
            return;
        }

        $rowsJson = $this->input->post('rows_json');
        $rows = json_decode((string) $rowsJson, true);
        if (empty($rows) || !is_array($rows)) {
            $this->jsonResponse(false, 'Tidak ada data import yang siap disimpan.');
            return;
        }

        $validated = $this->validateValsalImportRows($rows);
        if (empty($validated['valid_rows'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
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
            $targetId = (int) ($row['id_target'] ?? 0);
            $isNewCluster = (int) ($row['is_new_cluster'] ?? 0) === 1;
            $clusterName = trim((string) ($row['cluster_name'] ?? ''));
            $clusterCode = trim((string) ($row['cluster_code'] ?? ''));
            $statusValsal = $this->normalizeImportValsalStatus((string) ($row['status_valsal'] ?? 'ON REVIEW'));
            $valsalDate = $this->normalizeDate((string) ($row['valsal_date'] ?? '')) ?: date('Y-m-d');
            $homepassValsal = (int) $this->normalizeNumber($row['homepass_valsal'] ?? 0);
            $remarkValsal = trim((string) ($row['remark_valsal'] ?? ''));

            if ($clusterId <= 0 && $isNewCluster) {
                $clusterId = $this->MVALSAL_MyRep->createClusterForValsalImport(
                    $targetId,
                    $clusterName,
                    $clusterCode,
                    $homepassValsal,
                    $userId
                );
            }

            if ($clusterId <= 0) {
                continue;
            }

            $bakSynced = $this->MVALSAL_MyRep->upsertBakDoneForValsalImport(
                $clusterId,
                $homepassValsal,
                $valsalDate,
                $userId,
                $remarkValsal
            );
            if (!$bakSynced) {
                continue;
            }

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

            if ($result) {
                $inserted++;
            }
        }

        if ($inserted <= 0) {
            $this->jsonResponse(false, 'Gagal menyimpan hasil import VALSAL.');
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'message' => $inserted . ' data VALSAL berhasil diimport.',
                'errors' => $validated['errors'],
            ]));
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

    public function downloadDocument($fileId = 0)
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

        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    public function downloadDocumentBundle($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            show_404();
            return;
        }

        $cluster = $this->MVALSAL_MyRep->getValsalByClusterId($clusterId);
        $documentMap = $this->MVALSAL_MyRep->getValsalDocumentItemsByClusterIds([$clusterId]);
        $documentRows = array_values($documentMap[$clusterId] ?? []);

        if (empty($documentRows) || !class_exists('ZipArchive')) {
            $this->session->set_flashdata('error', 'Arsip dokumen gabungan belum bisa dibuat di server ini.');
            redirect('VALSAL_MyRep');
            return;
        }

        $zip = new ZipArchive();
        $tempZip = tempnam(sys_get_temp_dir(), 'valsal_bundle_');
        if ($tempZip === false) {
            $this->session->set_flashdata('error', 'Gagal menyiapkan arsip dokumen gabungan.');
            redirect('VALSAL_MyRep');
            return;
        }

        $zipFile = $tempZip . '.zip';
        @rename($tempZip, $zipFile);

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            if (is_file($zipFile)) {
                @unlink($zipFile);
            }
            $this->session->set_flashdata('error', 'Gagal membuat arsip dokumen gabungan.');
            redirect('VALSAL_MyRep');
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

            $docName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($documentRow['doc_name'] ?? 'Dokumen'));
            $originalName = basename((string) ($documentRow['file_name'] ?? basename($fullPath)));
            $zip->addFile($fullPath, $docName . '_' . $originalName);
            $addedCount++;
        }

        $zip->close();

        if ($addedCount === 0) {
            if (is_file($zipFile)) {
                @unlink($zipFile);
            }
            $this->session->set_flashdata('error', 'Belum ada file dokumen yang bisa digabung untuk cluster ini.');
            redirect('VALSAL_MyRep');
            return;
        }

        $safeClusterName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($cluster['cluster_name'] ?? ('CLUSTER_' . $clusterId)));
        $downloadName = 'VALSAL_' . $safeClusterName . '_gabungan_' . date('Ymd_His') . '.zip';

        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($zipFile));
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($zipFile);
        @unlink($zipFile);
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
        $isNoDocumentRequired = $fieldName === 'file'
            ? (int) $this->input->post('is_document_not_required') === 1
            : false;
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
                'message' => 'File ' . ((string) ($context['doc_name'] ?? 'dokumen')) . ' wajib dipilih.',
                'file_name' => '',
                'file_path' => '',
            ];
        }

        $uploadDir = './uploads/myrep_valsal/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
        $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($context['doc_name'] ?? 'VALSAL_DOC'));
        $fileName = 'VALSAL_' . (int) $clusterId . '_' . (int) ($context['id_doc_item'] ?? 0) . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

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
        if ($date === null || $date === '') {
            return null;
        }

        if (is_numeric($date) && class_exists('PHPExcel_Shared_Date')) {
            try {
                return PHPExcel_Shared_Date::ExcelToPHPObject($date)->format('Y-m-d');
            } catch (Exception $e) {
            }
        }

        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        $date = str_replace('/', '-', $date);
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
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

    private function parseValsalImportHeader($header)
    {
        $header = strtolower(trim((string) $header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        $header = trim($header, '_');

        $aliases = [
            'cluster_id' => ['cluster_id', 'id_myrep_cluster', 'id_cluster'],
            'id_target' => ['id_target', 'target_id'],
            'city_name' => ['city_name', 'city', 'kota', 'nama_kota'],
            'cluster_name' => ['cluster_name', 'nama_cluster', 'cluster'],
            'cluster_code' => ['cluster_code', 'kode_cluster'],
            'homepass_valsal' => ['homepass_valsal', 'hp_valsal', 'homepass', 'hp'],
            'valsal_date' => ['valsal_date', 'tanggal_valsal'],
            'status_valsal' => ['status_valsal', 'status'],
            'remark_valsal' => ['remark_valsal', 'remark', 'catatan'],
        ];

        foreach ($aliases as $field => $options) {
            if (in_array($header, $options, true)) {
                return $field;
            }
        }

        return null;
    }

    private function normalizeImportValsalStatus($status)
    {
        $status = strtoupper(trim((string) $status));
        $allowed = ['DRAFT', 'ON REVIEW', 'REJECTED', 'DONE', 'APPROVED'];
        if (in_array($status, $allowed, true)) {
            return $status;
        }

        return 'ON REVIEW';
    }

    private function validateValsalImportRows(array $rawRows)
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
            $homepassValsal = (int) $this->normalizeNumber($rawRow['homepass_valsal'] ?? 0);
            $valsalDate = $this->normalizeDate((string) ($rawRow['valsal_date'] ?? '')) ?: date('Y-m-d');
            $statusValsal = $this->normalizeImportValsalStatus((string) ($rawRow['status_valsal'] ?? 'ON REVIEW'));
            $remarkValsal = trim((string) ($rawRow['remark_valsal'] ?? ''));
            $rowErrors = [];

            $candidate = [];
            if ($clusterId > 0) {
                $candidate = $this->MVALSAL_MyRep->getClusterForValsalImportById($clusterId);
            }

            if (empty($candidate) && $clusterName !== '') {
                if ($targetId <= 0 && $cityName !== '') {
                    $target = $this->MVALSAL_MyRep->getTargetByCity($cityName);
                    $targetId = (int) ($target['id_target'] ?? 0);
                }
                $candidate = $this->MVALSAL_MyRep->getClusterForValsalImportByName($clusterName, $cityName, $targetId);
                $clusterId = (int) ($candidate['id_myrep_cluster'] ?? 0);
            }

            $isNewCluster = false;
            if (empty($candidate) || $clusterId <= 0) {
                if ($targetId <= 0 && $cityName !== '') {
                    $target = $this->MVALSAL_MyRep->getTargetByCity($cityName);
                    $targetId = (int) ($target['id_target'] ?? 0);
                }

                if ($clusterName === '') {
                    $rowErrors[] = 'Cluster name wajib diisi jika cluster belum ada di master';
                }
                if ($targetId <= 0) {
                    $rowErrors[] = 'id_target / city_name wajib valid untuk membuat cluster baru';
                }

                $isNewCluster = empty($rowErrors);
            } else {
                if (!empty($candidate['id_valsal'])) {
                    $rowErrors[] = 'Cluster sudah punya data VALSAL';
                }
            }

            if ($homepassValsal <= 0) {
                $rowErrors[] = 'Homepass VALSAL harus lebih besar dari 0';
            }

            $preparedRows[] = [
                'row_number' => $rowNumber,
                'cluster_id' => $clusterId,
                'id_target' => $targetId,
                'is_new_cluster' => $isNewCluster ? 1 : 0,
                'city_name' => $cityName !== '' ? $cityName : (string) ($candidate['city_name'] ?? ''),
                'cluster_name' => $clusterName !== '' ? $clusterName : (string) ($candidate['cluster_name'] ?? ''),
                'cluster_code' => $clusterCode !== '' ? $clusterCode : (string) ($candidate['cluster_code'] ?? ''),
                'homepass_valsal' => $homepassValsal,
                'valsal_date' => $valsalDate,
                'status_valsal' => $statusValsal,
                'remark_valsal' => $remarkValsal,
                'status' => empty($rowErrors) ? 'valid' : 'invalid',
                'message' => empty($rowErrors) ? 'Siap diimport' : implode(', ', array_unique($rowErrors)),
                'errors' => $rowErrors,
            ];
        }

        foreach ($preparedRows as $preparedRow) {
            if (!empty($preparedRow['errors'])) {
                $errors[] = [
                    'row' => $preparedRow['row_number'],
                    'message' => implode(', ', array_unique($preparedRow['errors'])),
                ];
            }
        }

        $validRows = [];
        foreach ($preparedRows as $preparedRow) {
            if (empty($preparedRow['errors'])) {
                $validRows[] = [
                    'cluster_id' => (int) $preparedRow['cluster_id'],
                    'id_target' => (int) $preparedRow['id_target'],
                    'is_new_cluster' => (int) $preparedRow['is_new_cluster'],
                    'city_name' => (string) $preparedRow['city_name'],
                    'cluster_name' => (string) $preparedRow['cluster_name'],
                    'cluster_code' => (string) $preparedRow['cluster_code'],
                    'homepass_valsal' => (int) $preparedRow['homepass_valsal'],
                    'valsal_date' => (string) $preparedRow['valsal_date'],
                    'status_valsal' => (string) $preparedRow['status_valsal'],
                    'remark_valsal' => (string) $preparedRow['remark_valsal'],
                ];
            }
        }

        return [
            'rows' => $preparedRows,
            'valid_rows' => $validRows,
            'errors' => $errors,
        ];
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
        if ($handle === false) {
            return $rows;
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return $rows;
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!empty($data)) {
                if (isset($data[0])) {
                    $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
                }
                $rows[] = $data;
            }
        }
        fclose($handle);

        $sheetData = [];
        foreach ($rows as $rowIndex => $row) {
            $sheetRow = [];
            foreach ($row as $colIndex => $value) {
                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($colIndex);
                $sheetRow[$columnLetter] = $value;
            }
            $sheetData[$rowIndex + 1] = $sheetRow;
        }

        return $sheetData;
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

    private function sendValsalNotification($eventName, array $cluster, $documentLabel)
    {
        $clusterId = (int) ($cluster['id_myrep_cluster'] ?? 0);
        if ($clusterId <= 0) {
            return;
        }

        $this->myrepNotifier->notify('VALSAL_MyRep', $eventName, [
            'module_label' => 'VALSAL',
            'document_label' => (string) $documentLabel,
            'regional_name' => (string) ($cluster['regional_name'] ?? ''),
            'city_name' => (string) ($cluster['city_name'] ?? ''),
            'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
            'homepass' => (int) ($cluster['homepass_valsal'] ?? ($cluster['homepass'] ?? 0)),
            'sender_name' => (string) $this->session->userdata('nama_user'),
            'detail_url' => base_url('VALSAL_MyRep'),
        ]);
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

    private function buildReportDocumentHeaders($documentDefinitions)
    {
        $headers = [];
        foreach ($documentDefinitions as $documentDefinition) {
            $docName = (string) ($documentDefinition['doc_name'] ?? 'Dokumen');
            $headers[] = $docName . ' Status';
            $headers[] = $docName . ' File';
            $headers[] = $docName . ' Remark';
        }

        return $headers;
    }

    private function resolveDocumentStatusLabel($docRow)
    {
        if ((int) ($docRow['is_document_not_required'] ?? 0) === 1) {
            return 'Tidak Dibutuhkan';
        }

        $status = strtoupper(trim((string) ($docRow['status_file'] ?? '')));
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        if ($status !== '') {
            return $status;
        }

        return !empty($docRow['file_name']) ? 'UPLOADED' : 'BELUM UPLOAD';
    }

    private function buildClusterDocumentResponse($clusterId)
    {
        $clusterId = (int) $clusterId;
        $cluster = $this->MVALSAL_MyRep->getValsalByClusterId($clusterId);
        $documentRows = $this->MVALSAL_MyRep->getValsalDocumentItemsByClusterIds([$clusterId]);

        return [
            'cluster_id' => $clusterId,
            'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
            'status_valsal' => (string) ($cluster['status_valsal'] ?? ''),
            'status_current' => (string) ($cluster['status_current'] ?? ''),
            'documents' => array_values($documentRows[$clusterId] ?? []),
        ];
    }
}
