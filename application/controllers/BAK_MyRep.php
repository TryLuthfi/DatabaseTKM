<?php
defined('BASEPATH') or exit('No direct script access allowed');

class BAK_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MBAK_MyRep');
        $this->load->model('MMyRep_Cleanup');
        $this->load->library('upload');
        $this->load->library('Myrep_notification_service', null, 'myrepNotifier');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('BAK_MyRep');
            $this->myrepAccess->enforceByMethod('BAK_MyRep', (string) $this->router->fetch_method(), [
                'previewClusterImport' => 'TAMBAH',
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

        $data['title'] = 'BAK MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['isReady'] = $this->MBAK_MyRep->bakTablesReady();
        $data['docReady'] = $this->MBAK_MyRep->bakDocumentTablesReady();
        if ($data['docReady']) {
            $this->MBAK_MyRep->ensureBakDocumentSetup();
        }
        $data['canApprove'] = $this->isApprover();
        $data['cityOptions'] = $this->MBAK_MyRep->getCityOptions();
        $data['regionalOptions'] = $this->MBAK_MyRep->getRegionalOptions();
        $data['cityOptionsByRegional'] = $this->MBAK_MyRep->getCityOptionsByRegional();
        $data['regionalOptionsByCity'] = $this->MBAK_MyRep->getRegionalOptionsByCity();
        $data['targetOptions'] = $this->MBAK_MyRep->getTargetOptions();
        $data['createTargetOptions'] = $this->MBAK_MyRep->getCreateTargetOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MBAK_MyRep->getBakRows($selectedCity, $selectedStatus)
            : [];
        $clusterIds = array_values(array_filter(array_map(static function ($row) {
            return (int) ($row['id_myrep_cluster'] ?? 0);
        }, $data['clusterRows'])));
        $data['bakDocumentDefinitions'] = $data['docReady'] ? $this->MBAK_MyRep->getBakDocumentDefinitions() : [];
        $data['bakDocumentMap'] = ($data['docReady'] && !empty($clusterIds))
            ? $this->MBAK_MyRep->getBakDocumentItemsByClusterIds($clusterIds)
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
        $districtId = trim((string) $this->input->post('district_id'));
        $villageId = trim((string) $this->input->post('village_id'));
        $today = date('Y-m-d');
        $baOpenDate = $this->normalizeDate($this->input->post('ba_open_date')) ?: $today;
        $bakDate = $this->normalizeDate($this->input->post('bak_date')) ?: $today;
        $ntpName = trim((string) $this->input->post('nomor_ntp'));
        $ntpDate = $this->normalizeDate($this->input->post('tanggal_ntp'));
        $ntpYear = $ntpDate ? (int) date('Y', strtotime($ntpDate)) : null;
        $homepassBak = (int) $this->normalizeNumber($this->input->post('homepass_bak'));
        $remarkBak = trim((string) $this->input->post('remark_bak'));
        $docReady = $this->MBAK_MyRep->bakDocumentTablesReady();
        if ($docReady) {
            $this->MBAK_MyRep->ensureBakDocumentSetup();
        }
        $documentDefinitions = $docReady ? $this->MBAK_MyRep->getBakDocumentDefinitions() : [];

        if ($targetId <= 0 || $clusterName === '' || $homepassBak <= 0) {
            $this->session->set_flashdata('error', 'Target, nama cluster, dan homepass BAK wajib diisi.');
            redirect('BAK_MyRep');
            return;
        }

        if ($docReady && empty($documentDefinitions)) {
            $this->session->set_flashdata('error', 'Konfigurasi dokumen BAK belum ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        if ($docReady) {
            foreach ($documentDefinitions as $documentDefinition) {
                $docItemId = (int) $documentDefinition['id_doc_item'];
                $fieldName = 'create_file_' . $docItemId;
                $isNoDocumentRequired = (int) $this->input->post('create_is_document_not_required_' . $docItemId) === 1;
                if (!$isNoDocumentRequired && empty($_FILES[$fieldName]['name'])) {
                    $this->session->set_flashdata('error', 'Dokumen ' . ($documentDefinition['doc_name'] ?? 'BAK') . ' wajib diupload atau tandai tidak dibutuhkan saat input cluster BAK baru.');
                    redirect('BAK_MyRep');
                    return;
                }
            }
        }

        if ($docReady && count($documentDefinitions) < 3) {
            $this->session->set_flashdata('error', 'Konfigurasi 3 dokumen BAK belum lengkap.');
            redirect('BAK_MyRep');
            return;
        }

        $target = $this->MBAK_MyRep->getTargetById($targetId);
        if (empty($target)) {
            $this->session->set_flashdata('error', 'Target kota MyRep tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        $district = $districtId !== '' ? $this->MBAK_MyRep->getDistrictById($districtId) : [];
        $village = $villageId !== '' ? $this->MBAK_MyRep->getVillageById($villageId) : [];

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
                'regency_id' => null,
                'district_id' => !empty($district['id']) ? (string) $district['id'] : null,
                'district_name' => !empty($district['name']) ? (string) $district['name'] : null,
                'village_id' => !empty($village['id']) ? (string) $village['id'] : null,
                'village_name' => !empty($village['name']) ? (string) $village['name'] : null,
                'team_name' => $target['team_name'] ?? null,
                'chief' => $target['chief'] ?? null,
                'rpm' => $target['rpm'] ?? null,
                'sm' => $target['sm'] ?? null,
                'spv' => $target['spv'] ?? null,
                'hp_plan' => $homepassBak,
                'ntp_name' => $ntpName !== '' ? $ntpName : null,
                'ntp_date' => $ntpDate ?: null,
                'ntp_year' => $ntpYear,
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
            foreach ($documentDefinitions as $documentDefinition) {
                $docItemId = (int) $documentDefinition['id_doc_item'];
                $context = $this->MBAK_MyRep->getBakDocumentContext($clusterId, $docItemId);
                $isNoDocumentRequired = (int) $this->input->post('create_is_document_not_required_' . $docItemId) === 1;
                if (empty($context['id_doc_item'])) {
                    $this->MMyRep_Cleanup->deleteWholeCluster($clusterId);
                    $this->session->set_flashdata('error', 'Konfigurasi dokumen ' . ($documentDefinition['doc_name'] ?? 'BAK') . ' belum ditemukan.');
                    redirect('BAK_MyRep');
                    return;
                }

                $uploadResult = $isNoDocumentRequired
                    ? [
                        'status' => true,
                        'message' => '',
                        'file_name' => '',
                        'file_path' => '',
                    ]
                    : $this->storeBakUploadFile($clusterId, $context, 'create_file_' . $docItemId);
                if (!$uploadResult['status']) {
                    $this->MMyRep_Cleanup->deleteWholeCluster($clusterId);
                    $this->session->set_flashdata('error', $uploadResult['message']);
                    redirect('BAK_MyRep');
                    return;
                }

                $fileId = $this->MBAK_MyRep->saveBakFileUpload($clusterId, $docItemId, [
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
                    $this->session->set_flashdata('error', 'Dokumen ' . ($documentDefinition['doc_name'] ?? 'BAK') . ' gagal disimpan.');
                    redirect('BAK_MyRep');
                    return;
                }
            }

            $this->MBAK_MyRep->syncBakStatusByCluster($clusterId, $userId);
            $this->sendBakNotification('cluster_masuk', [
                'cluster_name' => $clusterName,
                'regional_name' => (string) ($target['regional_name'] ?? ''),
                'city_name' => (string) ($target['city_name'] ?? ''),
                'id_myrep_cluster' => $clusterId,
                'homepass_bak' => $homepassBak,
            ], 'BAK');
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
        $districtId = trim((string) $this->input->post('district_id'));
        $villageId = trim((string) $this->input->post('village_id'));
        $existing = $this->MBAK_MyRep->getClusterById($clusterId);
        if (empty($existing)) {
            $this->session->set_flashdata('error', 'Data cluster BAK tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        $baOpenDate = $this->normalizeDate($this->input->post('ba_open_date')) ?: date('Y-m-d');
        $bakDate = $this->normalizeDate($this->input->post('bak_date')) ?: date('Y-m-d');
        $ntpName = trim((string) $this->input->post('nomor_ntp'));
        $ntpDate = $this->normalizeDate($this->input->post('tanggal_ntp'));
        $ntpYear = $ntpDate ? (int) date('Y', strtotime($ntpDate)) : null;
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

        $district = $districtId !== '' ? $this->MBAK_MyRep->getDistrictById($districtId) : [];
        $village = $villageId !== '' ? $this->MBAK_MyRep->getVillageById($villageId) : [];

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
                'regency_id' => null,
                'district_id' => !empty($district['id']) ? (string) $district['id'] : null,
                'district_name' => !empty($district['name']) ? (string) $district['name'] : null,
                'village_id' => !empty($village['id']) ? (string) $village['id'] : null,
                'village_name' => !empty($village['name']) ? (string) $village['name'] : null,
                'team_name' => $target['team_name'] ?? null,
                'chief' => $target['chief'] ?? null,
                'rpm' => $target['rpm'] ?? null,
                'sm' => $target['sm'] ?? null,
                'spv' => $target['spv'] ?? null,
                'hp_plan' => $homepassBak,
                'ntp_name' => $ntpName !== '' ? $ntpName : null,
                'ntp_date' => $ntpDate ?: null,
                'ntp_year' => $ntpYear,
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

        if ($this->MBAK_MyRep->bakDocumentTablesReady()) {
            $this->MBAK_MyRep->syncBakStatusByCluster($clusterId, $userId);
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

        $this->MBAK_MyRep->ensureBakDocumentSetup();

        $clusterId = (int) $this->input->post('cluster_id');
        $docItemId = (int) $this->input->post('doc_item_id');
        $context = $this->MBAK_MyRep->getBakDocumentContext($clusterId, $docItemId);
        if ($clusterId <= 0 || empty($context['id_doc_item'])) {
            $this->handleUploadError('Konfigurasi dokumen BAK belum ditemukan.', 'BAK_MyRep');
            return;
        }
        $notificationEvent = !empty($context['id_doc_file']) ? 'document_revised' : 'document_masuk';

        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;
        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->handleUploadError('File ' . ($context['doc_name'] ?? 'dokumen') . ' wajib dipilih.', 'BAK_MyRep');
            return;
        }

        $uploadResult = $this->storeBakUploadFile($clusterId, $context, 'file');
        if (!$uploadResult['status']) {
            $this->handleUploadError($uploadResult['message'], 'BAK_MyRep');
            return;
        }

        $fileId = $this->MBAK_MyRep->saveBakFileUpload($clusterId, $docItemId, [
            'file_name' => $uploadResult['file_name'],
            'file_path' => $uploadResult['file_path'],
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($fileId <= 0) {
            $this->handleUploadError('Dokumen ' . ($context['doc_name'] ?? 'BAK') . ' gagal disimpan.', 'BAK_MyRep');
            return;
        }

        $this->MBAK_MyRep->syncBakStatusByCluster($clusterId, (int) $this->session->userdata('id_user'));
        $cluster = $this->MBAK_MyRep->getClusterById($clusterId);
        $this->sendBakNotification($notificationEvent, $cluster, (string) ($context['doc_name'] ?? 'BAK'));

        $this->handleUploadSuccess(
            $isNoDocumentRequired ? 'Dokumen ' . ($context['doc_name'] ?? 'BAK') . ' ditandai tidak dibutuhkan dan dikirim ke review.' : 'Dokumen ' . ($context['doc_name'] ?? 'BAK') . ' berhasil diupload.',
            'BAK_MyRep'
        );
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
                $this->jsonResponse(false, 'Anda tidak memiliki akses approve dokumen BAK.');
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen BAK.');
            redirect('BAK_MyRep');
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        if ($fileId <= 0) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Dokumen BAK tidak ditemukan.');
                return;
            }
            $this->session->set_flashdata('error', 'Dokumen BAK tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        $file = $this->MBAK_MyRep->getBakFileById($fileId);
        if (empty($file['id_myrep_cluster'])) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Data cluster dokumen BAK tidak ditemukan.');
                return;
            }
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
            $this->MBAK_MyRep->syncBakStatusByCluster((int) $file['id_myrep_cluster'], (int) $this->session->userdata('id_user'));
        }

        $docName = (string) ($file['doc_name'] ?? 'BAK');
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
        redirect('BAK_MyRep');
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
                $this->jsonResponse(false, 'Anda tidak memiliki akses reject dokumen BAK.');
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen BAK.');
            redirect('BAK_MyRep');
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        if ($fileId <= 0) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Dokumen BAK tidak ditemukan.');
                return;
            }
            $this->session->set_flashdata('error', 'Dokumen BAK tidak ditemukan.');
            redirect('BAK_MyRep');
            return;
        }

        $file = $this->MBAK_MyRep->getBakFileById($fileId);
        if (empty($file['id_myrep_cluster'])) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Data cluster dokumen BAK tidak ditemukan.');
                return;
            }
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
            $this->MBAK_MyRep->syncBakStatusByCluster((int) $file['id_myrep_cluster'], (int) $this->session->userdata('id_user'));
        }

        $docName = (string) ($file['doc_name'] ?? 'BAK');
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
        redirect('BAK_MyRep');
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
                $this->jsonResponse(false, 'Anda tidak memiliki akses approve semua dokumen BAK.');
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve semua dokumen BAK.');
            redirect('BAK_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if ($clusterId <= 0) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Cluster dokumen BAK tidak valid.');
                return;
            }
            $this->session->set_flashdata('error', 'Cluster dokumen BAK tidak valid.');
            redirect('BAK_MyRep');
            return;
        }

        $documentMap = $this->MBAK_MyRep->getBakDocumentItemsByClusterIds([$clusterId]);
        $documentRows = array_values($documentMap[$clusterId] ?? []);
        if (empty($documentRows)) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Dokumen BAK untuk cluster ini belum tersedia.');
                return;
            }
            $this->session->set_flashdata('error', 'Dokumen BAK untuk cluster ini belum tersedia.');
            redirect('BAK_MyRep');
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

            $result = $this->MBAK_MyRep->updateBakFileStatus($fileId, [
                'status_file' => 'APPROVED',
                'remark' => trim((string) ($documentRow['remark'] ?? '')),
                'approved_by' => $approvedBy,
            ]);

            if ($result) {
                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            $this->MBAK_MyRep->syncBakStatusByCluster($clusterId, $approvedBy);
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
        redirect('BAK_MyRep');
    }

    public function downloadReport()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MBAK_MyRep->bakTablesReady()) {
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
        $bakDateStart = $this->normalizeDate($this->input->get('bak_date_start')) ?: '';
        $bakDateEnd = $this->normalizeDate($this->input->get('bak_date_end')) ?: '';

        $rows = $this->MBAK_MyRep->getBakRows($selectedCity, $selectedStatus, $selectedRegional, $cityList, $regionalList, $bakDateStart, $bakDateEnd);

        $documentDefinitions = [];
        $documentMap = [];
        if ($this->MBAK_MyRep->bakDocumentTablesReady()) {
            $this->MBAK_MyRep->ensureBakDocumentSetup();
            $documentDefinitions = $this->MBAK_MyRep->getBakDocumentDefinitions();
            $clusterIds = array_values(array_filter(array_map(static function ($row) {
                return (int) ($row['id_myrep_cluster'] ?? 0);
            }, $rows)));
            if (!empty($clusterIds)) {
                $documentMap = $this->MBAK_MyRep->getBakDocumentItemsByClusterIds($clusterIds);
            }
        }

        $filename = 'report_bak_myrep_' . date('Ymd_His') . '.csv';
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
            'HP Estimasi',
            'Tanggal BA OPEN',
            'Tanggal BAK',
            'Status BAK',
            'Status Flow',
            'Remark BAK',
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
                (string) ($row['ba_open_date'] ?? ''),
                (string) ($row['bak_date'] ?? ''),
                (string) ($row['status_bak'] ?? ''),
                (string) ($row['status_current'] ?? ''),
                (string) ($row['remark_bak'] ?? ''),
            ];

            foreach ($documentDefinitions as $documentDefinition) {
                $docRow = $docIndexed[(int) $documentDefinition['id_doc_item']] ?? [];
                $csvRow[] = (string) ($this->resolveDocumentStatusLabel($docRow));
                $csvRow[] = (string) ($docRow['file_name'] ?? '');
                $csvRow[] = (string) ($docRow['remark'] ?? '');
            }

            fputcsv($output, $csvRow);
        }

        fclose($output);
        exit;
    }

    public function downloadClusterImportTemplate()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $filename = 'format_import_cluster_bak_myrep_' . date('Ymd_His') . '.csv';
        $headers = [
            'id_target',
            'city_name',
            'cluster_name',
            'cluster_code',
            'district_id',
            'district_name',
            'village_id',
            'village_name',
            'homepass_bak',
            'ba_open_date',
            'bak_date',
            'remark_bak',
        ];

        $exampleRows = [
            ['', 'MALANG', 'Cluster A', 'CL-A', '', 'KLOJEN', '', 'KAUMAN', '100', date('Y-m-d'), date('Y-m-d'), 'Contoh cluster BAK 1'],
            ['', 'MALANG', 'Cluster B', 'CL-B', '', 'BLIMBING', '', 'POLOWIJEN', '120', date('Y-m-d'), date('Y-m-d'), 'Contoh cluster BAK 2'],
            ['', 'MALANG', 'Cluster C', 'CL-C', '', 'LOWOKWARU', '', 'MOJOLANGU', '90', date('Y-m-d'), date('Y-m-d'), 'Contoh cluster BAK 3'],
            ['', 'MALANG', 'Cluster D', 'CL-D', '', 'SUKUN', '', 'BANDULAN', '110', date('Y-m-d'), date('Y-m-d'), 'Contoh cluster BAK 4'],
            ['', 'MALANG', 'Cluster E', 'CL-E', '', 'KEDUNGKANDANG', '', 'SAWOJAJAR', '130', date('Y-m-d'), date('Y-m-d'), 'Contoh cluster BAK 5'],
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

    public function previewClusterImport()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session login tidak ditemukan.');
            return;
        }

        if (!$this->MBAK_MyRep->bakTablesReady()) {
            $this->jsonResponse(false, 'Tabel BAK MyRep belum tersedia.');
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
            $this->jsonResponse(false, 'File import cluster BAK tidak bisa dibaca.');
            return;
        }

        @unlink($filePath);

        if (count($sheetData) < 2) {
            $this->jsonResponse(false, 'File import cluster BAK tidak memiliki data.');
            return;
        }

        $headerRow = reset($sheetData);
        $headerMap = [];
        foreach ($headerRow as $column => $header) {
            $mappedField = $this->parseBakImportHeader($header);
            if ($mappedField) {
                $headerMap[$column] = $mappedField;
            }
        }

        foreach (['cluster_name', 'homepass_bak'] as $requiredField) {
            if (!in_array($requiredField, $headerMap, true)) {
                $this->jsonResponse(false, 'Header file wajib memuat ' . $requiredField . '.');
                return;
            }
        }

        if (!in_array('id_target', $headerMap, true) && !in_array('city_name', $headerMap, true)) {
            $this->jsonResponse(false, 'Header file wajib memuat id_target atau city_name.');
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

        $validated = $this->validateBakImportRows($rows);
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

    public function saveImportedClusters()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session login tidak ditemukan.');
            return;
        }

        if (!$this->MBAK_MyRep->bakTablesReady()) {
            $this->jsonResponse(false, 'Tabel BAK MyRep belum tersedia.');
            return;
        }

        $rowsJson = $this->input->post('rows_json');
        $rows = json_decode((string) $rowsJson, true);
        if (empty($rows) || !is_array($rows)) {
            $this->jsonResponse(false, 'Tidak ada data import yang siap disimpan.');
            return;
        }

        $validated = $this->validateBakImportRows($rows);
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
            $target = $this->MBAK_MyRep->getTargetById((int) ($row['id_target'] ?? 0));
            if (empty($target)) {
                continue;
            }

            $districtId = trim((string) ($row['district_id'] ?? ''));
            $districtName = trim((string) ($row['district_name'] ?? ''));
            $villageId = trim((string) ($row['village_id'] ?? ''));
            $villageName = trim((string) ($row['village_name'] ?? ''));
            $district = $districtId !== '' ? $this->MBAK_MyRep->getDistrictById($districtId) : [];
            if (empty($district) && $districtName !== '') {
                $district = $this->MBAK_MyRep->getDistrictByNameAndTarget($districtName, (int) ($row['id_target'] ?? 0), (string) ($target['city_name'] ?? ''));
            }
            $village = $villageId !== '' ? $this->MBAK_MyRep->getVillageById($villageId) : [];
            if (empty($village) && !empty($district['id']) && $villageName !== '') {
                $village = $this->MBAK_MyRep->getVillageByNameAndDistrict($villageName, (string) $district['id']);
            }
            $statusBak = $this->normalizeImportBakStatus((string) ($row['status_bak'] ?? 'ON REVIEW'));
            $baOpenDate = $this->normalizeDate((string) ($row['ba_open_date'] ?? '')) ?: date('Y-m-d');
            $bakDate = $this->normalizeDate((string) ($row['bak_date'] ?? '')) ?: date('Y-m-d');
            $homepassBak = (int) $this->normalizeNumber($row['homepass_bak'] ?? 0);

            $clusterId = $this->MBAK_MyRep->createClusterAndBak(
                [
                    'id_target' => (int) $row['id_target'],
                    'cluster_name' => (string) ($row['cluster_name'] ?? ''),
                    'cluster_code' => (($row['cluster_code'] ?? '') !== '') ? (string) $row['cluster_code'] : null,
                    'regional_name' => $target['regional_name'] ?? null,
                    'province_name' => $target['province_name'] ?? null,
                    'city_name' => $target['city_name'] ?? null,
                    'regency_id' => null,
                    'district_id' => !empty($district['id']) ? (string) $district['id'] : null,
                    'district_name' => !empty($district['name']) ? (string) $district['name'] : null,
                    'village_id' => !empty($village['id']) ? (string) $village['id'] : null,
                    'village_name' => !empty($village['name']) ? (string) $village['name'] : null,
                    'team_name' => $target['team_name'] ?? null,
                    'chief' => $target['chief'] ?? null,
                    'rpm' => $target['rpm'] ?? null,
                    'sm' => $target['sm'] ?? null,
                    'spv' => $target['spv'] ?? null,
                    'hp_plan' => $homepassBak,
                    'status_current' => $this->buildCurrentStatus($baOpenDate, $bakDate, $statusBak),
                    'remark_general' => (($row['remark_bak'] ?? '') !== '') ? (string) $row['remark_bak'] : null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ],
                [
                    'ba_open_date' => $baOpenDate,
                    'bak_date' => $bakDate,
                    'homepass_bak' => $homepassBak,
                    'status_bak' => $statusBak,
                    'remark_bak' => (($row['remark_bak'] ?? '') !== '') ? (string) $row['remark_bak'] : null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]
            );

            if ($clusterId > 0) {
                $inserted++;
            }
        }

        if ($inserted <= 0) {
            $this->jsonResponse(false, 'Gagal menyimpan hasil import cluster BAK.');
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'message' => $inserted . ' cluster BAK berhasil diimport.',
                'errors' => $validated['errors'],
            ]));
    }

    public function getDistrictOptions()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['results' => []]));
            return;
        }

        $targetId = (int) $this->input->get('target_id');
        $keyword = trim((string) $this->input->get('q'));
        $cityName = trim((string) $this->input->get('city_name'));
        $rows = $this->MBAK_MyRep->searchDistrictOptionsByTarget($targetId, $keyword, 50, $cityName);

        $results = array_map(static function ($row) {
            return [
                'id' => (string) ($row['id'] ?? ''),
                'text' => (string) ($row['name'] ?? ''),
            ];
        }, $rows);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['results' => $results]));
    }

    public function getVillageOptions()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['results' => []]));
            return;
        }

        $districtId = trim((string) $this->input->get('district_id'));
        $keyword = trim((string) $this->input->get('q'));
        $rows = $this->MBAK_MyRep->searchVillageOptionsByDistrict($districtId, $keyword);

        $results = array_map(static function ($row) {
            return [
                'id' => (string) ($row['id'] ?? ''),
                'text' => (string) ($row['name'] ?? ''),
            ];
        }, $rows);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['results' => $results]));
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

    public function downloadDocument($fileId = 0)
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

        $cluster = $this->MBAK_MyRep->getClusterById($clusterId);
        $documentMap = $this->MBAK_MyRep->getBakDocumentItemsByClusterIds([$clusterId]);
        $documentRows = array_values($documentMap[$clusterId] ?? []);

        if (empty($documentRows) || !class_exists('ZipArchive')) {
            $this->session->set_flashdata('error', 'Arsip dokumen gabungan belum bisa dibuat di server ini.');
            redirect('BAK_MyRep');
            return;
        }

        $zip = new ZipArchive();
        $tempZip = tempnam(sys_get_temp_dir(), 'bak_bundle_');
        if ($tempZip === false) {
            $this->session->set_flashdata('error', 'Gagal menyiapkan arsip dokumen gabungan.');
            redirect('BAK_MyRep');
            return;
        }

        $zipFile = $tempZip . '.zip';
        @rename($tempZip, $zipFile);

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            if (is_file($zipFile)) {
                @unlink($zipFile);
            }
            $this->session->set_flashdata('error', 'Gagal membuat arsip dokumen gabungan.');
            redirect('BAK_MyRep');
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
            redirect('BAK_MyRep');
            return;
        }

        $safeClusterName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($cluster['cluster_name'] ?? ('CLUSTER_' . $clusterId)));
        $downloadName = 'BAK_' . $safeClusterName . '_gabungan_' . date('Ymd_His') . '.zip';

        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($zipFile));
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($zipFile);
        @unlink($zipFile);
        exit;
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
            redirect('BAK_MyRep');
            return;
        }

        $deleted = $this->MMyRep_Cleanup->deleteWholeCluster($clusterId);
        $this->session->set_flashdata($deleted ? 'success' : 'error', $deleted ? 'Cluster MyRep beserta seluruh flow sebelumnya berhasil dihapus bersih.' : 'Gagal menghapus cluster MyRep.');
        redirect('BAK_MyRep');
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

        $uploadDir = './uploads/myrep_bak/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
        $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($context['doc_name'] ?? 'BAK_DOC'));
        $fileName = 'BAK_' . (int) $clusterId . '_' . (int) ($context['id_doc_item'] ?? 0) . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

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

    private function sendBakNotification($eventName, array $cluster, $documentLabel)
    {
        $clusterId = (int) ($cluster['id_myrep_cluster'] ?? 0);
        if ($clusterId <= 0) {
            return;
        }

        $this->myrepNotifier->notify('BAK_MyRep', $eventName, [
            'module_label' => 'BAK',
            'document_label' => (string) $documentLabel,
            'regional_name' => (string) ($cluster['regional_name'] ?? ''),
            'city_name' => (string) ($cluster['city_name'] ?? ''),
            'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
            'homepass' => (int) ($cluster['homepass_bak'] ?? ($cluster['homepass'] ?? 0)),
            'sender_name' => (string) $this->session->userdata('nama_user'),
            'detail_url' => base_url('BAK_MyRep'),
        ]);
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

    private function parseBakImportHeader($header)
    {
        $header = strtolower(trim((string) $header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        $header = trim($header, '_');

        $aliases = [
            'id_target' => ['id_target', 'target_id'],
            'city_name' => ['city_name', 'city', 'kota', 'nama_kota'],
            'cluster_name' => ['cluster_name', 'nama_cluster', 'cluster'],
            'cluster_code' => ['cluster_code', 'kode_cluster'],
            'district_id' => ['district_id', 'kecamatan_id'],
            'district_name' => ['district_name', 'kecamatan', 'nama_kecamatan'],
            'village_id' => ['village_id', 'kelurahan_id', 'desa_id'],
            'village_name' => ['village_name', 'kelurahan', 'desa', 'nama_kelurahan', 'nama_desa'],
            'homepass_bak' => ['homepass_bak', 'homepass', 'hp', 'hp_bak'],
            'ba_open_date' => ['ba_open_date', 'tanggal_ba_open', 'ba_open'],
            'bak_date' => ['bak_date', 'tanggal_bak'],
            'status_bak' => ['status_bak', 'status'],
            'remark_bak' => ['remark_bak', 'remark', 'catatan'],
        ];

        foreach ($aliases as $field => $options) {
            if (in_array($header, $options, true)) {
                return $field;
            }
        }

        return null;
    }

    private function normalizeImportBakStatus($status)
    {
        $status = strtoupper(trim((string) $status));
        $allowed = ['DRAFT', 'ON REVIEW', 'REJECTED', 'DONE', 'APPROVED'];
        if (in_array($status, $allowed, true)) {
            return $status;
        }

        return 'ON REVIEW';
    }

    private function validateBakImportRows(array $rawRows)
    {
        $preparedRows = [];
        $errors = [];

        foreach ($rawRows as $index => $rawRow) {
            $rowNumber = $index + 1;
            $targetId = (int) ($rawRow['id_target'] ?? 0);
            $cityName = strtoupper(trim((string) ($rawRow['city_name'] ?? '')));
            $clusterName = trim((string) ($rawRow['cluster_name'] ?? ''));
            $clusterCode = trim((string) ($rawRow['cluster_code'] ?? ''));
            $districtId = trim((string) ($rawRow['district_id'] ?? ''));
            $districtName = trim((string) ($rawRow['district_name'] ?? ''));
            $villageId = trim((string) ($rawRow['village_id'] ?? ''));
            $villageName = trim((string) ($rawRow['village_name'] ?? ''));
            $homepassBak = (int) $this->normalizeNumber($rawRow['homepass_bak'] ?? 0);
            $baOpenDate = $this->normalizeDate((string) ($rawRow['ba_open_date'] ?? '')) ?: date('Y-m-d');
            $bakDate = $this->normalizeDate((string) ($rawRow['bak_date'] ?? '')) ?: date('Y-m-d');
            $statusBak = $this->normalizeImportBakStatus((string) ($rawRow['status_bak'] ?? 'ON REVIEW'));
            $remarkBak = trim((string) ($rawRow['remark_bak'] ?? ''));
            $rowErrors = [];

            if ($targetId <= 0 && $cityName !== '') {
                $target = $this->MBAK_MyRep->getTargetByCity($cityName);
                $targetId = (int) ($target['id_target'] ?? 0);
            }

            if ($targetId <= 0) {
                $rowErrors[] = 'Target kota tidak ditemukan (isi id_target atau city_name yang valid)';
            }

            if ($clusterName === '') {
                $rowErrors[] = 'Nama cluster wajib diisi';
            }

            if ($homepassBak <= 0) {
                $rowErrors[] = 'Homepass BAK harus lebih besar dari 0';
            }

            $resolvedTargetCityName = '';
            if ($targetId > 0) {
                $targetById = $this->MBAK_MyRep->getTargetById($targetId);
                $resolvedTargetCityName = strtoupper(trim((string) ($targetById['city_name'] ?? '')));
            }

            $districtRow = [];
            if ($districtId !== '') {
                $districtRow = $this->MBAK_MyRep->getDistrictById($districtId);
                if (empty($districtRow)) {
                    $rowErrors[] = 'district_id tidak ditemukan';
                }
            } elseif ($districtName !== '') {
                $districtRow = $this->MBAK_MyRep->getDistrictByNameAndTarget($districtName, $targetId, $resolvedTargetCityName !== '' ? $resolvedTargetCityName : $cityName);
                if (empty($districtRow)) {
                    $rowErrors[] = 'Nama kecamatan tidak ditemukan';
                } else {
                    $districtId = (string) ($districtRow['id'] ?? '');
                    $districtName = (string) ($districtRow['name'] ?? $districtName);
                }
            }

            if ($villageId !== '') {
                $villageRow = $this->MBAK_MyRep->getVillageById($villageId);
                if (empty($villageRow)) {
                    $rowErrors[] = 'village_id tidak ditemukan';
                } elseif (!empty($districtId) && (string) ($villageRow['district_id'] ?? '') !== (string) $districtId) {
                    $rowErrors[] = 'village_id tidak sesuai dengan district_id';
                } else {
                    if ($villageName === '') {
                        $villageName = (string) ($villageRow['name'] ?? '');
                    }
                }
            } elseif ($villageName !== '') {
                if ($districtId === '') {
                    $rowErrors[] = 'Isi kecamatan dulu untuk validasi kelurahan';
                } else {
                    $villageRow = $this->MBAK_MyRep->getVillageByNameAndDistrict($villageName, $districtId);
                    if (empty($villageRow)) {
                        $rowErrors[] = 'Nama kelurahan tidak ditemukan';
                    } else {
                        $villageId = (string) ($villageRow['id'] ?? '');
                        $villageName = (string) ($villageRow['name'] ?? $villageName);
                    }
                }
            }

            if ($targetId > 0 && $clusterName !== '' && $this->MBAK_MyRep->clusterExists($clusterName, $targetId)) {
                $rowErrors[] = 'Cluster dengan target yang sama sudah ada';
            }

            $preparedRows[] = [
                'row_number' => $rowNumber,
                'id_target' => $targetId,
                'city_name' => $cityName,
                'cluster_name' => $clusterName,
                'cluster_code' => $clusterCode,
                'district_id' => $districtId,
                'district_name' => $districtName,
                'village_id' => $villageId,
                'village_name' => $villageName,
                'homepass_bak' => $homepassBak,
                'ba_open_date' => $baOpenDate,
                'bak_date' => $bakDate,
                'status_bak' => $statusBak,
                'remark_bak' => $remarkBak,
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
                    'id_target' => (int) $preparedRow['id_target'],
                    'cluster_name' => (string) $preparedRow['cluster_name'],
                    'cluster_code' => (string) $preparedRow['cluster_code'],
                    'district_id' => (string) $preparedRow['district_id'],
                    'district_name' => (string) $preparedRow['district_name'],
                    'village_id' => (string) $preparedRow['village_id'],
                    'village_name' => (string) $preparedRow['village_name'],
                    'homepass_bak' => (int) $preparedRow['homepass_bak'],
                    'ba_open_date' => (string) $preparedRow['ba_open_date'],
                    'bak_date' => (string) $preparedRow['bak_date'],
                    'status_bak' => (string) $preparedRow['status_bak'],
                    'remark_bak' => (string) $preparedRow['remark_bak'],
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
        if (empty($this->session->userdata('id_user'))) {
            return false;
        }

        if (isset($this->myrepAccess) && is_object($this->myrepAccess) && method_exists($this->myrepAccess, 'hasPermission')) {
            return (bool) $this->myrepAccess->hasPermission('BAK_MyRep', 'APPROVAL');
        }

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
        $cluster = $this->MBAK_MyRep->getClusterById($clusterId);
        $documentRows = $this->MBAK_MyRep->getBakDocumentItemsByClusterIds([$clusterId]);

        return [
            'cluster_id' => $clusterId,
            'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
            'status_bak' => (string) ($cluster['status_bak'] ?? ''),
            'status_current' => (string) ($cluster['status_current'] ?? ''),
            'documents' => array_values($documentRows[$clusterId] ?? []),
        ];
    }
}
