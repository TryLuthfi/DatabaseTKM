<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Batch_Approval_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MBatch_Approval_MyRep');
        $this->load->model('MPost_Donasi_MyRep');
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

        $data['title'] = 'Batch Approval MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['isReady'] = $this->MBatch_Approval_MyRep->batchTablesReady();
        $data['docReady'] = $this->MBatch_Approval_MyRep->batchDocumentTablesReady();
        $data['canApprove'] = $this->isApprover();
        $data['cityOptions'] = $this->MBatch_Approval_MyRep->getCityOptions();
        $data['eligibleClusterOptions'] = $this->MBatch_Approval_MyRep->getEligibleClusterOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MBatch_Approval_MyRep->getBatchRows($selectedCity, $selectedStatus)
            : [];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Batch_Approval_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function detail($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel Batch Approval MyRep belum tersedia.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            redirect('Batch_Approval_MyRep');
            return;
        }

        $cluster = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        if (empty($cluster) || empty($cluster['id_batch_approval'])) {
            $this->session->set_flashdata('error', 'Detail Batch Approval tidak ditemukan.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        $batchFile = $this->MBatch_Approval_MyRep->batchDocumentTablesReady()
            ? $this->MBatch_Approval_MyRep->getBatchFileByClusterId($clusterId)
            : [];

        $data['title'] = 'Detail Batch Approval MyRep';
        $data['cluster'] = $cluster;
        $data['batchPics'] = $this->MBatch_Approval_MyRep->getBatchPics((int) $cluster['id_batch_approval']);
        $data['docReady'] = $this->MBatch_Approval_MyRep->batchDocumentTablesReady();
        $data['canApprove'] = $this->isApprover();
        $data['batchDocument'] = $batchFile;
        $data['batchDocumentLogs'] = !empty($batchFile['id_doc_file'])
            ? $this->MBatch_Approval_MyRep->getBatchFileLogs((int) $batchFile['id_doc_file'])
            : [];
        $data['postDonasiDocReady'] = $this->MPost_Donasi_MyRep->documentTablesReady();
        $data['postDonasiRows'] = $data['postDonasiDocReady']
            ? $this->MPost_Donasi_MyRep->getDocumentRows($clusterId)
            : [];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Batch_Approval_MyRep/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveBatchApproval()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel Batch Approval MyRep belum tersedia. Jalankan query database flow baru terlebih dahulu.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $hpDonasi = (int) $this->normalizeNumber($this->input->post('hp_donasi'));
        $nominalPengajuanArea = $this->normalizeNumber($this->input->post('nominal_pengajuan_area'));
        $nominalNegoEmr = $this->normalizeNullableNumber($this->input->post('nominal_nego_emr'));
        $nominalReleaseFinance = $this->normalizeNullableNumber($this->input->post('nominal_release_finance'));
        $freeWifiQty = $this->normalizeNullableInt($this->input->post('free_wifi_qty'));
        $freeWifiPeriodMonth = $this->normalizeNullableInt($this->input->post('free_wifi_period_month'));
        $recipientName = trim((string) $this->input->post('recipient_name'));
        $recipientPhone = trim((string) $this->input->post('recipient_phone'));
        $recipientPosition = trim((string) $this->input->post('recipient_position'));
        $recipientPeriod = trim((string) $this->input->post('recipient_period'));
        $bankName = trim((string) $this->input->post('bank_name'));
        $bankAccountNumber = trim((string) $this->input->post('bank_account_number'));
        $submissionDate = $this->normalizeDate($this->input->post('submission_date'));
        $stagingStatus = 'WAITING HO';
        $astriBatchNumber = trim((string) $this->input->post('astri_batch_number'));
        $remark = trim((string) $this->input->post('remark_batch_approval'));
        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;
        $pics = $this->collectPicsFromPost();

        if ($clusterId <= 0 || $hpDonasi <= 0 || $nominalPengajuanArea <= 0 || $recipientName === '' || $bankName === '' || $bankAccountNumber === '') {
            $this->session->set_flashdata('error', 'Cluster, HP donasi, nominal area, data penerima, dan data bank wajib diisi.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        if (!$isNoDocumentRequired && empty($_FILES['batch_rar_file']['name'])) {
            $this->session->set_flashdata('error', 'Upload RAR wajib diisi. Centang `Tidak membutuhkan dokument` jika dokumen memang tidak diperlukan.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        $cluster = $this->MBatch_Approval_MyRep->getBatchCandidateById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Cluster belum memenuhi syarat untuk proses Batch Approval.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        if (!empty($cluster['id_batch_approval'])) {
            $this->session->set_flashdata('error', 'Cluster ini sudah pernah diproses di modul Batch Approval.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        if (empty($pics)) {
            $pics[] = [
                'pic_name' => $recipientName,
                'pic_phone' => $recipientPhone,
                'pic_position' => $recipientPosition,
                'pic_period' => $recipientPeriod,
                'is_primary' => 1,
            ];
        }

        $userId = (int) $this->session->userdata('id_user');
        $nominalPerHomepass = $hpDonasi > 0 ? round($nominalPengajuanArea / $hpDonasi, 2) : 0;
        $batchId = $this->MBatch_Approval_MyRep->createBatchApproval($clusterId, [
            'submission_date' => $submissionDate,
            'hp_donasi' => $hpDonasi,
            'nominal_pengajuan_area' => $nominalPengajuanArea,
            'nominal_nego_emr' => $nominalNegoEmr,
            'nominal_release_finance' => $nominalReleaseFinance,
            'nominal_per_homepass' => $nominalPerHomepass,
            'bank_name' => $bankName,
            'bank_account_number' => $bankAccountNumber,
            'recipient_name' => $recipientName,
            'recipient_phone' => $recipientPhone,
            'recipient_position' => $recipientPosition,
            'recipient_period' => $recipientPeriod,
            'free_wifi_qty' => $freeWifiQty,
            'free_wifi_period_month' => $freeWifiPeriodMonth,
            'astri_batch_number' => $astriBatchNumber !== '' ? $astriBatchNumber : null,
            'staging_status' => $stagingStatus,
            'submitted_to_ho_at' => $stagingStatus === 'WAITING HO' ? date('Y-m-d H:i:s') : null,
            'submitted_to_astri_at' => $stagingStatus === 'WAITING MYREP' ? date('Y-m-d H:i:s') : null,
            'submitted_to_finance_at' => $stagingStatus === 'WAITING FINANCE' ? date('Y-m-d H:i:s') : null,
            'released_at' => $stagingStatus === 'RELEASED' ? date('Y-m-d H:i:s') : null,
            'remark_batch_approval' => $remark !== '' ? $remark : null,
            'created_by' => $userId,
            'updated_by' => $userId,
        ], [
            'status_current' => $this->mapClusterStatusFromStaging($stagingStatus),
            'updated_by' => $userId,
        ], $pics);

        if ($batchId <= 0) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data Batch Approval.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        if ($this->MBatch_Approval_MyRep->batchDocumentTablesReady()) {
            $this->handleInitialBatchDocumentUpload($clusterId, $remark, $isNoDocumentRequired);
        }

        $this->session->set_flashdata('success', 'Data Batch Approval berhasil ditambahkan.');
        redirect('Batch_Approval_MyRep');
    }

    public function updateBatchApproval()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel Batch Approval MyRep belum tersedia. Jalankan query database flow baru terlebih dahulu.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $batchId = (int) $this->input->post('id_batch_approval');
        $hpDonasi = (int) $this->normalizeNumber($this->input->post('hp_donasi'));
        $nominalPengajuanArea = $this->normalizeNumber($this->input->post('nominal_pengajuan_area'));
        $nominalNegoEmr = $this->normalizeNullableNumber($this->input->post('nominal_nego_emr'));
        $nominalReleaseFinance = $this->normalizeNullableNumber($this->input->post('nominal_release_finance'));
        $freeWifiQty = $this->normalizeNullableInt($this->input->post('free_wifi_qty'));
        $freeWifiPeriodMonth = $this->normalizeNullableInt($this->input->post('free_wifi_period_month'));
        $recipientName = trim((string) $this->input->post('recipient_name'));
        $recipientPhone = trim((string) $this->input->post('recipient_phone'));
        $recipientPosition = trim((string) $this->input->post('recipient_position'));
        $recipientPeriod = trim((string) $this->input->post('recipient_period'));
        $bankName = trim((string) $this->input->post('bank_name'));
        $bankAccountNumber = trim((string) $this->input->post('bank_account_number'));
        $submissionDate = $this->normalizeDate($this->input->post('submission_date'));
        $stagingStatus = strtoupper(trim((string) $this->input->post('staging_status')));
        $astriBatchNumber = trim((string) $this->input->post('astri_batch_number'));
        $remark = trim((string) $this->input->post('remark_batch_approval'));
        $pics = $this->collectPicsFromPost();

        if ($clusterId <= 0 || $batchId <= 0 || $hpDonasi <= 0 || $nominalPengajuanArea <= 0 || $recipientName === '' || $bankName === '' || $bankAccountNumber === '') {
            $this->session->set_flashdata('error', 'Data update Batch Approval belum lengkap.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        if (empty($pics)) {
            $pics[] = [
                'pic_name' => $recipientName,
                'pic_phone' => $recipientPhone,
                'pic_position' => $recipientPosition,
                'pic_period' => $recipientPeriod,
                'is_primary' => 1,
            ];
        }

        $userId = (int) $this->session->userdata('id_user');
        $stagingStatus = $this->normalizeStagingStatus($stagingStatus, false);
        $nominalPerHomepass = $hpDonasi > 0 ? round($nominalPengajuanArea / $hpDonasi, 2) : 0;
        $existing = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        $result = $this->MBatch_Approval_MyRep->updateBatchApproval($clusterId, $batchId, [
            'submission_date' => $submissionDate,
            'hp_donasi' => $hpDonasi,
            'nominal_pengajuan_area' => $nominalPengajuanArea,
            'nominal_nego_emr' => $nominalNegoEmr,
            'nominal_release_finance' => $nominalReleaseFinance,
            'nominal_per_homepass' => $nominalPerHomepass,
            'bank_name' => $bankName,
            'bank_account_number' => $bankAccountNumber,
            'recipient_name' => $recipientName,
            'recipient_phone' => $recipientPhone,
            'recipient_position' => $recipientPosition,
            'recipient_period' => $recipientPeriod,
            'free_wifi_qty' => $freeWifiQty,
            'free_wifi_period_month' => $freeWifiPeriodMonth,
            'astri_batch_number' => $astriBatchNumber !== '' ? $astriBatchNumber : null,
            'staging_status' => $stagingStatus,
            'submitted_to_ho_at' => $this->resolveStageTimestamp($existing['submitted_to_ho_at'] ?? null, $stagingStatus === 'WAITING HO'),
            'submitted_to_astri_at' => $this->resolveStageTimestamp($existing['submitted_to_astri_at'] ?? null, $stagingStatus === 'WAITING MYREP'),
            'submitted_to_finance_at' => $this->resolveStageTimestamp($existing['submitted_to_finance_at'] ?? null, $stagingStatus === 'WAITING FINANCE'),
            'released_at' => $this->resolveStageTimestamp($existing['released_at'] ?? null, $stagingStatus === 'RELEASED'),
            'remark_batch_approval' => $remark !== '' ? $remark : null,
            'updated_by' => $userId,
        ], [
            'status_current' => $this->mapClusterStatusFromStaging($stagingStatus),
            'updated_by' => $userId,
        ], $pics);

        $redirectPath = $this->resolveBatchRedirectPath($clusterId);

        if (!$result) {
            $this->session->set_flashdata('error', 'Gagal memperbarui data Batch Approval.');
            redirect($redirectPath);
            return;
        }

        $this->session->set_flashdata('success', 'Data Batch Approval berhasil diperbarui.');
        redirect($redirectPath);
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

        $clusterId = (int) $this->input->post('cluster_id');
        $redirectPath = $this->resolveBatchRedirectPath($clusterId);

        if (!$this->MBatch_Approval_MyRep->batchTablesReady() || !$this->MBatch_Approval_MyRep->batchDocumentTablesReady()) {
            $this->handleUploadError('Tabel dokumen Batch Approval belum tersedia.', $redirectPath);
            return;
        }

        $context = $this->MBatch_Approval_MyRep->getBatchDocumentContext($clusterId);
        if ($clusterId <= 0 || empty($context['id_doc_item'])) {
            $this->handleUploadError('Konfigurasi dokumen RAR belum ditemukan.', $redirectPath);
            return;
        }

        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;
        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->handleUploadError('File RAR wajib dipilih.', $redirectPath);
            return;
        }

        $uploadDir = './uploads/myrep_batch_approval/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = '';
        $filePath = '';
        if (!$isNoDocumentRequired) {
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($context['doc_name'] ?? 'RAR'));
            $fileName = 'BATCH_' . $clusterId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
                'max_size' => 30720,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $this->handleUploadError(strip_tags($this->upload->display_errors()), $redirectPath);
                return;
            }

            $fileData = $this->upload->data();
            $fileName = $fileData['file_name'];
            $filePath = 'uploads/myrep_batch_approval/' . $fileData['file_name'];
        }

        $fileId = $this->MBatch_Approval_MyRep->saveBatchFileUpload($clusterId, [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($fileId <= 0) {
            $this->handleUploadError('Dokumen RAR gagal disimpan.', $redirectPath);
            return;
        }

        $this->handleUploadSuccess(
            $isNoDocumentRequired ? 'Dokumen RAR ditandai tidak dibutuhkan dan dikirim ke review.' : 'Dokumen RAR berhasil diupload.',
            $redirectPath
        );
    }

    public function uploadTransferProof()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $redirectPath = $this->resolveBatchRedirectPath($clusterId);

        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel Batch Approval belum tersedia.');
            redirect($redirectPath);
            return;
        }

        $batchId = (int) $this->input->post('id_batch_approval');
        if ($clusterId <= 0 || $batchId <= 0 || empty($_FILES['transfer_proof']['name'])) {
            $this->session->set_flashdata('error', 'Data upload bukti transfer belum lengkap.');
            redirect($redirectPath);
            return;
        }

        $uploadDir = './uploads/myrep_batch_transfer/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($_FILES['transfer_proof']['name'], PATHINFO_EXTENSION);
        $fileName = 'TRANSFER_' . $clusterId . '_' . date('YmdHis') . '.' . $extension;
        $config = [
            'upload_path' => $uploadDir,
            'allowed_types' => 'pdf|jpg|jpeg|png',
            'max_size' => 30720,
            'file_name' => $fileName,
            'overwrite' => true,
        ];

        $this->upload->initialize($config);
        if (!$this->upload->do_upload('transfer_proof')) {
            $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
            redirect($redirectPath);
            return;
        }

        $fileData = $this->upload->data();
        $this->MBatch_Approval_MyRep->updateTransferProof($batchId, [
            'transfer_proof_file_name' => $fileData['file_name'],
            'transfer_proof_file_path' => 'uploads/myrep_batch_transfer/' . $fileData['file_name'],
            'staging_status' => 'RELEASED',
            'released_at' => date('Y-m-d H:i:s'),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ], [
            'status_current' => 'RELEASED',
            'updated_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata('success', 'Bukti transfer berhasil diupload dan status diubah ke RELEASED.');
        redirect($redirectPath);
    }

    public function updateStagingProgress()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $batchId = (int) $this->input->post('id_batch_approval');
        $redirectPath = $this->resolveBatchRedirectPath($clusterId);

        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel Batch Approval belum tersedia.');
            redirect($redirectPath);
            return;
        }

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses mengubah staging Batch Approval.');
            redirect($redirectPath);
            return;
        }

        $batch = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        if ($clusterId <= 0 || $batchId <= 0 || empty($batch) || (int) ($batch['id_batch_approval'] ?? 0) !== $batchId) {
            $this->session->set_flashdata('error', 'Data Batch Approval tidak ditemukan.');
            redirect($redirectPath);
            return;
        }

        $currentStage = strtoupper(trim((string) ($batch['staging_status'] ?? 'DRAFT')));
        $targetStage = strtoupper(trim((string) $this->input->post('target_stage')));
        $userId = (int) $this->session->userdata('id_user');
        $batchPayload = ['updated_by' => $userId];
        $successMessage = 'Staging Batch Approval berhasil diperbarui.';

        if ($currentStage === 'WAITING HO' && $targetStage === 'WAITING MYREP') {
            $submittedToMyrepAt = $this->normalizeDateTimeInput($this->input->post('submitted_to_astri_at'));
            if ($submittedToMyrepAt === null) {
                $this->session->set_flashdata('error', 'Tanggal input ke MYREP wajib diisi.');
                redirect($redirectPath);
                return;
            }

            $batchPayload['staging_status'] = 'WAITING MYREP';
            $batchPayload['submitted_to_astri_at'] = $submittedToMyrepAt;
            $successMessage = 'Staging berhasil diubah ke WAITING MYREP.';
        } elseif ($currentStage === 'WAITING MYREP' && $targetStage === 'WAITING FINANCE') {
            $astriBatchNumber = trim((string) $this->input->post('astri_batch_number'));
            $nominalApprovalMyrep = $this->normalizeNullableNumber($this->input->post('nominal_nego_emr'));
            $approvedMyrepAt = $this->normalizeDateTimeInput($this->input->post('submitted_to_finance_at'));

            if ($astriBatchNumber === '' || $nominalApprovalMyrep === null || $approvedMyrepAt === null) {
                $this->session->set_flashdata('error', 'Nomor batch, nominal approval MYREP, dan tanggal approved MYREP wajib diisi.');
                redirect($redirectPath);
                return;
            }

            $batchPayload['staging_status'] = 'WAITING FINANCE';
            $batchPayload['astri_batch_number'] = $astriBatchNumber;
            $batchPayload['nominal_nego_emr'] = $nominalApprovalMyrep;
            $batchPayload['submitted_to_finance_at'] = $approvedMyrepAt;
            $successMessage = 'Staging berhasil diubah ke WAITING FINANCE.';
        } elseif ($currentStage === 'WAITING FINANCE' && $targetStage === 'RELEASED') {
            $releasedAt = $this->normalizeDateTimeInput($this->input->post('released_at'));
            $nominalReleaseFinance = $this->normalizeNullableNumber($this->input->post('nominal_release_finance'));

            if ($releasedAt === null || $nominalReleaseFinance === null || empty($_FILES['transfer_proof']['name'])) {
                $this->session->set_flashdata('error', 'Tanggal pencairan, nominal cair, dan foto transfer wajib diisi.');
                redirect($redirectPath);
                return;
            }

            $uploadDir = './uploads/myrep_batch_transfer/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['transfer_proof']['name'], PATHINFO_EXTENSION);
            $fileName = 'TRANSFER_' . $clusterId . '_' . date('YmdHis') . '.' . $extension;
            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'pdf|jpg|jpeg|png',
                'max_size' => 30720,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('transfer_proof')) {
                $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
                redirect($redirectPath);
                return;
            }

            $fileData = $this->upload->data();
            $batchPayload['staging_status'] = 'RELEASED';
            $batchPayload['released_at'] = $releasedAt;
            $batchPayload['nominal_release_finance'] = $nominalReleaseFinance;
            $batchPayload['transfer_proof_file_name'] = $fileData['file_name'];
            $batchPayload['transfer_proof_file_path'] = 'uploads/myrep_batch_transfer/' . $fileData['file_name'];
            $successMessage = 'Staging berhasil diubah ke RELEASED.';
        } else {
            $this->session->set_flashdata('error', 'Transisi staging tidak valid.');
            redirect($redirectPath);
            return;
        }

        $result = $this->MBatch_Approval_MyRep->updateBatchStage(
            $clusterId,
            $batchId,
            $batchPayload,
            [
                'status_current' => $this->mapClusterStatusFromStaging($batchPayload['staging_status']),
                'updated_by' => $userId,
            ]
        );

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? $successMessage : 'Gagal memperbarui staging Batch Approval.');
        redirect($redirectPath);
    }

    public function approveDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $redirectPath = $this->resolveBatchRedirectPath($clusterId);

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen Batch Approval.');
            redirect($redirectPath);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        if ($fileId <= 0) {
            $this->session->set_flashdata('error', 'Dokumen Batch Approval tidak ditemukan.');
            redirect($redirectPath);
            return;
        }

        $result = $this->MBatch_Approval_MyRep->updateBatchFileStatus($fileId, [
            'status_file' => 'APPROVED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen RAR berhasil di-approve.' : 'Gagal approve dokumen RAR.');
        redirect($redirectPath);
    }

    public function rejectDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $redirectPath = $this->resolveBatchRedirectPath($clusterId);

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen Batch Approval.');
            redirect($redirectPath);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        if ($fileId <= 0) {
            $this->session->set_flashdata('error', 'Dokumen Batch Approval tidak ditemukan.');
            redirect($redirectPath);
            return;
        }

        $result = $this->MBatch_Approval_MyRep->updateBatchFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen RAR berhasil di-reject.' : 'Gagal reject dokumen RAR.');
        redirect($redirectPath);
    }

    public function previewDocument($fileId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $file = $this->MBatch_Approval_MyRep->getBatchFileById((int) $fileId);
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

    private function normalizeStagingStatus($status, $isCreate)
    {
        $allowed = ['DRAFT', 'WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL', 'REJECTED'];
        $status = strtoupper(trim((string) $status));
        if (!in_array($status, $allowed, true)) {
            return $isCreate ? 'WAITING HO' : 'DRAFT';
        }

        return $status;
    }

    private function mapClusterStatusFromStaging($stagingStatus)
    {
        $stagingStatus = strtoupper(trim((string) $stagingStatus));
        if ($stagingStatus === 'REJECTED') {
            return 'REJECTED';
        }

        if ($stagingStatus !== '') {
            return $stagingStatus;
        }

        return 'VALSAL';
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

        $normalized = preg_replace('/[^\d,.\-]/', '', trim((string) $value));
        if ($normalized === '' || $normalized === '-' || $normalized === ',' || $normalized === '.') {
            return 0;
        }

        $hasComma = strpos($normalized, ',') !== false;
        $dotCount = substr_count($normalized, '.');

        if ($hasComma) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif ($dotCount > 1) {
            $normalized = str_replace('.', '', $normalized);
        } elseif ($dotCount === 1) {
            $parts = explode('.', $normalized);
            $decimalLength = isset($parts[1]) ? strlen($parts[1]) : 0;
            if ($decimalLength === 3) {
                $normalized = implode('', $parts);
            }
        }

        return (float) $normalized;
    }

    private function normalizeNullableNumber($value)
    {
        $value = trim((string) $value);
        return $value === '' ? null : $this->normalizeNumber($value);
    }

    private function normalizeNullableInt($value)
    {
        $value = trim((string) $value);
        return $value === '' ? null : (int) $this->normalizeNumber($value);
    }

    private function normalizeDateTimeInput($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value . ' 00:00:00';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    private function collectPicsFromPost()
    {
        $names = (array) $this->input->post('pic_name');
        $phones = (array) $this->input->post('pic_phone');
        $positions = (array) $this->input->post('pic_position');
        $periods = (array) $this->input->post('pic_period');

        $pics = [];
        $picNo = 1;
        foreach ($names as $index => $name) {
            $name = trim((string) $name);
            $phone = trim((string) ($phones[$index] ?? ''));
            $position = trim((string) ($positions[$index] ?? ''));
            $period = trim((string) ($periods[$index] ?? ''));

            if ($name === '') {
                continue;
            }

            $pics[] = [
                'pic_no' => $picNo,
                'pic_name' => $name,
                'pic_phone' => $phone !== '' ? $phone : null,
                'pic_position' => $position !== '' ? $position : null,
                'pic_period' => $period !== '' ? $period : null,
                'is_primary' => $picNo === 1 ? 1 : 0,
            ];
            $picNo++;

            if ($picNo > 5) {
                break;
            }
        }

        return $pics;
    }

    private function resolveStageTimestamp($existingTimestamp, $shouldSet)
    {
        if (!$shouldSet) {
            return $existingTimestamp;
        }

        return !empty($existingTimestamp) ? $existingTimestamp : date('Y-m-d H:i:s');
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
            redirect('Batch_Approval_MyRep');
            return;
        }

        $deleted = $this->MMyRep_Cleanup->deleteWholeCluster($clusterId);
        $this->session->set_flashdata($deleted ? 'success' : 'error', $deleted ? 'Cluster MyRep beserta flow Batch Approval dan seluruh tahap sebelumnya berhasil dihapus bersih.' : 'Gagal menghapus cluster MyRep.');
        redirect('Batch_Approval_MyRep');
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

    private function resolveBatchRedirectPath($clusterId = 0)
    {
        if ((int) $this->input->post('redirect_to_detail') === 1 && (int) $clusterId > 0) {
            return 'Batch_Approval_MyRep/detail/' . (int) $clusterId;
        }

        return 'Batch_Approval_MyRep';
    }

    private function handleInitialBatchDocumentUpload($clusterId, $remark = '', $isNoDocumentRequired = false)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return;
        }

        $context = $this->MBatch_Approval_MyRep->getBatchDocumentContext($clusterId);
        if (empty($context['id_doc_item'])) {
            return;
        }

        if ($isNoDocumentRequired) {
            $this->MBatch_Approval_MyRep->saveBatchFileUpload($clusterId, [
                'file_name' => '',
                'file_path' => '',
                'is_document_not_required' => 1,
                'status_file' => 'TIDAK BUTUH DOKUMENT',
                'remark' => $remark,
                'uploaded_by' => (int) $this->session->userdata('id_user'),
            ]);
            return;
        }

        if (empty($_FILES['batch_rar_file']['name'])) {
            return;
        }

        $uploadDir = './uploads/myrep_batch_approval/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($_FILES['batch_rar_file']['name'], PATHINFO_EXTENSION);
        $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($context['doc_name'] ?? 'RAR'));
        $fileName = 'BATCH_' . $clusterId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

        $config = [
            'upload_path' => $uploadDir,
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png|rar|zip',
            'max_size' => 30720,
            'file_name' => $fileName,
            'overwrite' => true,
        ];

        $this->upload->initialize($config);
        if (!$this->upload->do_upload('batch_rar_file')) {
            return;
        }

        $fileData = $this->upload->data();
        $this->MBatch_Approval_MyRep->saveBatchFileUpload($clusterId, [
            'file_name' => $fileData['file_name'],
            'file_path' => 'uploads/myrep_batch_approval/' . $fileData['file_name'],
            'is_document_not_required' => 0,
            'status_file' => 'UPLOADED',
            'remark' => $remark,
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);
    }
}
