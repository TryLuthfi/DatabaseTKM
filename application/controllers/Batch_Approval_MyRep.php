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
        $this->load->library('Myrep_notification_service', null, 'myrepNotifier');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('Batch_Approval_MyRep');
            $this->myrepAccess->enforceByMethod('Batch_Approval_MyRep', (string) $this->router->fetch_method(), [
                'previewBatchImport' => 'TAMBAH',
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

        $data['title'] = 'Batch Approval MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['isReady'] = $this->MBatch_Approval_MyRep->batchTablesReady();
        $data['docReady'] = $this->MBatch_Approval_MyRep->batchDocumentTablesReady();
        $data['canApprove'] = $this->isApprover();
        $data['cityOptions'] = $this->MBatch_Approval_MyRep->getCityOptions();
        $data['regionalOptions'] = $this->MBatch_Approval_MyRep->getRegionalOptions();
        $data['cityOptionsByRegional'] = $this->MBatch_Approval_MyRep->getCityOptionsByRegional();
        $data['regionalOptionsByCity'] = $this->MBatch_Approval_MyRep->getRegionalOptionsByCity();
        $data['eligibleClusterOptions'] = $this->MBatch_Approval_MyRep->getEligibleClusterOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MBatch_Approval_MyRep->getBatchRows($selectedCity, $selectedStatus)
            : [];
        $data['clusterReviewPicMap'] = $this->MBatch_Approval_MyRep->getBatchClusterReviewPicMap($data['clusterRows']);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Batch_Approval_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function downloadReport()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
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
        $submissionDateStart = $this->normalizeDate($this->input->get('submission_date_start')) ?: '';
        $submissionDateEnd = $this->normalizeDate($this->input->get('submission_date_end')) ?: '';

        $rows = $this->MBatch_Approval_MyRep->getBatchRows($selectedCity, $selectedStatus, $selectedRegional, $cityList, $regionalList, $submissionDateStart, $submissionDateEnd);

        $filename = 'report_batch_approval_myrep_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Cluster',
            'Kode Cluster',
            'Regional',
            'Kota',
            'Periode Target',
            'Tanggal Submission',
            'HP Donasi',
            'Nominal Pengajuan Area',
            'Nominal Nego EMR',
            'Nominal Release Finance',
            'Staging Status',
            'Status Flow',
            'Nomor Batch Astri',
            'Remark Batch Approval',
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
                (string) ($row['submission_date'] ?? ''),
                (string) (int) ($row['hp_donasi'] ?? 0),
                (string) (float) ($row['nominal_pengajuan_area'] ?? 0),
                (string) (float) ($row['nominal_nego_emr'] ?? 0),
                (string) (float) ($row['nominal_release_finance'] ?? 0),
                (string) ($row['staging_status'] ?? ''),
                (string) ($row['status_current'] ?? ''),
                (string) ($row['astri_batch_number'] ?? ''),
                (string) ($row['remark_batch_approval'] ?? ''),
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
        $linkedSupportDocumentMap = $data['docReady']
            ? $this->MBatch_Approval_MyRep->getAutoLinkedSupportDocumentMap($clusterId)
            : [];
        foreach ($data['postDonasiRows'] as &$postDonasiRow) {
            $normalizedDocName = strtoupper(trim((string) ($postDonasiRow['doc_name'] ?? '')));
            if (isset($linkedSupportDocumentMap[$normalizedDocName])) {
                $postDonasiRow = array_merge($postDonasiRow, $linkedSupportDocumentMap[$normalizedDocName]);
            }
        }
        unset($postDonasiRow);

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
            $this->session->set_flashdata('error', 'Cluster belum memenuhi syarat (VALSAL DONE/APPROVED) atau tidak termasuk city mapping user.');
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
        ], $pics, $cluster);

        if ($batchId <= 0) {
            $errorDetail = trim((string) $this->MBatch_Approval_MyRep->getLastErrorMessage());
            $this->session->set_flashdata(
                'error',
                'Gagal menyimpan data Batch Approval.'
                . ($errorDetail !== '' ? ' Detail: ' . $errorDetail : '')
            );
            redirect('Batch_Approval_MyRep');
            return;
        }

        if ($this->MBatch_Approval_MyRep->batchDocumentTablesReady()) {
            $this->handleInitialBatchDocumentUpload($clusterId, $remark, $isNoDocumentRequired, false);
        }

        $clusterDetail = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        $this->sendBatchNotification('cluster_masuk', $clusterDetail, 'Batch Approval');

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
        $notificationEvent = !empty($context['id_doc_file']) ? 'document_revised' : 'document_masuk';

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

        $clusterDetail = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        $this->sendBatchNotification($notificationEvent, $clusterDetail, (string) ($context['doc_name'] ?? 'RAR'));

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
                $this->session->set_flashdata('error', 'Tanggal pencairan, nominal pencairan, dan foto transfer wajib diisi.');
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

    public function downloadBatchImportTemplate()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

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
            'hp_donasi',
            'nominal_pengajuan_area',
            'nominal_nego_emr',
            'nominal_release_finance',
            'submission_date',
            'staging_status',
            'astri_batch_number',
            'recipient_name',
            'recipient_phone',
            'recipient_position',
            'recipient_period',
            'bank_name',
            'bank_account_number',
            'free_wifi_qty',
            'free_wifi_period_month',
            'remark_batch_approval',
            'pic_name',
            'pic_phone',
            'pic_position',
            'pic_period',
        ];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=batch_approval_import_template_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        $exampleRows = [
            ['', '', 'MALANG', 'Cluster A', 'CL-A', '100', date('Y-m-d'), 'DONE', 'VALSAL done', '80', '24000000', '', '', date('Y-m-d'), 'DRAFT', '', 'Budi', '081200000001', 'Ketua RT', '2026', 'BCA', '1234567890', '', '', 'Contoh status DRAFT', 'Budi', '081200000001', 'Ketua RT', '2026'],
            ['', '', 'MALANG', 'Cluster B', 'CL-B', '120', date('Y-m-d'), 'APPROVED', 'VALSAL approved', '100', '30000000', '', '', date('Y-m-d'), 'WAITING HO', '', 'Andi', '081200000002', 'Ketua RW', '2026', 'BNI', '1234567891', '1', '12', 'Contoh status WAITING HO', 'Andi', '081200000002', 'Ketua RW', '2026'],
            ['', '', 'MALANG', 'Cluster C', 'CL-C', '90', date('Y-m-d'), 'DONE', 'VALSAL done', '70', '21000000', '20000000', '', date('Y-m-d'), 'WAITING MYREP', 'ASTRI-001', 'Sari', '081200000003', 'Tokoh Masyarakat', '2026', 'MANDIRI', '1234567892', '', '', 'Contoh status WAITING MYREP', 'Sari', '081200000003', 'Tokoh Masyarakat', '2026'],
            ['', '', 'MALANG', 'Cluster D', 'CL-D', '110', date('Y-m-d'), 'DONE', 'VALSAL done', '90', '27000000', '26000000', '', date('Y-m-d'), 'WAITING FINANCE', 'ASTRI-002', 'Rina', '081200000004', 'Ketua Panitia', '2026', 'BRI', '1234567893', '', '', 'Contoh status WAITING FINANCE', 'Rina', '081200000004', 'Ketua Panitia', '2026'],
            ['', '', 'MALANG', 'Cluster E', 'CL-E', '130', date('Y-m-d'), 'DONE', 'VALSAL done', '100', '30000000', '29500000', '29500000', date('Y-m-d'), 'RELEASED', 'ASTRI-003', 'Doni', '081200000005', 'Koordinator', '2026', 'CIMB', '1234567894', '', '', 'Contoh status RELEASED', 'Doni', '081200000005', 'Koordinator', '2026'],
            ['', '', 'MALANG', 'Cluster F', 'CL-F', '95', date('Y-m-d'), 'DONE', 'VALSAL done', '75', '22500000', '22000000', '22000000', date('Y-m-d'), 'DONE BATCH APPROVAL', 'ASTRI-004', 'Lina', '081200000006', 'Sekretaris', '2026', 'PERMATA', '1234567895', '', '', 'Contoh status DONE BATCH APPROVAL', 'Lina', '081200000006', 'Sekretaris', '2026'],
            ['', '', 'MALANG', 'Cluster G', 'CL-G', '85', date('Y-m-d'), 'DONE', 'VALSAL done', '60', '18000000', '', '', date('Y-m-d'), 'REJECTED', '', 'Yoga', '081200000007', 'Bendahara', '2026', 'DANAMON', '1234567896', '', '', 'Contoh status REJECTED', 'Yoga', '081200000007', 'Bendahara', '2026'],
        ];
        foreach ($exampleRows as $exampleRow) {
            fputcsv($output, $exampleRow);
        }
        fclose($output);
        exit;
    }

    public function previewBatchImport()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session login tidak ditemukan.');
            return;
        }

        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
            $this->jsonResponse(false, 'Tabel Batch Approval MyRep belum tersedia.');
            return;
        }

        if (empty($_FILES['file_excel']['name'])) {
            $this->jsonResponse(false, 'File import belum dipilih.');
            return;
        }

        $uploadDir = FCPATH . 'uploads/temp_batch_import/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['xls', 'xlsx', 'csv'], true)) {
            $this->jsonResponse(false, 'Format file harus xls/xlsx/csv.');
            return;
        }

        $tempName = 'batch_import_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $tempPath = $uploadDir . $tempName;
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
        foreach ($sheetData as $row) {
            $headerRow = $row;
            break;
        }

        $mappedHeader = [];
        foreach ($headerRow as $columnKey => $columnName) {
            $normalizedKey = $this->parseBatchImportHeader((string) $columnName);
            if ($normalizedKey !== null) {
                $mappedHeader[$columnKey] = $normalizedKey;
            }
        }

        $rows = [];
        $rowIndex = 0;
        foreach ($sheetData as $row) {
            $rowIndex++;
            if ($rowIndex === 1) {
                continue;
            }

            $item = [];
            $isBlank = true;
            foreach ($mappedHeader as $columnKey => $fieldName) {
                $value = isset($row[$columnKey]) ? trim((string) $row[$columnKey]) : '';
                if ($value !== '') {
                    $isBlank = false;
                }
                $item[$fieldName] = $value;
            }

            if (!$isBlank) {
                $rows[] = $item;
            }
        }

        $validated = $this->validateBatchImportRows($rows);
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

    public function saveImportedBatch()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session login tidak ditemukan.');
            return;
        }

        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
            $this->jsonResponse(false, 'Tabel Batch Approval MyRep belum tersedia.');
            return;
        }

        $rowsJson = $this->input->post('rows_json');
        $rows = json_decode((string) $rowsJson, true);
        if (empty($rows) || !is_array($rows)) {
            $this->jsonResponse(false, 'Tidak ada data import yang siap disimpan.');
            return;
        }

        $validated = $this->validateBatchImportRows($rows);
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
            $clusterName = trim((string) ($row['cluster_name'] ?? ''));
            $clusterCode = trim((string) ($row['cluster_code'] ?? ''));
            $homepassValsal = (int) $this->normalizeNumber($row['homepass_valsal'] ?? 0);
            $valsalDate = $this->normalizeDate((string) ($row['valsal_date'] ?? '')) ?: date('Y-m-d');
            $remarkValsal = trim((string) ($row['remark_valsal'] ?? ''));
            $isNewCluster = (int) ($row['is_new_cluster'] ?? 0) === 1;

            if ($clusterId <= 0 && $isNewCluster) {
                $clusterId = $this->MBatch_Approval_MyRep->createClusterForBatchImport(
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

            $bakSynced = $this->MBatch_Approval_MyRep->upsertBakDoneForBatchImport(
                $clusterId,
                $homepassValsal,
                $valsalDate,
                $userId,
                $remarkValsal
            );
            $valsalSynced = $bakSynced ? $this->MBatch_Approval_MyRep->upsertValsalDoneForBatchImport(
                $clusterId,
                $homepassValsal,
                $valsalDate,
                $userId,
                $remarkValsal
            ) : false;
            if (!$bakSynced || !$valsalSynced) {
                continue;
            }

            $stagingStatus = $this->normalizeStagingStatus((string) ($row['staging_status'] ?? 'WAITING HO'), true);
            $hpDonasi = (int) $this->normalizeNumber($row['hp_donasi'] ?? 0);
            $nominalPengajuanArea = $this->normalizeNumber($row['nominal_pengajuan_area'] ?? 0);
            $nominalNegoEmr = $this->normalizeNullableNumber($row['nominal_nego_emr'] ?? '');
            $nominalReleaseFinance = $this->normalizeNullableNumber($row['nominal_release_finance'] ?? '');
            $nominalPerHomepass = $hpDonasi > 0 ? round($nominalPengajuanArea / $hpDonasi, 2) : 0;

            $pics = [[
                'pic_no' => 1,
                'pic_name' => (string) ($row['pic_name'] ?? $row['recipient_name'] ?? ''),
                'pic_phone' => trim((string) ($row['pic_phone'] ?? $row['recipient_phone'] ?? '')) ?: null,
                'pic_position' => trim((string) ($row['pic_position'] ?? $row['recipient_position'] ?? '')) ?: null,
                'pic_period' => trim((string) ($row['pic_period'] ?? $row['recipient_period'] ?? '')) ?: null,
                'is_primary' => 1,
            ]];

            $batchId = $this->MBatch_Approval_MyRep->createBatchApproval($clusterId, [
                'submission_date' => $this->normalizeDate((string) ($row['submission_date'] ?? '')) ?: date('Y-m-d'),
                'hp_donasi' => $hpDonasi,
                'nominal_pengajuan_area' => $nominalPengajuanArea,
                'nominal_nego_emr' => $nominalNegoEmr,
                'nominal_release_finance' => $nominalReleaseFinance,
                'nominal_per_homepass' => $nominalPerHomepass,
                'bank_name' => (string) ($row['bank_name'] ?? ''),
                'bank_account_number' => (string) ($row['bank_account_number'] ?? ''),
                'recipient_name' => (string) ($row['recipient_name'] ?? ''),
                'recipient_phone' => trim((string) ($row['recipient_phone'] ?? '')) ?: null,
                'recipient_position' => trim((string) ($row['recipient_position'] ?? '')) ?: null,
                'recipient_period' => trim((string) ($row['recipient_period'] ?? '')) ?: null,
                'free_wifi_qty' => $this->normalizeNullableInt($row['free_wifi_qty'] ?? ''),
                'free_wifi_period_month' => $this->normalizeNullableInt($row['free_wifi_period_month'] ?? ''),
                'astri_batch_number' => trim((string) ($row['astri_batch_number'] ?? '')) ?: null,
                'staging_status' => $stagingStatus,
                'submitted_to_ho_at' => $stagingStatus === 'WAITING HO' ? date('Y-m-d H:i:s') : null,
                'submitted_to_astri_at' => $stagingStatus === 'WAITING MYREP' ? date('Y-m-d H:i:s') : null,
                'submitted_to_finance_at' => $stagingStatus === 'WAITING FINANCE' ? date('Y-m-d H:i:s') : null,
                'released_at' => $stagingStatus === 'RELEASED' ? date('Y-m-d H:i:s') : null,
                'remark_batch_approval' => trim((string) ($row['remark_batch_approval'] ?? '')) ?: null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ], [
                'status_current' => $this->mapClusterStatusFromStaging($stagingStatus),
                'updated_by' => $userId,
            ], $pics);

            if ($batchId > 0) {
                $inserted++;
            }
        }

        if ($inserted <= 0) {
            $errorDetail = trim((string) $this->MBatch_Approval_MyRep->getLastErrorMessage());
            $this->jsonResponse(
                false,
                'Gagal menyimpan hasil import Batch Approval.'
                . ($errorDetail !== '' ? ' Detail: ' . $errorDetail : '')
            );
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'message' => $inserted . ' data Batch Approval berhasil diimport.',
            ]));
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

    private function parseBatchImportHeader($header)
    {
        $header = strtolower(trim((string) $header));
        if ($header === '') {
            return null;
        }

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
            'status_valsal' => ['status_valsal', 'status_valsal_input'],
            'remark_valsal' => ['remark_valsal', 'catatan_valsal'],
            'hp_donasi' => ['hp_donasi'],
            'nominal_pengajuan_area' => ['nominal_pengajuan_area'],
            'nominal_nego_emr' => ['nominal_nego_emr'],
            'nominal_release_finance' => ['nominal_release_finance'],
            'submission_date' => ['submission_date', 'tanggal_pengajuan'],
            'staging_status' => ['staging_status', 'status_batch'],
            'astri_batch_number' => ['astri_batch_number', 'nomor_batch_astri'],
            'recipient_name' => ['recipient_name', 'nama_penerima'],
            'recipient_phone' => ['recipient_phone', 'telp_penerima'],
            'recipient_position' => ['recipient_position', 'jabatan_penerima'],
            'recipient_period' => ['recipient_period', 'periode_penerima'],
            'bank_name' => ['bank_name', 'nama_bank'],
            'bank_account_number' => ['bank_account_number', 'no_rekening'],
            'free_wifi_qty' => ['free_wifi_qty'],
            'free_wifi_period_month' => ['free_wifi_period_month'],
            'remark_batch_approval' => ['remark_batch_approval', 'remark_batch', 'catatan_batch'],
            'pic_name' => ['pic_name', 'nama_pic'],
            'pic_phone' => ['pic_phone', 'telp_pic'],
            'pic_position' => ['pic_position', 'jabatan_pic'],
            'pic_period' => ['pic_period', 'periode_pic'],
        ];

        foreach ($aliases as $field => $options) {
            if (in_array($header, $options, true)) {
                return $field;
            }
        }

        return null;
    }

    private function validateBatchImportRows(array $rawRows)
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
            $statusValsal = strtoupper(trim((string) ($rawRow['status_valsal'] ?? 'DONE')));
            $remarkValsal = trim((string) ($rawRow['remark_valsal'] ?? ''));

            $hpDonasi = (int) $this->normalizeNumber($rawRow['hp_donasi'] ?? 0);
            $nominalPengajuanArea = $this->normalizeNumber($rawRow['nominal_pengajuan_area'] ?? 0);
            $submissionDate = $this->normalizeDate((string) ($rawRow['submission_date'] ?? '')) ?: date('Y-m-d');
            $stagingStatus = $this->normalizeStagingStatus((string) ($rawRow['staging_status'] ?? 'WAITING HO'), true);
            $recipientName = trim((string) ($rawRow['recipient_name'] ?? ''));
            $bankName = trim((string) ($rawRow['bank_name'] ?? ''));
            $bankAccountNumber = trim((string) ($rawRow['bank_account_number'] ?? ''));

            $rowErrors = [];
            $candidate = [];
            if ($clusterId > 0) {
                $candidate = $this->MBatch_Approval_MyRep->getClusterForBatchImportById($clusterId);
            }

            if (empty($candidate) && $clusterName !== '') {
                if ($targetId <= 0 && $cityName !== '') {
                    $target = $this->MBatch_Approval_MyRep->getTargetByCity($cityName);
                    $targetId = (int) ($target['id_target'] ?? 0);
                }
                $candidate = $this->MBatch_Approval_MyRep->getClusterForBatchImportByName($clusterName, $cityName, $targetId);
                $clusterId = (int) ($candidate['id_myrep_cluster'] ?? 0);
            }

            $isNewCluster = false;
            if (empty($candidate) || $clusterId <= 0) {
                if ($targetId <= 0 && $cityName !== '') {
                    $target = $this->MBatch_Approval_MyRep->getTargetByCity($cityName);
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
                if (!empty($candidate['id_batch_approval'])) {
                    $rowErrors[] = 'Cluster sudah punya data Batch Approval';
                }
            }

            if ($homepassValsal <= 0 || $hpDonasi <= 0) {
                $rowErrors[] = 'homepass_valsal dan hp_donasi wajib > 0';
            }
            if ($nominalPengajuanArea <= 0) {
                $rowErrors[] = 'nominal_pengajuan_area wajib > 0';
            }
            if ($recipientName === '' || $bankName === '' || $bankAccountNumber === '') {
                $rowErrors[] = 'recipient_name, bank_name, bank_account_number wajib diisi';
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
                'hp_donasi' => $hpDonasi,
                'nominal_pengajuan_area' => $nominalPengajuanArea,
                'nominal_nego_emr' => $rawRow['nominal_nego_emr'] ?? '',
                'nominal_release_finance' => $rawRow['nominal_release_finance'] ?? '',
                'submission_date' => $submissionDate,
                'staging_status' => $stagingStatus,
                'astri_batch_number' => (string) ($rawRow['astri_batch_number'] ?? ''),
                'recipient_name' => $recipientName,
                'recipient_phone' => (string) ($rawRow['recipient_phone'] ?? ''),
                'recipient_position' => (string) ($rawRow['recipient_position'] ?? ''),
                'recipient_period' => (string) ($rawRow['recipient_period'] ?? ''),
                'bank_name' => $bankName,
                'bank_account_number' => $bankAccountNumber,
                'free_wifi_qty' => $rawRow['free_wifi_qty'] ?? '',
                'free_wifi_period_month' => $rawRow['free_wifi_period_month'] ?? '',
                'remark_batch_approval' => (string) ($rawRow['remark_batch_approval'] ?? ''),
                'pic_name' => (string) ($rawRow['pic_name'] ?? ''),
                'pic_phone' => (string) ($rawRow['pic_phone'] ?? ''),
                'pic_position' => (string) ($rawRow['pic_position'] ?? ''),
                'pic_period' => (string) ($rawRow['pic_period'] ?? ''),
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
                $validRows[] = $preparedRow;
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

    private function handleInitialBatchDocumentUpload($clusterId, $remark = '', $isNoDocumentRequired = false, $shouldNotify = true)
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
        $fileId = $this->MBatch_Approval_MyRep->saveBatchFileUpload($clusterId, [
            'file_name' => $fileData['file_name'],
            'file_path' => 'uploads/myrep_batch_approval/' . $fileData['file_name'],
            'is_document_not_required' => 0,
            'status_file' => 'UPLOADED',
            'remark' => $remark,
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);
        if ($fileId > 0 && $shouldNotify) {
            $clusterDetail = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
            $this->sendBatchNotification('document_masuk', $clusterDetail, (string) ($context['doc_name'] ?? 'RAR'));
        }
    }

    private function sendBatchNotification($eventName, array $cluster, $documentLabel)
    {
        $clusterId = (int) ($cluster['id_myrep_cluster'] ?? 0);
        if ($clusterId <= 0) {
            return;
        }

        $this->myrepNotifier->notify('Batch_Approval_MyRep', $eventName, [
            'module_label' => 'Batch Approval',
            'document_label' => (string) $documentLabel,
            'regional_name' => (string) ($cluster['regional_name'] ?? ''),
            'city_name' => (string) ($cluster['city_name'] ?? ''),
            'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
            'homepass' => (int) ($cluster['hp_donasi'] ?? 0),
            'donation_total' => (float) ($cluster['nominal_pengajuan_area'] ?? 0),
            'nominal_per_homepass' => (float) ($cluster['nominal_per_homepass'] ?? 0),
            'sender_name' => (string) $this->session->userdata('nama_user'),
            'detail_url' => base_url('Batch_Approval_MyRep/detail/' . $clusterId),
        ]);
    }
}
