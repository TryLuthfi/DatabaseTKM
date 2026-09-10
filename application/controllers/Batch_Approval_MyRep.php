<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Batch_Approval_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MBatch_Approval_MyRep');
        $this->load->model('MPost_Donasi_MyRep');
        $this->load->library('upload');
        $this->load->library('Myrep_notification_service', null, 'myrepNotifier');
        $this->load->library('Myrep_reject_email_service', null, 'myrepRejectEmail');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('Batch_Approval_MyRep');
            $this->myrepAccess->enforceByMethod('Batch_Approval_MyRep', (string) $this->router->fetch_method(), [
                'tableData' => 'VIEW',
                'previewBatchImport' => 'TAMBAH',
                'printChecklistPengajuan' => 'VIEW',
                'saveImportedBatch' => 'TAMBAH',
                'updateBatchApproval' => 'VIEW',
                'uploadDocument' => 'VIEW',
                'uploadDonationDocument' => 'VIEW',
                'uploadBulkDonationDocuments' => 'VIEW',
                'approveDonationDocument' => 'APPROVAL',
                'approveDonationFinanceDocument' => 'APPROVAL',
                'rejectDonationDocument' => 'APPROVAL',
                'rejectDonationFinanceDocument' => 'APPROVAL',
                'approveAllDonationDocuments' => 'APPROVAL',
                'approveAllDonationFinanceDocuments' => 'APPROVAL',
                'updateDonationAstriStatus' => 'APPROVAL',
                'bulkUpdateDonationAstriStatus' => 'APPROVAL',
                'saveDonationPoInvoice' => 'APPROVAL',
            ]);
        }
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Session habis. Silakan login ulang.');
                return;
            }
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
        $summaryRows = $data['isReady']
            ? $this->MBatch_Approval_MyRep->getBatchRows($selectedCity, '')
            : [];
        $data['summaryRows'] = $summaryRows;
        $data['clusterRows'] = [];
        $data['clusterReviewPicMap'] = [];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Batch_Approval_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function tableData()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonDataTableResponse(0, 0, []);
            return;
        }

        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
            $this->jsonDataTableResponse(0, 0, []);
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->post('city')));
        $selectedStatus = strtoupper(trim((string) $this->input->post('status')));
        $tab = strtolower(trim((string) $this->input->post('tab')));
        if (!in_array($tab, ['all', 'ny_drm'], true)) {
            $tab = 'all';
        }

        $searchPayload = $this->input->post('search');
        $searchValue = is_array($searchPayload) ? trim((string) ($searchPayload['value'] ?? '')) : '';
        $orderPayload = $this->input->post('order');
        $order = [];
        if (is_array($orderPayload) && isset($orderPayload[0]) && is_array($orderPayload[0])) {
            $order = [
                'column' => $orderPayload[0]['column'] ?? null,
                'dir' => $orderPayload[0]['dir'] ?? 'asc',
            ];
        }
        $start = max(0, (int) $this->input->post('start'));
        $length = (int) $this->input->post('length');
        if ($length <= 0) {
            $length = 10;
        }

        try {
            $rows = $this->MBatch_Approval_MyRep->getBatchRows($selectedCity, '');
            if ($selectedStatus !== '') {
                $rows = array_values(array_filter($rows, static function ($row) use ($selectedStatus) {
                    return strtoupper((string) ($row['display_staging_status'] ?? $row['staging_status'] ?? '')) === $selectedStatus;
                }));
            }
            if ($tab === 'ny_drm') {
                $rows = array_values(array_filter($rows, function ($row) {
                    return $this->isNyDrmBatchRow($row);
                }));
            }

            $recordsTotal = count($rows);
            if ($searchValue !== '') {
                $needle = strtoupper($searchValue);
                $rows = array_values(array_filter($rows, function ($row) use ($needle) {
                    $haystack = implode(' ', [
                        $row['cluster_name'] ?? '',
                        $row['cluster_code'] ?? '',
                        $row['regional_name'] ?? '',
                        $row['city_name'] ?? '',
                        $row['status_current'] ?? '',
                        $row['display_staging_status'] ?? '',
                        $this->getIndonesianStagingLabel((string) ($row['display_staging_status'] ?? $row['staging_status'] ?? '')),
                    ]);

                    return strpos(strtoupper($haystack), $needle) !== false;
                }));
            }

            $recordsFiltered = count($rows);
            $rows = $this->sortBatchApprovalTableRows($rows, $order);
            $totals = $this->calculateBatchApprovalTableTotals($rows);
            $pageRows = array_slice($rows, $start, $length);
            $clusterReviewPicMap = $this->MBatch_Approval_MyRep->getBatchClusterReviewPicMap($pageRows);

            $data = [];
            $no = $start + 1;
            foreach ($pageRows as $row) {
                $data[] = $this->buildBatchApprovalTableRow($row, $no++, $clusterReviewPicMap);
            }

            $this->jsonDataTableResponse($recordsTotal, $recordsFiltered, $data, ['totals' => $totals]);
        } catch (\Throwable $e) {
            log_message('error', 'Batch Approval tableData failed: ' . $e->getMessage());
            $this->jsonDataTableResponse(0, 0, []);
        }
    }

    public function downloadReport()
    {
        if (empty($this->session->userdata('id_user'))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Session habis. Silakan login ulang.');
                return;
            }
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
            'Display Status',
            'Status Flow',
            'Pre Zeyn Approved',
            'Pre Zeyn Required',
            'Pre Zeyn Finance Approved',
            'Pre Zeyn Finance Required',
            'Post Zeyn Approved',
            'Post Zeyn Required',
            'Post Zeyn Finance Approved',
            'Post Zeyn Finance Required',
            'Astri Final Approved',
            'Astri Final Required',
            'Nomor Batch Astri',
            'Tanggal Batch Approval Astri',
            'Tanggal Pengajuan Finance',
            'Tanggal Release Finance',
            'Nomor PO Donasi',
            'Tanggal PO Donasi',
            'Nilai PO Donasi',
            'Status PO Donasi',
            'Nomor Invoice Donasi',
            'Tanggal Invoice Donasi',
            'Nilai Invoice Donasi',
            'Status Invoice Donasi',
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
                $this->getIndonesianStagingLabel((string) ($row['staging_status'] ?? '')),
                $this->getIndonesianStagingLabel((string) ($row['display_staging_status'] ?? $row['staging_status'] ?? '')),
                (string) ($row['status_current'] ?? ''),
                (string) (int) ($row['pre_zeyn_doc_approved'] ?? 0),
                (string) (int) ($row['pre_zeyn_doc_total'] ?? 0),
                (string) (int) ($row['pre_zeyn_finance_approved'] ?? 0),
                (string) (int) ($row['pre_zeyn_finance_required'] ?? 0),
                (string) (int) ($row['post_zeyn_doc_approved'] ?? 0),
                (string) (int) ($row['post_zeyn_doc_total'] ?? 0),
                (string) (int) ($row['post_zeyn_finance_approved'] ?? 0),
                (string) (int) ($row['post_zeyn_finance_required'] ?? 0),
                (string) (int) ($row['astri_final_approved'] ?? 0),
                (string) (int) ($row['astri_final_total'] ?? 0),
                (string) ($row['astri_batch_number'] ?? ''),
                (string) ($row['astri_batch_approved_at'] ?? ''),
                (string) ($row['finance_submitted_at'] ?? ''),
                (string) ($row['released_at'] ?? ''),
                (string) ($row['po_donasi_number'] ?? ''),
                (string) ($row['po_donasi_date'] ?? ''),
                (string) ($row['po_donasi_value'] ?? ''),
                (string) ($row['po_donasi_status'] ?? ''),
                (string) ($row['invoice_donasi_number'] ?? ''),
                (string) ($row['invoice_donasi_date'] ?? ''),
                (string) ($row['invoice_donasi_value'] ?? ''),
                (string) ($row['invoice_donasi_status'] ?? ''),
                (string) ($row['remark_batch_approval'] ?? ''),
            ]);
        }

        fclose($output);
        exit;
    }

    public function downloadStageSummaryReport()
    {
        if (empty($this->session->userdata('id_user'))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Session habis. Silakan login ulang.');
                return;
            }
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
        $summary = [];
        foreach ($rows as $row) {
            if ((int) ($row['id_batch_approval'] ?? 0) <= 0) {
                continue;
            }
            $status = strtoupper(trim((string) ($row['display_staging_status'] ?? $row['staging_status'] ?? 'DRAFT')));
            if ($status === '') {
                $status = 'DRAFT';
            }
            if (!isset($summary[$status])) {
                $summary[$status] = [
                    'status' => $status,
                    'count' => 0,
                    'hp' => 0,
                    'nominal_pengajuan' => 0,
                    'nominal_release' => 0,
                ];
            }
            $summary[$status]['count']++;
            $summary[$status]['hp'] += (float) ($row['hp_donasi'] ?? 0);
            $summary[$status]['nominal_pengajuan'] += (float) ($row['nominal_pengajuan_area'] ?? 0);
            $summary[$status]['nominal_release'] += (float) ($row['nominal_release_finance'] ?? 0);
        }

        ksort($summary);

        $filename = 'summary_staging_donasi_batch_approval_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Staging Status',
            'Jumlah Cluster',
            'Total HP Donasi',
            'Total Nominal Pengajuan',
            'Total Nominal Release',
        ]);

        foreach ($summary as $row) {
            fputcsv($output, [
                $this->getIndonesianStagingLabel((string) $row['status']),
                (string) (int) $row['count'],
                (string) (float) $row['hp'],
                (string) (float) $row['nominal_pengajuan'],
                (string) (float) $row['nominal_release'],
            ]);
        }

        fclose($output);
        exit;
    }

    public function detail($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Session habis. Silakan login ulang.');
                return;
            }
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
        $data['canDonationInternalApprovalAction'] = $this->canDonationInternalApprovalAction();
        $data['canSubmitDonationFinanceRequest'] = $this->canSubmitDonationFinanceRequest();
        $data['canEditBatchApproval'] = $this->canEditBatchApprovalDetail($cluster);
        $data['canReplaceDonationFile'] = $this->isSitacHoUser();
        $data['canFinanceApprovalAction'] = $this->isFinanceHoUser();
        $data['batchDocument'] = $batchFile;
        $data['batchDocumentLogs'] = !empty($batchFile['id_doc_file'])
            ? $this->MBatch_Approval_MyRep->getBatchFileLogs((int) $batchFile['id_doc_file'])
            : [];
        $data['preZeynDocumentRows'] = $data['docReady']
            ? $this->MBatch_Approval_MyRep->getDonationDocumentRows($clusterId, 'PRE_ZEYN')
            : [];
        $data['postZeynDocumentRows'] = $data['docReady']
            ? $this->MBatch_Approval_MyRep->getDonationDocumentRows($clusterId, 'POST_ZEYN')
            : [];
        $data['donationDocumentSummary'] = $data['docReady']
            ? $this->MBatch_Approval_MyRep->getDonationDocumentSummary($clusterId)
            : [];
        $didSyncDonationStage = false;
        if ($data['docReady']) {
            $didSyncDonationStage = $this->syncDonationApprovalStage($clusterId, 'PRE_ZEYN') || $didSyncDonationStage;
            $didSyncDonationStage = $this->syncDonationApprovalStage($clusterId, 'POST_ZEYN') || $didSyncDonationStage;
            $didSyncDonationStage = $this->syncDonationFinanceApprovalStage($clusterId, 'PRE_ZEYN') || $didSyncDonationStage;
            $didSyncDonationStage = $this->syncDonationFinanceApprovalStage($clusterId, 'POST_ZEYN') || $didSyncDonationStage;
        }
        if ($didSyncDonationStage) {
            $cluster = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
            $data['cluster'] = $cluster;
            $data['donationDocumentSummary'] = $this->MBatch_Approval_MyRep->getDonationDocumentSummary($clusterId);
        }
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

    public function printChecklistPengajuan($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MBatch_Approval_MyRep->batchTablesReady() || !$this->MBatch_Approval_MyRep->batchDocumentTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel Batch Approval atau dokumen belum tersedia.');
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

        $documentRows = $this->MBatch_Approval_MyRep->getDonationDocumentRows($clusterId, 'PRE_ZEYN');
        $clusterName = trim((string) ($cluster['cluster_name'] ?? ''));
        $data = [
            'title' => 'Checklist pengajuan donasi' . ($clusterName !== '' ? ' - ' . $clusterName : ''),
            'cluster' => $cluster,
            'documentRows' => $documentRows,
            'printStatus' => $this->resolveDonationChecklistPrintStatus($documentRows),
            'signatures' => $this->buildDonationChecklistSignatures($documentRows),
            'printedAt' => date('Y-m-d H:i:s'),
            'printedBy' => (string) ($this->session->userdata('nama_user') ?: $this->session->userdata('username_user') ?: '-'),
        ];

        $this->load->view('Batch_Approval_MyRep/print_checklist_pengajuan', $data);
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
        $nominalNegoEmr = $nominalPengajuanArea;
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
        $stagingStatus = 'BATCH_APPROVED';
        $astriBatchNumber = trim((string) $this->input->post('astri_batch_number'));
        $astriBatchApprovedAt = $this->normalizeDateTimeInput($this->input->post('astri_batch_approved_at')) ?: date('Y-m-d H:i:s');
        $remark = trim((string) $this->input->post('remark_batch_approval'));
        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;
        $pics = $this->collectPicsFromPost();

        if ($clusterId <= 0 || $hpDonasi <= 0 || $nominalPengajuanArea <= 0 || $recipientName === '' || $bankName === '' || $bankAccountNumber === '') {
            $this->session->set_flashdata('error', 'Cluster, HP donasi, nominal area, data penerima, dan data bank wajib diisi.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        if ($astriBatchNumber === '') {
            $this->session->set_flashdata('error', 'Nomor batch approval Astri wajib diisi.');
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
            'astri_batch_number' => $astriBatchNumber,
            'staging_status' => $stagingStatus,
            'submitted_to_ho_at' => date('Y-m-d H:i:s'),
            'astri_initial_submitted_at' => date('Y-m-d H:i:s'),
            'astri_batch_approved_at' => $astriBatchApprovedAt,
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
        redirect('Batch_Approval_MyRep/detail/' . $clusterId);
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
        $existing = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        if (!$this->canEditBatchApprovalDetail($existing)) {
            $this->session->set_flashdata('error', 'Edit Batch Approval hanya tersedia untuk Super Admin, Admin, SITAC HO, Admin Area, dan SND Area.');
            redirect($this->resolveBatchRedirectPath($clusterId));
            return;
        }

        $hpDonasi = (int) $this->normalizeNumber($this->input->post('hp_donasi'));
        $nominalPengajuanArea = $this->normalizeNumber($this->input->post('nominal_pengajuan_area'));
        $nominalNegoEmr = $nominalPengajuanArea;
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
        $astriBatchApprovedAt = $this->normalizeDateTimeInput($this->input->post('astri_batch_approved_at'));
        $remark = trim((string) $this->input->post('remark_batch_approval'));
        $pics = $this->collectPicsFromPost();
        $stagingStatus = $this->normalizeStagingStatus($stagingStatus, false);
        $batchApprovalRequiredStages = [
            'DRAFT',
            'WAITING_INPUT',
            'WAITING INPUT',
            'WAITING_BATCH_APPROVAL',
        ];
        $isBatchApprovalRequired = !in_array($stagingStatus, $batchApprovalRequiredStages, true);
        $isBaseDataRequired = !in_array($stagingStatus, ['DRAFT', 'WAITING_INPUT', 'WAITING INPUT', 'WAITING_BATCH_APPROVAL'], true);

        if ($clusterId <= 0 || $batchId <= 0) {
            $this->session->set_flashdata('error', 'Data update Batch Approval belum lengkap.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        if ($isBaseDataRequired && ($hpDonasi <= 0 || $nominalPengajuanArea <= 0 || $recipientName === '' || $bankName === '' || $bankAccountNumber === '')) {
            $this->session->set_flashdata('error', 'Data update Batch Approval belum lengkap.');
            redirect($this->resolveBatchRedirectPath($clusterId));
            return;
        }

        if ($isBatchApprovalRequired && $astriBatchNumber === '') {
            $this->session->set_flashdata('error', 'Nomor batch approval Astri wajib diisi.');
            redirect($this->resolveBatchRedirectPath($clusterId));
            return;
        }

        if ($isBatchApprovalRequired && empty($astriBatchApprovedAt)) {
            $this->session->set_flashdata('error', 'Tanggal batch approval wajib diisi.');
            redirect($this->resolveBatchRedirectPath($clusterId));
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
        $gateError = $this->validateDonationStageGate($clusterId, $stagingStatus);
        if ($gateError !== '') {
            $redirectPath = $this->resolveBatchRedirectPath($clusterId);
            $this->session->set_flashdata('error', $gateError);
            redirect($redirectPath);
            return;
        }
        $nominalPerHomepass = $hpDonasi > 0 ? round($nominalPengajuanArea / $hpDonasi, 2) : 0;
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
            'astri_batch_number' => $astriBatchNumber,
            'astri_batch_approved_at' => $astriBatchApprovedAt,
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

        if (!$this->canUploadDonationDocument()) {
            $this->handleUploadError('Upload dokumen hanya tersedia untuk Admin Area dan Super Admin.', $redirectPath);
            return;
        }

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
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png|rar|zip',
                'max_size' => 20480,
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

        if (!$this->MBatch_Approval_MyRep->areDonationRequiredDocumentsApproved($clusterId, 'PRE_ZEYN')) {
            $this->session->set_flashdata('error', 'Finance tidak boleh release donasi karena 9 dokumen pra-finance belum full approved.');
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
            'allowed_types' => 'jpg|jpeg|png',
            'max_size' => 20480,
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

        $this->session->set_flashdata('success', 'Bukti transfer berhasil diupload dan status diubah ke Donasi Dibayarkan.');
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

        $batch = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        if ($clusterId <= 0 || $batchId <= 0 || empty($batch) || (int) ($batch['id_batch_approval'] ?? 0) !== $batchId) {
            $this->session->set_flashdata('error', 'Data Batch Approval tidak ditemukan.');
            redirect($redirectPath);
            return;
        }

        $storedStage = strtoupper(trim((string) ($batch['staging_status'] ?? 'DRAFT')));
        $currentStage = strtoupper(trim((string) ($batch['display_staging_status'] ?? $storedStage)));
        if ($currentStage === '') {
            $currentStage = $storedStage !== '' ? $storedStage : 'DRAFT';
        }
        $targetStage = strtoupper(trim((string) $this->input->post('target_stage')));
        $isAreaAllowedInitialDecision = $currentStage === 'WAITING_BATCH_APPROVAL'
            && in_array($targetStage, ['BATCH_APPROVED', 'HOLD', 'REJECTED'], true);
        $isFinanceRequestSubmission = $targetStage === 'WAITING_FINANCE_RELEASE';

        if ($isFinanceRequestSubmission && !$this->canSubmitDonationFinanceRequest()) {
            $this->session->set_flashdata('error', 'Hanya SITAC HO dan Admin Area yang bisa memproses pengajuan saku.');
            redirect($redirectPath);
            return;
        }

        if (!$this->isApprover() && !$isAreaAllowedInitialDecision && !$isFinanceRequestSubmission) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses mengubah staging Batch Approval.');
            redirect($redirectPath);
            return;
        }
        $userId = (int) $this->session->userdata('id_user');
        $batchPayload = ['updated_by' => $userId];
        $successMessage = 'Staging Batch Approval berhasil diperbarui.';

        if ($currentStage === 'WAITING_BATCH_APPROVAL' && in_array($targetStage, ['BATCH_APPROVED', 'HOLD', 'REJECTED'], true)) {
            if ($targetStage === 'BATCH_APPROVED') {
                $astriBatchNumber = trim((string) $this->input->post('astri_batch_number'));
                $approvedAt = $this->normalizeDateTimeInput($this->input->post('astri_batch_approved_at')) ?: date('Y-m-d H:i:s');
                $nominalNegoEmr = $this->normalizeNullableNumber($this->input->post('nominal_nego_emr'));
                if ($astriBatchNumber === '') {
                    $this->session->set_flashdata('error', 'Nomor batch approval Astri wajib diisi.');
                    redirect($redirectPath);
                    return;
                }
                if ($nominalNegoEmr === null || $nominalNegoEmr <= 0) {
                    $this->session->set_flashdata('error', 'Nominal Approval EMR wajib diisi.');
                    redirect($redirectPath);
                    return;
                }
                $batchPayload['staging_status'] = 'BATCH_APPROVED';
                $batchPayload['astri_batch_number'] = $astriBatchNumber;
                $batchPayload['astri_batch_approved_at'] = $approvedAt;
                $batchPayload['nominal_nego_emr'] = $nominalNegoEmr;
                $successMessage = 'Batch approval Astri berhasil dicatat.';
            } elseif ($targetStage === 'HOLD') {
                $batchPayload['staging_status'] = 'HOLD';
                $batchPayload['hold_at'] = date('Y-m-d H:i:s');
                $batchPayload['hold_remark'] = trim((string) $this->input->post('remark'));
                $successMessage = 'Pengajuan donasi ditandai Ditahan.';
            } else {
                $remark = trim((string) $this->input->post('remark'));
                if ($remark === '') {
                    $this->session->set_flashdata('error', 'Remark reject wajib diisi.');
                    redirect($redirectPath);
                    return;
                }
                $batchPayload['staging_status'] = 'REJECTED';
                $batchPayload['rejected_at'] = date('Y-m-d H:i:s');
                $batchPayload['rejected_remark'] = $remark;
                $successMessage = 'Pengajuan donasi ditandai Ditolak.';
            }
        } elseif (in_array($currentStage, ['BATCH_APPROVED', 'PRE_ZEYN_DOC_APPROVED', 'PRE_ZEYN_FINANCE_ON_REVIEW', 'PRE_ZEYN_FINANCE_APPROVED'], true) && $targetStage === 'WAITING_FINANCE_RELEASE') {
            if (!$this->MBatch_Approval_MyRep->areDonationRequiredDocumentsFinanceApproved($clusterId, 'PRE_ZEYN')) {
                $this->session->set_flashdata('error', 'Finance belum bisa diajukan karena 9 dokumen pra-finance belum full approved Finance.');
                redirect($redirectPath);
                return;
            }
            if ((float) ($batch['nominal_nego_emr'] ?? 0) <= 0) {
                $this->session->set_flashdata('error', 'Nominal Approval EMR wajib diisi sebelum ajukan ke Finance.');
                redirect($redirectPath);
                return;
            }

            $submittedAt = date('Y-m-d H:i:s');
            $batchPayload['staging_status'] = 'WAITING_FINANCE_RELEASE';
            $batchPayload['pre_zeyn_doc_approved_at'] = $submittedAt;
            $batchPayload['finance_submitted_at'] = $submittedAt;
            $batchPayload['submitted_to_finance_at'] = $submittedAt;
            $successMessage = 'Proses pengajuan saku berhasil dicatat.';
        } elseif ($currentStage === 'WAITING_FINANCE_RELEASE' && $targetStage === 'RELEASED') {
            if (!$this->MBatch_Approval_MyRep->areDonationRequiredDocumentsFinanceApproved($clusterId, 'PRE_ZEYN')) {
                $this->session->set_flashdata('error', 'Finance tidak boleh release donasi karena dokumen pra-finance belum full approved Finance.');
                redirect($redirectPath);
                return;
            }
            $releasedAt = $this->normalizeDateTimeInput($this->input->post('released_at'));
            $nominalReleaseFinance = $this->normalizeNullableNumber($this->input->post('nominal_release_finance'));

            if ($releasedAt === null || $nominalReleaseFinance === null || empty($_FILES['transfer_proof']['name'])) {
                $this->session->set_flashdata('error', 'Tanggal pencairan, nominal pencairan, dan gambar transfer wajib diisi.');
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
                'allowed_types' => 'jpg|jpeg|png',
                'max_size' => 20480,
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
            $financeSubmittedAt = !empty($batch['finance_submitted_at'] ?? '') ? $batch['finance_submitted_at'] : date('Y-m-d H:i:s');
            $batchPayload['staging_status'] = 'RELEASED';
            $batchPayload['pre_zeyn_doc_approved_at'] = $this->resolveStageTimestamp($batch['pre_zeyn_doc_approved_at'] ?? null, true);
            $batchPayload['finance_submitted_at'] = $financeSubmittedAt;
            $batchPayload['submitted_to_finance_at'] = !empty($batch['submitted_to_finance_at'] ?? '') ? $batch['submitted_to_finance_at'] : $financeSubmittedAt;
            $batchPayload['released_at'] = $releasedAt;
            $batchPayload['nominal_release_finance'] = $nominalReleaseFinance;
            $batchPayload['transfer_proof_file_name'] = $fileData['file_name'];
            $batchPayload['transfer_proof_file_path'] = 'uploads/myrep_batch_transfer/' . $fileData['file_name'];
            $successMessage = 'Staging berhasil diubah ke Donasi Dibayarkan.';
        } elseif (in_array($currentStage, ['WAITING_ASTRI_SUBMISSION', 'ASTRI_ON_REVIEW'], true) && $targetStage === 'ASTRI_APPROVED') {
            if (!$this->MBatch_Approval_MyRep->areAllDonationDocumentsAstriApproved($clusterId)) {
                $this->session->set_flashdata('error', 'Astri belum bisa completed karena masih ada dokumen yang belum approved Astri.');
                redirect($redirectPath);
                return;
            }
            $batchPayload['staging_status'] = 'ASTRI_APPROVED';
            $batchPayload['final_astri_approved_at'] = date('Y-m-d H:i:s');
            $successMessage = 'Semua dokumen Astri sudah approved.';
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

        if ($result && $targetStage === 'WAITING_FINANCE_RELEASE' && in_array($currentStage, ['BATCH_APPROVED', 'PRE_ZEYN_DOC_APPROVED', 'PRE_ZEYN_FINANCE_ON_REVIEW', 'PRE_ZEYN_FINANCE_APPROVED'], true)) {
            $clusterDetail = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
            $this->sendBatchNotification('propose_donation', $clusterDetail, 'PROPOSE DONATION - EMR');
        }

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

        if ($result) {
            $this->myrepRejectEmail->enqueueReject('Batch_Approval_MyRep', $fileId);
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen RAR berhasil di-reject.' : 'Gagal reject dokumen RAR.');
        redirect($redirectPath);
    }

    public function uploadDonationDocument()
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
        $docItemId = (int) $this->input->post('id_doc_item');
        $groupKey = strtoupper(trim((string) $this->input->post('group_key')));
        $redirectPath = $this->resolveBatchRedirectPath($clusterId);

        if (!$this->MBatch_Approval_MyRep->batchDocumentTablesReady()) {
            $this->handleUploadError('Tabel dokumen Batch Approval belum tersedia.', $redirectPath);
            return;
        }

        $context = $this->MBatch_Approval_MyRep->getDonationDocumentDetail($clusterId, $docItemId, $groupKey);
        if (empty($context)) {
            $this->handleUploadError('Item dokumen donasi tidak ditemukan.', $redirectPath);
            return;
        }

        if ($this->isPostDonationActionLocked($clusterId, (string) ($context['group_label'] ?? $groupKey))) {
            $this->handleUploadError('Dokumen tahap 2 baru bisa diupload setelah pencairan dana dicatat.', $redirectPath);
            return;
        }

        $rawStatus = strtoupper(trim((string) ($context['status_file'] ?? '')));
        $astriStatus = strtoupper(trim((string) ($context['astri_status'] ?? 'NY')));
        $financeStatus = strtoupper(trim((string) ($context['finance_status'] ?? 'NY')));
        $isReplaceApprovedFile = (int) $this->input->post('replace_file') === 1;
        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;
        $canStandardUpload = $this->canUploadDonationDocument();
        $canUploadByStatus = $canStandardUpload && in_array($rawStatus, ['', 'REJECTED'], true);
        $canUploadAstriRejectedRevision = $canStandardUpload && $rawStatus === 'APPROVED' && $astriStatus === 'REJECTED';
        $canUploadFinanceRejectedRevision = $canStandardUpload && $rawStatus === 'APPROVED' && $financeStatus === 'REJECTED';
        $canReplaceApprovedFile = $rawStatus === 'APPROVED' && $isReplaceApprovedFile && $this->isSitacHoUser();
        if (!$canUploadByStatus && !$canUploadAstriRejectedRevision && !$canUploadFinanceRejectedRevision && !$canReplaceApprovedFile) {
            $message = !$canStandardUpload && !$canReplaceApprovedFile
                ? 'Upload dokumen hanya tersedia untuk Admin Area, SITAC HO, dan Super Admin.'
                : ($rawStatus === 'APPROVED'
                ? 'Dokumen approved hanya bisa di-replace oleh akun SITAC HO, kecuali dokumen rejected Astri/Finance untuk revisi area.'
                : 'Dokumen hanya bisa diupload saat status belum upload atau rejected.');
            $this->handleUploadError($message, $redirectPath);
            return;
        }

        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->handleUploadError('File dokumen wajib dipilih.', $redirectPath);
            return;
        }

        $docName = (string) ($context['doc_name'] ?? 'DONASI');
        if ($isNoDocumentRequired && strtoupper(trim($docName)) !== 'FORM FREE WIFI & KTP') {
            $this->handleUploadError('Opsi Tidak dibutuhkan dokumen hanya tersedia untuk Form Free Wifi & KTP.', $redirectPath);
            return;
        }

        $savedFileName = '';
        $savedFilePath = '';
        if (!$isNoDocumentRequired) {
            $allowedTypes = $this->resolveDonationAllowedTypes($docName, (string) ($context['group_label'] ?? ''));

            $uploadDir = './uploads/myrep_batch_donation/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName);
            $fileName = 'DONASI_' . $clusterId . '_' . $docItemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;
            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => $allowedTypes,
                'max_size' => 20480,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $this->handleUploadError(strip_tags($this->upload->display_errors()), $redirectPath);
                return;
            }

            $fileData = $this->upload->data();
            $savedFileName = $fileData['file_name'];
            $savedFilePath = 'uploads/myrep_batch_donation/' . $fileData['file_name'];
        }

        $fileId = $this->MBatch_Approval_MyRep->saveDonationFileUpload($clusterId, $docItemId, [
            'file_name' => $savedFileName,
            'file_path' => $savedFilePath,
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
            'group_key' => $groupKey,
            'preserve_finance_approval' => $canUploadAstriRejectedRevision && $financeStatus === 'APPROVED',
            'preserve_astri_rejected' => $canUploadAstriRejectedRevision,
        ]);

        $message = $fileId > 0 ? 'Dokumen donasi berhasil diupload.' : 'Dokumen donasi gagal disimpan.';
        if ($fileId > 0) {
            $this->queueDonationUploadNotification($clusterId, (string) ($context['group_label'] ?? $groupKey), [(string) ($context['doc_name'] ?? 'Dokumen Donasi')]);
            $this->syncDonationUploadReviewStage($clusterId, (string) ($context['group_label'] ?? ''));
            $this->handleUploadSuccess($message, $redirectPath);
        } else {
            $this->handleUploadError($message, $redirectPath);
        }
    }

    public function uploadBulkDonationDocuments()
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
        $groupKey = strtoupper(trim((string) $this->input->post('group_key')));
        $redirectPath = $this->resolveBatchRedirectPath($clusterId);
        if (!$this->canUploadDonationDocument()) {
            $this->handleUploadError('Upload dokumen hanya tersedia untuk Admin Area, SITAC HO, dan Super Admin.', $redirectPath);
            return;
        }

        if (!$this->MBatch_Approval_MyRep->batchDocumentTablesReady()) {
            $this->handleUploadError('Tabel dokumen Batch Approval belum tersedia.', $redirectPath);
            return;
        }

        if ($this->isPostDonationActionLocked($clusterId, $groupKey)) {
            $this->handleUploadError('Dokumen tahap 2 baru bisa diupload setelah pencairan dana dicatat.', $redirectPath);
            return;
        }

        $rows = $this->MBatch_Approval_MyRep->getDonationDocumentRows($clusterId, $groupKey);
        $uploadDir = './uploads/myrep_batch_donation/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploaded = 0;
        $errors = [];
        $uploadedDocNames = [];
        $uploadedGroupLabel = '';
        foreach ($rows as $row) {
            $docItemId = (int) ($row['id_doc_item'] ?? 0);
            if ($this->isPostDonationActionLocked($clusterId, (string) ($row['group_label'] ?? $groupKey))) {
                $errors[] = (string) ($row['doc_name'] ?? 'Dokumen tahap 2') . ': baru bisa diupload setelah pencairan dana dicatat.';
                continue;
            }

            $rawStatus = strtoupper(trim((string) ($row['status_file'] ?? '')));
            $astriStatus = strtoupper(trim((string) ($row['astri_status'] ?? 'NY')));
            $financeStatus = strtoupper(trim((string) ($row['finance_status'] ?? 'NY')));
            $canUploadAstriRejectedRevision = $rawStatus === 'APPROVED' && $astriStatus === 'REJECTED';
            $canUploadFinanceRejectedRevision = $rawStatus === 'APPROVED' && $financeStatus === 'REJECTED';
            $fieldName = 'bulk_file_' . $docItemId;
            $isNoDocumentRequired = (int) $this->input->post('bulk_not_required_' . $docItemId) === 1;
            if ($docItemId <= 0 || (!in_array($rawStatus, ['', 'REJECTED'], true) && !$canUploadAstriRejectedRevision && !$canUploadFinanceRejectedRevision)) {
                continue;
            }

            $docName = (string) ($row['doc_name'] ?? 'DONASI');
            if ($isNoDocumentRequired && strtoupper(trim($docName)) !== 'FORM FREE WIFI & KTP') {
                $errors[] = $docName . ': opsi Tidak dibutuhkan dokumen hanya tersedia untuk Form Free Wifi & KTP.';
                continue;
            }

            if (!$isNoDocumentRequired && empty($_FILES[$fieldName]['name'])) {
                continue;
            }

            $savedFileName = '';
            $savedFilePath = '';
            if (!$isNoDocumentRequired) {
                $allowedTypes = $this->resolveDonationAllowedTypes($docName, (string) ($row['group_label'] ?? ''));
                $extension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
                $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName);
                $fileName = 'DONASI_' . $clusterId . '_' . $docItemId . '_' . $safeDocName . '_' . date('YmdHis') . '_' . $uploaded . '.' . $extension;

                $this->upload->initialize([
                    'upload_path' => $uploadDir,
                    'allowed_types' => $allowedTypes,
                    'max_size' => 20480,
                    'file_name' => $fileName,
                    'overwrite' => true,
                ]);
                if (!$this->upload->do_upload($fieldName)) {
                    $errors[] = $docName . ': ' . strip_tags($this->upload->display_errors());
                    continue;
                }

                $fileData = $this->upload->data();
                $savedFileName = $fileData['file_name'];
                $savedFilePath = 'uploads/myrep_batch_donation/' . $fileData['file_name'];
            }

            $fileId = $this->MBatch_Approval_MyRep->saveDonationFileUpload($clusterId, $docItemId, [
                'file_name' => $savedFileName,
                'file_path' => $savedFilePath,
                'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
                'status_file' => 'UPLOADED',
                'remark' => trim((string) $this->input->post('bulk_remark_' . $docItemId)),
                'uploaded_by' => (int) $this->session->userdata('id_user'),
                'group_key' => $groupKey,
                'preserve_finance_approval' => $canUploadAstriRejectedRevision && $financeStatus === 'APPROVED',
                'preserve_astri_rejected' => $canUploadAstriRejectedRevision,
            ]);
            if ($fileId > 0) {
                $uploaded++;
                $uploadedDocNames[] = $docName;
                if ($uploadedGroupLabel === '') {
                    $uploadedGroupLabel = (string) ($row['group_label'] ?? $groupKey);
                }
            } else {
                $errors[] = $docName . ': gagal disimpan.';
            }
        }

        if ($uploaded > 0) {
            $this->queueDonationUploadNotification($clusterId, $uploadedGroupLabel !== '' ? $uploadedGroupLabel : $groupKey, $uploadedDocNames);
        }

        if ($uploaded > 0 && empty($errors)) {
            $this->syncDonationUploadReviewStage($clusterId, $groupKey);
            $this->handleUploadSuccess($uploaded . ' dokumen donasi berhasil diupload.', $redirectPath);
        } elseif ($uploaded > 0) {
            $this->syncDonationUploadReviewStage($clusterId, $groupKey);
            $this->handleUploadSuccess($uploaded . ' dokumen berhasil diupload. Sebagian gagal: ' . implode(' | ', $errors), $redirectPath);
        } else {
            $this->handleUploadError(empty($errors) ? 'Tidak ada file yang dipilih untuk bulk upload.' : implode(' | ', $errors), $redirectPath);
        }
    }

    public function approveDonationDocument()
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
        if (!$this->canDonationInternalApprovalAction()) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Anda tidak memiliki akses approve dokumen donasi.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen donasi.');
            redirect($redirectPath);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $context = $this->MBatch_Approval_MyRep->getDonationFileContext($fileId);
        if ($this->isPostDonationActionLocked($clusterId, (string) ($context['group_label'] ?? ''))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Dokumen tahap 2 baru bisa direview setelah pencairan dana dicatat.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Dokumen tahap 2 baru bisa direview setelah pencairan dana dicatat.');
            redirect($redirectPath);
            return;
        }

        $result = $fileId > 0 && $this->MBatch_Approval_MyRep->updateBatchFileStatus($fileId, [
            'status_file' => 'APPROVED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);
        if ($result) {
            $this->syncDonationApprovalStage($clusterId, (string) ($context['group_label'] ?? ''));
        }

        if ($this->isAjaxRequest()) {
            $this->jsonResponse((bool) $result, $result ? 'Dokumen donasi berhasil di-approve.' : 'Gagal approve dokumen donasi.', base_url($redirectPath));
            return;
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen donasi berhasil di-approve.' : 'Gagal approve dokumen donasi.');
        redirect($redirectPath);
    }

    public function rejectDonationDocument()
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
        if (!$this->canDonationInternalApprovalAction()) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Anda tidak memiliki akses reject dokumen donasi.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen donasi.');
            redirect($redirectPath);
            return;
        }

        $remark = trim((string) $this->input->post('remark'));
        if ($remark === '') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Alasan reject wajib diisi.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Alasan reject wajib diisi.');
            redirect($redirectPath);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $context = $this->MBatch_Approval_MyRep->getDonationFileContext($fileId);
        if ($this->isPostDonationActionLocked($clusterId, (string) ($context['group_label'] ?? ''))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Dokumen tahap 2 baru bisa direview setelah pencairan dana dicatat.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Dokumen tahap 2 baru bisa direview setelah pencairan dana dicatat.');
            redirect($redirectPath);
            return;
        }

        $result = $fileId > 0 && $this->MBatch_Approval_MyRep->updateBatchFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => $remark,
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);
        if ($result) {
            $this->myrepRejectEmail->enqueueReject('Batch_Approval_MyRep', $fileId);
            $groupLabel = strtoupper(trim((string) ($context['group_label'] ?? '')));
            if ($groupLabel === 'PRE ZEYN DOCUMENT') {
                $this->setDonationStageFromSystem($clusterId, 'BATCH_APPROVED');
            } elseif ($groupLabel === 'POST PAYMENT ZEYN DOCUMENT') {
                $this->setDonationStageFromSystem($clusterId, 'RELEASED');
            }
        }

        if ($this->isAjaxRequest()) {
            $this->jsonResponse((bool) $result, $result ? 'Dokumen donasi berhasil di-reject.' : 'Gagal reject dokumen donasi.', base_url($redirectPath));
            return;
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Dokumen donasi berhasil di-reject.' : 'Gagal reject dokumen donasi.');
        redirect($redirectPath);
    }

    public function approveAllDonationDocuments()
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
        $groupKey = strtoupper(trim((string) $this->input->post('group_key')));
        $redirectPath = $this->resolveBatchRedirectPath($clusterId);
        if (!$this->canDonationInternalApprovalAction()) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Anda tidak memiliki akses approve all dokumen donasi.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve all dokumen donasi.');
            redirect($redirectPath);
            return;
        }

        if ($this->isPostDonationActionLocked($clusterId, $groupKey)) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Dokumen tahap 2 baru bisa direview setelah pencairan dana dicatat.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Dokumen tahap 2 baru bisa direview setelah pencairan dana dicatat.');
            redirect($redirectPath);
            return;
        }

        $updated = $this->MBatch_Approval_MyRep->approveAllDonationDocuments($clusterId, $groupKey, (int) $this->session->userdata('id_user'), trim((string) $this->input->post('remark')));
        $this->syncDonationApprovalStage($clusterId, $groupKey);
        if ($this->isAjaxRequest()) {
            $this->jsonResponse(true, $updated . ' dokumen donasi berhasil di-approve.', base_url($redirectPath));
            return;
        }

        $this->session->set_flashdata('success', $updated . ' dokumen donasi berhasil di-approve.');
        redirect($redirectPath);
    }

    public function approveDonationFinanceDocument()
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
        if (!$this->isFinanceHoUser()) {
            $this->handleDonationAjaxOrRedirect(false, 'Anda tidak memiliki akses approval Finance dokumen donasi.', $redirectPath);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $context = $this->MBatch_Approval_MyRep->getDonationFileContext($fileId);
        if ($this->isPostDonationActionLocked($clusterId, (string) ($context['group_label'] ?? ''))) {
            $this->handleDonationAjaxOrRedirect(false, 'Dokumen tahap 2 baru bisa direview Finance setelah pencairan dana dicatat.', $redirectPath);
            return;
        }

        $result = $fileId > 0 && $this->MBatch_Approval_MyRep->updateDonationFinanceStatus($fileId, 'APPROVED', (int) $this->session->userdata('id_user'), trim((string) $this->input->post('remark')));
        if ($result) {
            $this->syncDonationFinanceApprovalStage($clusterId, (string) ($context['group_label'] ?? ''));
        }

        $this->handleDonationAjaxOrRedirect((bool) $result, $result ? 'Dokumen donasi berhasil di-approve Finance.' : 'Gagal approve Finance dokumen donasi.', $redirectPath);
    }

    public function rejectDonationFinanceDocument()
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
        if (!$this->isFinanceHoUser()) {
            $this->handleDonationAjaxOrRedirect(false, 'Anda tidak memiliki akses reject Finance dokumen donasi.', $redirectPath);
            return;
        }

        $remark = trim((string) $this->input->post('remark'));
        if ($remark === '') {
            $this->handleDonationAjaxOrRedirect(false, 'Alasan reject Finance wajib diisi.', $redirectPath);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $context = $this->MBatch_Approval_MyRep->getDonationFileContext($fileId);
        if ($this->isPostDonationActionLocked($clusterId, (string) ($context['group_label'] ?? ''))) {
            $this->handleDonationAjaxOrRedirect(false, 'Dokumen tahap 2 baru bisa direview Finance setelah pencairan dana dicatat.', $redirectPath);
            return;
        }

        $result = $fileId > 0 && $this->MBatch_Approval_MyRep->updateDonationFinanceStatus($fileId, 'REJECTED', (int) $this->session->userdata('id_user'), $remark);
        if ($result) {
            $this->myrepRejectEmail->enqueueReject('Batch_Approval_MyRep', $fileId);
            $groupLabel = strtoupper(trim((string) ($context['group_label'] ?? '')));
            $this->setDonationStageFromSystem($clusterId, $groupLabel === 'POST PAYMENT ZEYN DOCUMENT' ? 'RELEASED' : 'BATCH_APPROVED');
            $clusterDetail = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
            $this->sendBatchNotification('document_revised', $clusterDetail, 'Reject Finance - ' . (string) ($context['doc_name'] ?? 'Dokumen Donasi'));
        }

        $this->handleDonationAjaxOrRedirect((bool) $result, $result ? 'Dokumen donasi berhasil di-reject Finance.' : 'Gagal reject Finance dokumen donasi.', $redirectPath);
    }

    public function approveAllDonationFinanceDocuments()
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
        $groupKey = strtoupper(trim((string) $this->input->post('group_key')));
        $redirectPath = $this->resolveBatchRedirectPath($clusterId);
        if (!$this->isFinanceHoUser()) {
            $this->handleDonationAjaxOrRedirect(false, 'Anda tidak memiliki akses approve all Finance dokumen donasi.', $redirectPath);
            return;
        }

        if ($this->isPostDonationActionLocked($clusterId, $groupKey)) {
            $this->handleDonationAjaxOrRedirect(false, 'Dokumen tahap 2 baru bisa direview Finance setelah pencairan dana dicatat.', $redirectPath);
            return;
        }

        $updated = $this->MBatch_Approval_MyRep->approveAllDonationFinanceDocuments($clusterId, $groupKey, (int) $this->session->userdata('id_user'), trim((string) $this->input->post('remark')));
        $this->syncDonationFinanceApprovalStage($clusterId, $groupKey);
        $this->handleDonationAjaxOrRedirect(true, $updated . ' dokumen donasi berhasil di-approve Finance.', $redirectPath);
    }

    public function updateDonationAstriStatus()
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
        if (!$this->canDonationInternalApprovalAction()) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Anda tidak memiliki akses update status Astri.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses update status Astri.');
            redirect($redirectPath);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $fileContext = $this->MBatch_Approval_MyRep->getDonationFileContext($fileId);
        if ($this->isPostDonationActionLocked($clusterId, (string) ($fileContext['group_label'] ?? ''))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Status Astri dokumen tahap 2 baru bisa diupdate setelah pencairan dana dicatat.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Status Astri dokumen tahap 2 baru bisa diupdate setelah pencairan dana dicatat.');
            redirect($redirectPath);
            return;
        }

        $astriStatusInput = strtoupper(trim((string) $this->input->post('astri_status')));
        $astriRemark = trim((string) $this->input->post('astri_remark'));
        $astriSubmittedDate = $this->normalizeDate($this->input->post('astri_submitted_date'));
        if ($astriStatusInput === 'REJECTED' && $astriRemark === '') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Remark Astri wajib diisi jika status dokumen rejected.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Remark Astri wajib diisi jika status dokumen rejected.');
            redirect($redirectPath);
            return;
        }
        if ($astriStatusInput !== 'NY' && empty($astriSubmittedDate)) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Tanggal submit Astri wajib diisi untuk status selain NY.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Tanggal submit Astri wajib diisi untuk status selain NY.');
            redirect($redirectPath);
            return;
        }

        $result = $fileId > 0 && $this->MBatch_Approval_MyRep->updateDonationAstriStatus($fileId, [
            'astri_status' => $astriStatusInput,
            'astri_submitted_date' => $astriSubmittedDate,
            'astri_approved_date' => $this->normalizeDate($this->input->post('astri_approved_date')),
            'astri_remark' => $astriRemark,
            'updated_by' => (int) $this->session->userdata('id_user'),
        ]);
        if ($result) {
            $groupLabel = strtoupper(trim((string) ($fileContext['group_label'] ?? '')));
            if (in_array($groupLabel, ['PRE ZEYN DOCUMENT', 'POST PAYMENT ZEYN DOCUMENT'], true) && $astriStatusInput === 'REJECTED') {
                $this->myrepRejectEmail->enqueueReject('Batch_Approval_MyRep', $fileId, [
                    'remark' => $astriRemark,
                    'rejecter_user_id' => (int) $this->session->userdata('id_user'),
                    'rejected_at' => date('Y-m-d H:i:s'),
                ]);
                $this->setDonationStageFromSystem($clusterId, 'ASTRI_ON_REVIEW', [
                    'final_astri_approved_at' => null,
                ]);
            } elseif ($groupLabel === 'POST PAYMENT ZEYN DOCUMENT' && in_array($astriStatusInput, ['ON REVIEW', 'APPROVED'], true)) {
                $batch = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
                $currentStage = strtoupper(trim((string) ($batch['staging_status'] ?? '')));
                if ($currentStage === 'WAITING_ASTRI_SUBMISSION') {
                    $this->setDonationStageFromSystem($clusterId, 'ASTRI_ON_REVIEW');
                }
            }
        }

        if ($this->isAjaxRequest()) {
            $this->jsonResponse((bool) $result, $result ? 'Status Astri dokumen berhasil diperbarui.' : 'Gagal update status Astri dokumen.', base_url($redirectPath));
            return;
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Status Astri dokumen berhasil diperbarui.' : 'Gagal update status Astri dokumen.');
        redirect($redirectPath);
    }

    public function bulkUpdateDonationAstriStatus()
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
        $groupKey = strtoupper(trim((string) $this->input->post('group_key')));
        $redirectPath = $this->resolveBatchRedirectPath($clusterId);
        if (!$this->canDonationInternalApprovalAction()) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Anda tidak memiliki akses update status Astri.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses update status Astri.');
            redirect($redirectPath);
            return;
        }

        $fileIds = array_values(array_unique(array_map('intval', (array) $this->input->post('id_doc_file'))));
        $astriSubmittedDates = (array) $this->input->post('astri_submitted_date');
        $astriApprovedDates = (array) $this->input->post('astri_approved_date');
        $astriStatuses = (array) $this->input->post('astri_status');
        $astriRemarks = (array) $this->input->post('astri_remark');
        if ($clusterId <= 0 || empty($fileIds)) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Data bulk Astri tidak valid.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Data bulk Astri tidak valid.');
            redirect($redirectPath);
            return;
        }

        if ($this->isPostDonationActionLocked($clusterId, $groupKey)) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Status Astri dokumen tahap 2 baru bisa diupdate setelah pencairan dana dicatat.', base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('error', 'Status Astri dokumen tahap 2 baru bisa diupdate setelah pencairan dana dicatat.');
            redirect($redirectPath);
            return;
        }

        $updatedCount = 0;
        $skippedMessages = [];
        foreach ($fileIds as $fileId) {
            $file = $this->MBatch_Approval_MyRep->getDonationFileContext($fileId);
            $docName = !empty($file['doc_name']) ? trim((string) $file['doc_name']) : ('Dokumen #' . $fileId);
            if (empty($file) || (int) ($file['id_myrep_cluster'] ?? 0) !== $clusterId) {
                $skippedMessages[] = $docName . ' tidak ditemukan.';
                continue;
            }
            if ($groupKey !== '') {
                $expectedGroupLabel = $groupKey === 'POST_ZEYN' ? 'POST PAYMENT ZEYN DOCUMENT' : 'PRE ZEYN DOCUMENT';
                if (strtoupper(trim((string) ($file['group_label'] ?? ''))) !== $expectedGroupLabel) {
                    $skippedMessages[] = $docName . ' bukan grup yang dipilih.';
                    continue;
                }
            }
            if (strtoupper(trim((string) ($file['status_file'] ?? ''))) !== 'APPROVED') {
                $skippedMessages[] = $docName . ' belum APPROVED internal.';
                continue;
            }

            $astriStatus = strtoupper(trim((string) ($astriStatuses[$fileId] ?? 'NY')));
            $astriStatus = $astriStatus !== '' ? $astriStatus : 'NY';
            if (!in_array($astriStatus, ['NY', 'ON REVIEW', 'APPROVED', 'REJECTED'], true)) {
                $skippedMessages[] = $docName . ' punya status Astri tidak dikenali.';
                continue;
            }

            $astriSubmittedDate = $this->normalizeDate($astriSubmittedDates[$fileId] ?? null);
            $astriApprovedDate = $this->normalizeDate($astriApprovedDates[$fileId] ?? null);
            $astriRemark = trim((string) ($astriRemarks[$fileId] ?? ''));
            if ($astriStatus !== 'NY' && empty($astriSubmittedDate)) {
                $skippedMessages[] = $docName . ' belum isi tanggal submit Astri.';
                continue;
            }
            if ($astriStatus === 'REJECTED' && $astriRemark === '') {
                $skippedMessages[] = $docName . ' rejected wajib remark.';
                continue;
            }

            $updated = $this->MBatch_Approval_MyRep->updateDonationAstriStatus($fileId, [
                'astri_status' => $astriStatus,
                'astri_submitted_date' => $astriStatus === 'NY' ? null : $astriSubmittedDate,
                'astri_approved_date' => $astriStatus === 'APPROVED' ? $astriApprovedDate : null,
                'astri_remark' => $astriRemark,
                'updated_by' => (int) $this->session->userdata('id_user'),
            ]);
            if ($updated) {
                if ($astriStatus === 'REJECTED') {
                    $this->myrepRejectEmail->enqueueReject('Batch_Approval_MyRep', $fileId, [
                        'remark' => $astriRemark,
                        'rejecter_user_id' => (int) $this->session->userdata('id_user'),
                        'rejected_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                $updatedCount++;
            } else {
                $skippedMessages[] = $docName . ' gagal disimpan.';
            }
        }

        if ($updatedCount > 0 && in_array($groupKey, ['PRE_ZEYN', 'POST_ZEYN'], true)) {
            $summary = $this->MBatch_Approval_MyRep->getDonationDocumentSummary($clusterId);
            $totalRejected = (int) ($summary['PRE_ZEYN']['astri_rejected'] ?? 0) + (int) ($summary['POST_ZEYN']['astri_rejected'] ?? 0);
            if ($totalRejected > 0) {
                $this->setDonationStageFromSystem($clusterId, 'ASTRI_ON_REVIEW', [
                    'final_astri_approved_at' => null,
                ]);
            } elseif ($groupKey === 'POST_ZEYN' && (int) ($summary['POST_ZEYN']['required'] ?? 0) > 0 && (int) ($summary['POST_ZEYN']['astri_submitted'] ?? 0) > 0) {
                $batch = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
                $currentStage = strtoupper(trim((string) ($batch['staging_status'] ?? '')));
                if ($currentStage === 'WAITING_ASTRI_SUBMISSION') {
                    $this->setDonationStageFromSystem($clusterId, 'ASTRI_ON_REVIEW');
                }
            }
        }

        $message = $updatedCount . ' status Astri berhasil diperbarui.';
        if (!empty($skippedMessages)) {
            $message .= ' ' . count($skippedMessages) . ' dokumen dilewati: ' . implode('; ', array_slice($skippedMessages, 0, 3));
        }

        if ($updatedCount > 0) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(true, $message, base_url($redirectPath));
                return;
            }
            $this->session->set_flashdata('success', $message);
            redirect($redirectPath);
            return;
        }

        if ($this->isAjaxRequest()) {
            $this->jsonResponse(false, !empty($skippedMessages) ? implode('; ', array_slice($skippedMessages, 0, 3)) : 'Tidak ada status Astri yang diperbarui.', base_url($redirectPath));
            return;
        }
        $this->session->set_flashdata('error', !empty($skippedMessages) ? implode('; ', array_slice($skippedMessages, 0, 3)) : 'Tidak ada status Astri yang diperbarui.');
        redirect($redirectPath);
    }

    public function saveDonationPoInvoice()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $batchId = (int) $this->input->post('id_batch_approval');
        $redirectPath = $this->resolveBatchRedirectPath($clusterId);
        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses PO/Invoice Donasi.');
            redirect($redirectPath);
            return;
        }
        if (!$this->MBatch_Approval_MyRep->areAllDonationDocumentsAstriApproved($clusterId)) {
            $this->session->set_flashdata('error', 'PO/Invoice Donasi belum bisa dibuat karena semua dokumen Astri belum full approved.');
            redirect($redirectPath);
            return;
        }

        $poNumber = trim((string) $this->input->post('po_donasi_number'));
        $poDate = $this->normalizeDate($this->input->post('po_donasi_date'));
        $poValue = $this->normalizeNullableNumber($this->input->post('po_donasi_value'));
        $invoiceNumber = trim((string) $this->input->post('invoice_donasi_number'));
        $invoiceDate = $this->normalizeDate($this->input->post('invoice_donasi_date'));
        $invoiceValue = $this->normalizeNullableNumber($this->input->post('invoice_donasi_value'));
        if ($poNumber === '' || $poDate === null || $poValue === null) {
            $this->session->set_flashdata('error', 'Nomor PO, tanggal PO, dan nilai PO Donasi wajib diisi.');
            redirect($redirectPath);
            return;
        }

        $batchPayload = [
            'staging_status' => $invoiceNumber !== '' ? 'INVOICE' : 'PO_DONASI',
            'po_donasi_number' => $poNumber,
            'po_donasi_date' => $poDate,
            'po_donasi_value' => $poValue,
            'po_donasi_status' => trim((string) $this->input->post('po_donasi_status')) ?: 'ISSUED',
            'invoice_donasi_number' => $invoiceNumber !== '' ? $invoiceNumber : null,
            'invoice_donasi_date' => $invoiceDate,
            'invoice_donasi_value' => $invoiceValue,
            'invoice_donasi_status' => trim((string) $this->input->post('invoice_donasi_status')) ?: ($invoiceNumber !== '' ? 'BILLED' : null),
            'invoice_donasi_remark' => trim((string) $this->input->post('invoice_donasi_remark')),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ];
        $poPayload = [
            'po_number' => $poNumber,
            'po_date' => $poDate,
            'po_value' => $poValue,
            'status_po' => $batchPayload['po_donasi_status'],
            'remark_po' => 'PT EMR - DONASI',
            'created_by' => (int) $this->session->userdata('id_user'),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ];
        $terminPayload = [
            'termin_value' => $poValue,
            'status_termin' => $invoiceNumber !== '' ? 'BILLED' : 'READY BILLING',
            'invoice_number' => $invoiceNumber !== '' ? $invoiceNumber : null,
            'invoice_date' => $invoiceDate,
            'invoice_value' => $invoiceValue,
            'remark_termin' => trim((string) $this->input->post('invoice_donasi_remark')),
            'created_by' => (int) $this->session->userdata('id_user'),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ];

        $result = $this->MBatch_Approval_MyRep->saveDonationPoInvoice($clusterId, $batchId, $batchPayload, $poPayload, $terminPayload);
        if ($result && $poNumber !== '') {
            $this->load->model('MPO_Monitor');
            $this->MPO_Monitor->syncMyRepClaimsForPoNumber($poNumber, (int) $this->session->userdata('id_user'), true);
        }
        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'PO/Invoice Donasi berhasil disimpan.' : 'Gagal menyimpan PO/Invoice Donasi.');
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

    public function downloadDocument($fileId = 0)
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

        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    public function downloadDonationDocumentBundle($clusterId = 0, $groupKey = '')
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        $groupKey = strtoupper(trim((string) $groupKey));
        if ($clusterId <= 0 || !in_array($groupKey, ['PRE_ZEYN', 'POST_ZEYN'], true) || !$this->MBatch_Approval_MyRep->batchDocumentTablesReady()) {
            show_404();
            return;
        }

        $cluster = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        if (empty($cluster)) {
            show_404();
            return;
        }

        $documentRows = $this->MBatch_Approval_MyRep->getDonationDocumentRows($clusterId, $groupKey);
        if (empty($documentRows)) {
            $this->session->set_flashdata('error', 'Dokumen donasi tidak ditemukan.');
            redirect($this->resolveBatchRedirectPath($clusterId));
            return;
        }

        if (!class_exists('ZipArchive')) {
            $this->session->set_flashdata('error', 'Ekstensi ZIP belum aktif di server.');
            redirect($this->resolveBatchRedirectPath($clusterId));
            return;
        }

        $tempZip = tempnam(sys_get_temp_dir(), 'batch_donation_bundle_');
        if ($tempZip === false) {
            $this->session->set_flashdata('error', 'Gagal menyiapkan file download gabungan.');
            redirect($this->resolveBatchRedirectPath($clusterId));
            return;
        }

        $zipFile = $tempZip . '.zip';
        @rename($tempZip, $zipFile);

        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            if (is_file($zipFile)) {
                @unlink($zipFile);
            }
            $this->session->set_flashdata('error', 'Gagal membuat file download gabungan.');
            redirect($this->resolveBatchRedirectPath($clusterId));
            return;
        }

        $addedCount = 0;
        foreach ($documentRows as $index => $documentRow) {
            $statusFile = strtoupper(trim((string) ($documentRow['status_file'] ?? '')));
            $financeStatus = strtoupper(trim((string) ($documentRow['finance_status'] ?? 'NY')));
            $filePath = trim((string) ($documentRow['file_path'] ?? ''));
            if ($statusFile !== 'APPROVED' || $financeStatus !== 'APPROVED' || $filePath === '') {
                continue;
            }

            $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
            if (!is_file($fullPath)) {
                continue;
            }

            $docName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($documentRow['doc_name'] ?? 'DOKUMEN'));
            $originalName = basename($fullPath);
            $zip->addFile($fullPath, str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . '_' . $docName . '_' . $originalName);
            $addedCount++;
        }

        $zip->close();

        if ($addedCount <= 0) {
            if (is_file($zipFile)) {
                @unlink($zipFile);
            }
            $this->session->set_flashdata('error', 'Tidak ada dokumen yang sudah approved SITAC dan Finance untuk didownload.');
            redirect($this->resolveBatchRedirectPath($clusterId));
            return;
        }

        $safeClusterName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($cluster['cluster_name'] ?? 'CLUSTER'));
        $bundleLabel = $groupKey === 'POST_ZEYN' ? 'POST_PAYMENT_ZEYN' : 'PRE_FINANCE_ZEYN';
        $downloadName = 'DONASI_' . $bundleLabel . '_' . $safeClusterName . '_' . date('Ymd_His') . '.zip';

        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($zipFile));
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        readfile($zipFile);
        @unlink($zipFile);
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
            'astri_initial_submitted_at',
            'astri_batch_approved_at',
            'hold_at',
            'hold_remark',
            'rejected_at',
            'rejected_remark',
            'finance_submitted_at',
            'final_astri_submitted_at',
            'final_astri_approved_at',
            'pic_1_name',
            'pic_1_phone',
            'pic_1_position',
            'pic_1_period',
            'pic_2_name',
            'pic_2_phone',
            'pic_2_position',
            'pic_2_period',
            'pic_3_name',
            'pic_3_phone',
            'pic_3_position',
            'pic_3_period',
            'pic_4_name',
            'pic_4_phone',
            'pic_4_position',
            'pic_4_period',
            'pic_5_name',
            'pic_5_phone',
            'pic_5_position',
            'pic_5_period',
        ];
        foreach (range(1, 9) as $docNo) {
            foreach (['status', 'file_name', 'file_path', 'uploaded_at', 'approved_at', 'remark', 'finance_status', 'finance_approved_at', 'finance_remark'] as $field) {
                $headers[] = 'pre_doc_' . $docNo . '_' . $field;
            }
        }
        foreach (range(1, 6) as $docNo) {
            foreach (['status', 'file_name', 'file_path', 'uploaded_at', 'approved_at', 'remark', 'finance_status', 'finance_approved_at', 'finance_remark'] as $field) {
                $headers[] = 'post_doc_' . $docNo . '_' . $field;
            }
        }
        foreach (range(1, 6) as $docNo) {
            foreach (['status', 'submitted_date', 'approved_date', 'remark'] as $field) {
                $headers[] = 'astri_doc_' . $docNo . '_' . $field;
            }
        }
        $headers = array_merge($headers, [
            'po_donasi_number',
            'po_donasi_date',
            'po_donasi_value',
            'po_donasi_status',
            'invoice_donasi_number',
            'invoice_donasi_date',
            'invoice_donasi_value',
            'invoice_donasi_status',
            'invoice_donasi_remark',
        ]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=batch_approval_import_template_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        fputcsv($output, $this->buildBatchImportExampleRow($headers, [
            'city_name' => 'MALANG',
            'cluster_name' => 'Cluster Contoh Waiting Batch',
            'cluster_code' => 'CL-WAITING-BATCH',
            'homepass_valsal' => '120',
            'valsal_date' => date('Y-m-d'),
            'status_valsal' => 'DONE',
            'remark_valsal' => 'VALSAL done',
            'hp_donasi' => '100',
            'nominal_pengajuan_area' => '30000000',
            'submission_date' => date('Y-m-d'),
            'staging_status' => 'WAITING_BATCH_APPROVAL',
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081200000001',
            'recipient_position' => 'Ketua RT',
            'recipient_period' => '2026',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'remark_batch_approval' => 'Contoh pengajuan awal menunggu batch Astri',
            'astri_initial_submitted_at' => date('Y-m-d H:i:s'),
            'pic_1_name' => 'Budi Santoso',
            'pic_1_phone' => '081200000001',
            'pic_1_position' => 'Ketua RT',
            'pic_1_period' => '2026',
        ]));

        fputcsv($output, $this->buildBatchImportExampleRow($headers, [
            'city_name' => 'MALANG',
            'cluster_name' => 'Cluster Contoh Donasi Lengkap',
            'cluster_code' => 'CL-DONASI-FULL',
            'homepass_valsal' => '150',
            'valsal_date' => date('Y-m-d', strtotime('-10 days')),
            'status_valsal' => 'APPROVED',
            'remark_valsal' => 'VALSAL approved',
            'hp_donasi' => '120',
            'nominal_pengajuan_area' => '36000000',
            'nominal_nego_emr' => '35000000',
            'nominal_release_finance' => '35000000',
            'submission_date' => date('Y-m-d', strtotime('-9 days')),
            'staging_status' => 'INVOICE',
            'astri_batch_number' => 'ASTRI-DONASI-001',
            'recipient_name' => 'Andi Wijaya',
            'recipient_phone' => '081200000002',
            'recipient_position' => 'Ketua RW',
            'recipient_period' => '2026',
            'bank_name' => 'BNI',
            'bank_account_number' => '1234567891',
            'free_wifi_qty' => '1',
            'free_wifi_period_month' => '12',
            'remark_batch_approval' => 'Contoh row lengkap sampai invoice',
            'astri_initial_submitted_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
            'astri_batch_approved_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            'finance_submitted_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'final_astri_submitted_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'final_astri_approved_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'pic_1_name' => 'Andi Wijaya',
            'pic_1_phone' => '081200000002',
            'pic_1_position' => 'Ketua RW',
            'pic_1_period' => '2026',
            'po_donasi_number' => 'PO-DONASI-001',
            'po_donasi_date' => date('Y-m-d'),
            'po_donasi_value' => '35000000',
            'po_donasi_status' => 'ISSUED',
            'invoice_donasi_number' => 'INV-DONASI-001',
            'invoice_donasi_date' => date('Y-m-d'),
            'invoice_donasi_value' => '35000000',
            'invoice_donasi_status' => 'BILLED',
            'invoice_donasi_remark' => 'Invoice donasi 1 term 100 persen',
        ], true));
        fclose($output);
        exit;
    }

    public function downloadDonationDocumentFormat()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->get('cluster_id');
        $groupKey = strtoupper(trim((string) $this->input->get('group_key')));
        if ($clusterId <= 0 || $groupKey === '' || !$this->MBatch_Approval_MyRep->batchDocumentTablesReady()) {
            show_404();
            return;
        }

        $rows = $this->MBatch_Approval_MyRep->getDonationDocumentRows($clusterId, $groupKey);
        if (empty($rows)) {
            show_404();
            return;
        }

        $filename = 'format_upload_donasi_' . strtolower($groupKey) . '_' . $clusterId . '_' . date('YmdHis') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['No', 'Nama Dokumen', 'Verification By', 'Wajib', 'Allowed File', 'Maks Size', 'Catatan']);
        foreach ($rows as $index => $row) {
            $docName = (string) ($row['doc_name'] ?? '-');
            fputcsv($output, [
                $index + 1,
                $docName,
                $groupKey === 'POST_ZEYN' ? 'SITAC HO / ASTRI' : 'SITAC HO',
                (int) ($row['is_required'] ?? 1) === 1 ? 'YA' : 'OPSIONAL',
                strtoupper(str_replace('|', ', ', $this->resolveDonationAllowedTypes($docName, (string) ($row['group_label'] ?? '')))),
                '20 MB',
                (string) ($row['doc_requirement_note'] ?? ''),
            ]);
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
            if ($nominalNegoEmr === null && $nominalPengajuanArea > 0) {
                $nominalNegoEmr = $nominalPengajuanArea;
            }
            $nominalReleaseFinance = $this->normalizeNullableNumber($row['nominal_release_finance'] ?? '');
            $nominalPerHomepass = $hpDonasi > 0 ? round($nominalPengajuanArea / $hpDonasi, 2) : 0;
            if ($stagingStatus === 'WAITING_BATCH_APPROVAL'
                && (trim((string) ($row['astri_batch_number'] ?? '')) !== '' || trim((string) ($row['astri_batch_approved_at'] ?? '')) !== '')) {
                $stagingStatus = 'BATCH_APPROVED';
            }

            $pics = $this->collectPicsFromImportRow($row);
            $batchId = (int) ($row['id_batch_approval'] ?? 0);
            $financeSubmittedAt = $this->normalizeDateTimeInput($row['finance_submitted_at'] ?? '');
            $releasedAt = $this->normalizeDateTimeInput($row['released_at'] ?? '');

            $batchPayload = [
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
                'submitted_to_ho_at' => $stagingStatus === 'WAITING_BATCH_APPROVAL' ? date('Y-m-d H:i:s') : null,
                'astri_initial_submitted_at' => $this->normalizeDateTimeInput($row['astri_initial_submitted_at'] ?? '') ?: ($stagingStatus === 'WAITING_BATCH_APPROVAL' ? date('Y-m-d H:i:s') : null),
                'astri_batch_approved_at' => $this->normalizeDateTimeInput($row['astri_batch_approved_at'] ?? ''),
                'hold_at' => $this->normalizeDateTimeInput($row['hold_at'] ?? ''),
                'hold_remark' => trim((string) ($row['hold_remark'] ?? '')) ?: null,
                'rejected_at' => $this->normalizeDateTimeInput($row['rejected_at'] ?? ''),
                'rejected_remark' => trim((string) ($row['rejected_remark'] ?? '')) ?: null,
                'finance_submitted_at' => $financeSubmittedAt,
                'submitted_to_astri_at' => $stagingStatus === 'WAITING MYREP' ? date('Y-m-d H:i:s') : null,
                'submitted_to_finance_at' => $financeSubmittedAt ?: (in_array($stagingStatus, ['WAITING FINANCE', 'WAITING_FINANCE_RELEASE'], true) ? date('Y-m-d H:i:s') : null),
                'released_at' => $releasedAt ?: ($stagingStatus === 'RELEASED' ? date('Y-m-d H:i:s') : null),
                'final_astri_submitted_at' => $this->normalizeDateTimeInput($row['final_astri_submitted_at'] ?? ''),
                'final_astri_approved_at' => $this->normalizeDateTimeInput($row['final_astri_approved_at'] ?? ''),
                'po_donasi_number' => trim((string) ($row['po_donasi_number'] ?? '')) ?: null,
                'po_donasi_date' => $this->normalizeDate($row['po_donasi_date'] ?? ''),
                'po_donasi_value' => $this->normalizeNullableNumber($row['po_donasi_value'] ?? ''),
                'po_donasi_status' => trim((string) ($row['po_donasi_status'] ?? '')) ?: null,
                'invoice_donasi_number' => trim((string) ($row['invoice_donasi_number'] ?? '')) ?: null,
                'invoice_donasi_date' => $this->normalizeDate($row['invoice_donasi_date'] ?? ''),
                'invoice_donasi_value' => $this->normalizeNullableNumber($row['invoice_donasi_value'] ?? ''),
                'invoice_donasi_status' => trim((string) ($row['invoice_donasi_status'] ?? '')) ?: null,
                'invoice_donasi_remark' => trim((string) ($row['invoice_donasi_remark'] ?? '')) ?: null,
                'remark_batch_approval' => trim((string) ($row['remark_batch_approval'] ?? '')) ?: null,
                'updated_by' => $userId,
            ];
            $clusterPayload = [
                'status_current' => $this->mapClusterStatusFromStaging($stagingStatus),
                'updated_by' => $userId,
            ];

            if ($batchId > 0) {
                $saved = $this->MBatch_Approval_MyRep->updateBatchApproval($clusterId, $batchId, $batchPayload, $clusterPayload, $pics);
            } else {
                $batchPayload['created_by'] = $userId;
                $batchId = $this->MBatch_Approval_MyRep->createBatchApproval($clusterId, $batchPayload, $clusterPayload, $pics);
                $saved = $batchId > 0;
            }

            if ($saved && $batchId > 0) {
                $this->importDonationDocuments($clusterId, $row, $userId);
                $this->importDonationPoInvoice($clusterId, $batchId, $row, $userId);
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
                'message' => $inserted . ' data Batch Approval berhasil disimpan/diperbarui.',
            ]));
    }

    private function normalizeStagingStatus($status, $isCreate)
    {
        $status = strtoupper(trim((string) $status));
        $labelMap = [
            'DRAFT' => 'DRAFT',
            'MENUNGGU REVIEW HO' => 'WAITING HO',
            'MENUNGGU REVIEW EMR' => 'WAITING MYREP',
            'MENUNGGU FINANCE' => 'WAITING FINANCE',
            'MENUNGGU BATCH APPROVAL' => 'WAITING_BATCH_APPROVAL',
            'MENUNGGU NOMOR BATCH APPROVAL' => 'WAITING_BATCH_APPROVAL',
            'BATCH APPROVAL DISETUJUI' => 'BATCH_APPROVED',
            'DITAHAN' => 'HOLD',
            'MENUNGGU DOKUMEN AWAL ZEYN' => 'WAITING_PRE_ZEYN_DOC',
            'REVIEW DOKUMEN AWAL ZEYN' => 'PRE_ZEYN_DOC_ON_REVIEW',
            'DOKUMEN AWAL ZEYN DISETUJUI' => 'PRE_ZEYN_DOC_APPROVED',
            'NY DOKUMEN TAHAP 1' => 'WAITING_PRE_ZEYN_DOC',
            'ON REVIEW DOKUMEN TAHAP 1' => 'PRE_ZEYN_DOC_ON_REVIEW',
            'APPROVED DOKUMEN TAHAP 1' => 'PRE_ZEYN_DOC_APPROVED',
            'ON REVIEW FINANCE DOKUMEN TAHAP 1' => 'PRE_ZEYN_FINANCE_ON_REVIEW',
            'APPROVED FINANCE DOKUMEN TAHAP 1' => 'PRE_ZEYN_FINANCE_APPROVED',
            'MENUNGGU PEMBAYARAN FINANCE' => 'WAITING_FINANCE_RELEASE',
            'DONASI DIBAYARKAN' => 'RELEASED',
            'MENUNGGU DOKUMEN SETELAH BAYAR ZEYN' => 'WAITING_POST_ZEYN_DOC',
            'REVIEW DOKUMEN SETELAH BAYAR ZEYN' => 'POST_ZEYN_DOC_ON_REVIEW',
            'DOKUMEN SETELAH BAYAR ZEYN DISETUJUI' => 'POST_ZEYN_DOC_APPROVED',
            'NY DOKUMEN TAHAP 2' => 'WAITING_POST_ZEYN_DOC',
            'ON REVIEW DOKUMEN TAHAP 2' => 'POST_ZEYN_DOC_ON_REVIEW',
            'APPROVED DOKUMEN TAHAP 2' => 'POST_ZEYN_DOC_APPROVED',
            'ON REVIEW FINANCE DOKUMEN TAHAP 2' => 'POST_ZEYN_FINANCE_ON_REVIEW',
            'MENUNGGU SUBMIT ASTRI' => 'WAITING_ASTRI_SUBMISSION',
            'REVIEW ASTRI' => 'ASTRI_ON_REVIEW',
            'ASTRI DISETUJUI' => 'ASTRI_APPROVED',
            'ON REVIEW ASTRI' => 'ASTRI_ON_REVIEW',
            'APPROVED ASTRI' => 'ASTRI_APPROVED',
            'PO DONASI' => 'PO_DONASI',
            'INVOICE' => 'INVOICE',
            'DITOLAK' => 'REJECTED',
            'NEED REVISE' => 'NEED_REVISE',
            'PERLU REVISI' => 'NEED_REVISE',
            'NY DOK' => 'WAITING_POST_ZEYN_DOC',
            'ASTRI' => 'ASTRI_ON_REVIEW',
            'APPROVE' => 'ASTRI_APPROVED',
            'CANCEL' => 'REJECTED',
            'REVISI' => 'POST_ZEYN_DOC_ON_REVIEW',
            'TALANGAN' => 'WAITING_FINANCE_RELEASE',
        ];
        if (isset($labelMap[$status])) {
            $status = $labelMap[$status];
        }

        $allowed = [
            'DRAFT',
            'WAITING HO',
            'WAITING MYREP',
            'WAITING FINANCE',
            'WAITING_BATCH_APPROVAL',
            'BATCH_APPROVED',
            'HOLD',
            'REJECTED',
            'NEED_REVISE',
            'WAITING_PRE_ZEYN_DOC',
            'PRE_ZEYN_DOC_ON_REVIEW',
            'PRE_ZEYN_DOC_APPROVED',
            'PRE_ZEYN_FINANCE_ON_REVIEW',
            'PRE_ZEYN_FINANCE_APPROVED',
            'WAITING_FINANCE_SUBMISSION',
            'WAITING_FINANCE_RELEASE',
            'RELEASED',
            'WAITING_POST_ZEYN_DOC',
            'POST_ZEYN_DOC_ON_REVIEW',
            'POST_ZEYN_DOC_APPROVED',
            'WAITING_ASTRI_SUBMISSION',
            'ASTRI_ON_REVIEW',
            'ASTRI_APPROVED',
            'PO_DONASI',
            'INVOICE',
            'DONE BATCH APPROVAL',
        ];
        if (!in_array($status, $allowed, true)) {
            return $isCreate ? 'WAITING_BATCH_APPROVAL' : 'DRAFT';
        }

        return $status;
    }

    private function getIndonesianStagingLabel($status)
    {
        $status = strtoupper(trim((string) $status));
        $labels = [
            'DRAFT' => 'Draft',
            'WAITING HO' => 'Menunggu Review HO',
            'WAITING MYREP' => 'Menunggu Review EMR',
            'WAITING FINANCE' => 'Menunggu Finance',
            'WAITING_BATCH_APPROVAL' => 'Menunggu Nomor Batch Approval',
            'BATCH_APPROVED' => 'Batch Approval Disetujui',
            'HOLD' => 'Ditahan',
            'WAITING_PRE_ZEYN_DOC' => 'NY Dokumen Tahap 1',
            'PRE_ZEYN_DOC_ON_REVIEW' => 'On Review Dokumen Tahap 1',
            'PRE_ZEYN_DOC_APPROVED' => 'Approved Dokumen Tahap 1',
            'PRE_ZEYN_FINANCE_ON_REVIEW' => 'On Review Finance Dokumen Tahap 1',
            'PRE_ZEYN_FINANCE_APPROVED' => 'Approved Finance Dokumen Tahap 1',
            'WAITING_FINANCE_RELEASE' => 'Menunggu Pembayaran Finance',
            'RELEASED' => 'Donasi Dibayarkan',
            'WAITING_POST_ZEYN_DOC' => 'NY Dokumen Tahap 2',
            'POST_ZEYN_DOC_ON_REVIEW' => 'On Review Dokumen Tahap 2',
            'POST_ZEYN_DOC_APPROVED' => 'Approved Dokumen Tahap 2',
            'POST_ZEYN_FINANCE_ON_REVIEW' => 'On Review Finance Dokumen Tahap 2',
            'WAITING_ASTRI_SUBMISSION' => 'Menunggu Submit Astri',
            'ASTRI_ON_REVIEW' => 'On Review Astri',
            'ASTRI_APPROVED' => 'Approved Astri',
            'PO_DONASI' => 'PO Donasi',
            'INVOICE' => 'Invoice',
            'DONE BATCH APPROVAL' => 'Batch Approval Selesai',
            'WAITING DOC' => 'Menunggu Dokumen Post Donasi',
            'COMPLETED' => 'Done',
            'REJECTED' => 'Ditolak',
            'NEED_REVISE' => 'Need Revise',
        ];

        return $labels[$status] ?? ($status !== '' ? ucwords(strtolower(str_replace('_', ' ', $status))) : 'Draft');
    }

    private function parseBatchImportHeader($header)
    {
        $header = strtolower(trim((string) $header));
        if ($header === '') {
            return null;
        }

        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        $header = trim($header, '_');

        if (preg_match('/^(pre_doc|post_doc|astri_doc)_([1-9])_(status|file_name|file_path|uploaded_at|approved_at|remark|submitted_date|approved_date|finance_status|finance_approved_at|finance_remark)$/', $header)) {
            return $header;
        }
        if (preg_match('/^pic_([1-5])_(name|phone|position|period)$/', $header)) {
            return $header;
        }

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
            'hp_donasi' => ['hp_donasi', 'hp_donasi_actual', 'hp_penerima', 'hp_rfs'],
            'nominal_pengajuan_area' => ['nominal_pengajuan_area', 'nilai_donasi', 'nominal_donasi'],
            'nominal_nego_emr' => ['nominal_nego_emr'],
            'nominal_release_finance' => ['nominal_release_finance', 'pencairan_donasi_value', 'nilai_pencairan_donasi', 'nominal_pencairan_donasi'],
            'submission_date' => ['submission_date', 'tanggal_pengajuan'],
            'staging_status' => ['staging_status', 'current_status', 'status_batch', 'status_donasi'],
            'astri_batch_number' => ['astri_batch_number', 'nomor_batch_astri'],
            'recipient_name' => ['recipient_name', 'nama_penerima', 'nama_pemilik_rekening'],
            'recipient_phone' => ['recipient_phone', 'telp_penerima', 'no_tec'],
            'recipient_position' => ['recipient_position', 'jabatan_penerima'],
            'recipient_period' => ['recipient_period', 'periode_penerima'],
            'bank_name' => ['bank_name', 'nama_bank'],
            'bank_account_number' => ['bank_account_number', 'no_rekening'],
            'free_wifi_qty' => ['free_wifi_qty'],
            'free_wifi_period_month' => ['free_wifi_period_month'],
            'remark_batch_approval' => ['remark_batch_approval', 'remark_batch', 'catatan_batch'],
            'astri_initial_submitted_at' => ['astri_initial_submitted_at', 'tanggal_submit_awal_astri'],
            'astri_batch_approved_at' => ['astri_batch_approved_at', 'tanggal_batch_approval_astri'],
            'hold_at' => ['hold_at', 'tanggal_hold'],
            'hold_remark' => ['hold_remark', 'remark_hold'],
            'rejected_at' => ['rejected_at', 'tanggal_reject'],
            'rejected_remark' => ['rejected_remark', 'remark_reject'],
            'finance_submitted_at' => ['finance_submitted_at', 'tanggal_pengajuan_finance', 'pengajuan_ke_finance', 'pengajuan_donasi', 'pengajuan_donasi_date', 'tanggal_pengajuan_donasi'],
            'final_astri_submitted_at' => ['final_astri_submitted_at', 'tanggal_submit_final_astri', 'dok_upload_astri_submit', 'submit_astri'],
            'final_astri_approved_at' => ['final_astri_approved_at', 'tanggal_approve_final_astri', 'dok_upload_astri_approved', 'approved_astri'],
            'released_at' => ['released_at', 'tanggal_release_finance', 'tanggal_pencairan_donasi', 'pencairan_donasi', 'pencairan_donasi_date'],
            'po_donasi_number' => ['po_donasi_number', 'nomor_po_donasi'],
            'po_donasi_date' => ['po_donasi_date', 'tanggal_po_donasi'],
            'po_donasi_value' => ['po_donasi_value', 'nilai_po_donasi'],
            'po_donasi_status' => ['po_donasi_status', 'status_po_donasi'],
            'invoice_donasi_number' => ['invoice_donasi_number', 'nomor_invoice_donasi'],
            'invoice_donasi_date' => ['invoice_donasi_date', 'tanggal_invoice_donasi'],
            'invoice_donasi_value' => ['invoice_donasi_value', 'nilai_invoice_donasi'],
            'invoice_donasi_status' => ['invoice_donasi_status', 'status_invoice_donasi'],
            'invoice_donasi_remark' => ['invoice_donasi_remark', 'remark_invoice_donasi'],
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

    private function buildBatchImportExampleRow(array $headers, array $values, $includeDonationDocuments = false)
    {
        if ($includeDonationDocuments) {
            foreach (range(1, 9) as $docNo) {
                $values['pre_doc_' . $docNo . '_status'] = 'APPROVED';
                $values['pre_doc_' . $docNo . '_file_name'] = 'pre_doc_' . $docNo . '.pdf';
                $values['pre_doc_' . $docNo . '_file_path'] = 'uploads/import_sample/pre_doc_' . $docNo . '.pdf';
                $values['pre_doc_' . $docNo . '_uploaded_at'] = date('Y-m-d H:i:s', strtotime('-7 days'));
                $values['pre_doc_' . $docNo . '_approved_at'] = date('Y-m-d H:i:s', strtotime('-6 days'));
                $values['pre_doc_' . $docNo . '_remark'] = $docNo === 9 ? 'Opsional Form Free Wifi & KTP' : 'Dokumen pre Zeyn approved';
            }

            foreach (range(1, 6) as $docNo) {
                $values['post_doc_' . $docNo . '_status'] = 'APPROVED';
                $values['post_doc_' . $docNo . '_file_name'] = 'post_doc_' . $docNo . '.pdf';
                $values['post_doc_' . $docNo . '_file_path'] = 'uploads/import_sample/post_doc_' . $docNo . '.pdf';
                $values['post_doc_' . $docNo . '_uploaded_at'] = date('Y-m-d H:i:s', strtotime('-4 days'));
                $values['post_doc_' . $docNo . '_approved_at'] = date('Y-m-d H:i:s', strtotime('-3 days'));
                $values['post_doc_' . $docNo . '_remark'] = 'Dokumen post payment approved';

                $values['astri_doc_' . $docNo . '_status'] = 'APPROVED';
                $values['astri_doc_' . $docNo . '_submitted_date'] = date('Y-m-d', strtotime('-2 days'));
                $values['astri_doc_' . $docNo . '_approved_date'] = date('Y-m-d', strtotime('-1 day'));
                $values['astri_doc_' . $docNo . '_remark'] = 'Astri final approved';
            }
        }

        $row = [];
        foreach ($headers as $header) {
            $row[] = array_key_exists($header, $values) ? (string) $values[$header] : '';
        }

        return $row;
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
            }

            $existingBatch = $clusterId > 0 ? $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId) : [];
            if ($homepassValsal <= 0) {
                $homepassValsal = (int) ($existingBatch['homepass_valsal'] ?? $candidate['homepass_valsal'] ?? $candidate['hp_plan'] ?? 0);
            }
            $hpDonasi = (int) $this->normalizeNumber($rawRow['hp_donasi'] ?? 0);
            if ($hpDonasi <= 0) {
                $hpDonasi = (int) ($existingBatch['hp_donasi'] ?? 0);
            }
            $nominalPengajuanArea = $this->normalizeNumber($rawRow['nominal_pengajuan_area'] ?? 0);
            if ($nominalPengajuanArea <= 0) {
                $nominalPengajuanArea = (float) ($existingBatch['nominal_pengajuan_area'] ?? 0);
            }
            $submissionDate = $this->normalizeDate((string) ($rawRow['submission_date'] ?? '')) ?: ($existingBatch['submission_date'] ?? date('Y-m-d'));
            $stagingStatus = $this->normalizeStagingStatus((string) ($rawRow['staging_status'] ?? ($existingBatch['staging_status'] ?? 'WAITING_BATCH_APPROVAL')), true);
            $astriBatchNumber = trim((string) ($rawRow['astri_batch_number'] ?? '')) !== '' ? (string) $rawRow['astri_batch_number'] : (string) ($existingBatch['astri_batch_number'] ?? '');
            $astriBatchApprovedAt = trim((string) ($rawRow['astri_batch_approved_at'] ?? '')) !== '' ? $rawRow['astri_batch_approved_at'] : ($existingBatch['astri_batch_approved_at'] ?? '');
            if ($stagingStatus === 'WAITING_BATCH_APPROVAL' && ($astriBatchNumber !== '' || trim((string) $astriBatchApprovedAt) !== '')) {
                $stagingStatus = 'BATCH_APPROVED';
            }
            $recipientName = trim((string) ($rawRow['recipient_name'] ?? '')) ?: (string) ($existingBatch['recipient_name'] ?? '');
            $bankName = trim((string) ($rawRow['bank_name'] ?? '')) ?: (string) ($existingBatch['bank_name'] ?? '');
            $bankAccountNumber = trim((string) ($rawRow['bank_account_number'] ?? '')) ?: (string) ($existingBatch['bank_account_number'] ?? '');

            if ($homepassValsal <= 0 || $hpDonasi <= 0) {
                $rowErrors[] = 'homepass_valsal dan hp_donasi wajib > 0';
            }
            if ($nominalPengajuanArea <= 0) {
                $rowErrors[] = 'nominal_pengajuan_area wajib > 0';
            }
            if ($recipientName === '' || $bankName === '' || $bankAccountNumber === '') {
                $rowErrors[] = 'recipient_name, bank_name, bank_account_number wajib diisi';
            }

            $preparedRows[] = array_merge($rawRow, [
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
                'nominal_nego_emr' => trim((string) ($rawRow['nominal_nego_emr'] ?? '')) !== '' ? $rawRow['nominal_nego_emr'] : ($existingBatch['nominal_nego_emr'] ?? ($nominalPengajuanArea > 0 ? $nominalPengajuanArea : '')),
                'nominal_release_finance' => trim((string) ($rawRow['nominal_release_finance'] ?? '')) !== '' ? $rawRow['nominal_release_finance'] : ($existingBatch['nominal_release_finance'] ?? ''),
                'submission_date' => $submissionDate,
                'staging_status' => $stagingStatus,
                'astri_batch_number' => $astriBatchNumber,
                'recipient_name' => $recipientName,
                'recipient_phone' => trim((string) ($rawRow['recipient_phone'] ?? '')) !== '' ? (string) $rawRow['recipient_phone'] : (string) ($existingBatch['recipient_phone'] ?? ''),
                'recipient_position' => trim((string) ($rawRow['recipient_position'] ?? '')) !== '' ? (string) $rawRow['recipient_position'] : (string) ($existingBatch['recipient_position'] ?? ''),
                'recipient_period' => trim((string) ($rawRow['recipient_period'] ?? '')) !== '' ? (string) $rawRow['recipient_period'] : (string) ($existingBatch['recipient_period'] ?? ''),
                'bank_name' => $bankName,
                'bank_account_number' => $bankAccountNumber,
                'id_batch_approval' => (int) ($existingBatch['id_batch_approval'] ?? $candidate['id_batch_approval'] ?? 0),
                'import_mode' => !empty($existingBatch['id_batch_approval']) || !empty($candidate['id_batch_approval']) ? 'UPDATE' : 'CREATE',
                'free_wifi_qty' => trim((string) ($rawRow['free_wifi_qty'] ?? '')) !== '' ? $rawRow['free_wifi_qty'] : ($existingBatch['free_wifi_qty'] ?? ''),
                'free_wifi_period_month' => trim((string) ($rawRow['free_wifi_period_month'] ?? '')) !== '' ? $rawRow['free_wifi_period_month'] : ($existingBatch['free_wifi_period_month'] ?? ''),
                'remark_batch_approval' => trim((string) ($rawRow['remark_batch_approval'] ?? '')) !== '' ? (string) $rawRow['remark_batch_approval'] : (string) ($existingBatch['remark_batch_approval'] ?? ''),
                'astri_initial_submitted_at' => trim((string) ($rawRow['astri_initial_submitted_at'] ?? '')) !== '' ? $rawRow['astri_initial_submitted_at'] : ($existingBatch['astri_initial_submitted_at'] ?? ''),
                'astri_batch_approved_at' => $astriBatchApprovedAt,
                'finance_submitted_at' => trim((string) ($rawRow['finance_submitted_at'] ?? '')) !== '' ? $rawRow['finance_submitted_at'] : ($existingBatch['finance_submitted_at'] ?? ''),
                'released_at' => trim((string) ($rawRow['released_at'] ?? '')) !== '' ? $rawRow['released_at'] : ($existingBatch['released_at'] ?? ''),
                'final_astri_submitted_at' => trim((string) ($rawRow['final_astri_submitted_at'] ?? '')) !== '' ? $rawRow['final_astri_submitted_at'] : ($existingBatch['final_astri_submitted_at'] ?? ''),
                'final_astri_approved_at' => trim((string) ($rawRow['final_astri_approved_at'] ?? '')) !== '' ? $rawRow['final_astri_approved_at'] : ($existingBatch['final_astri_approved_at'] ?? ''),
                'po_donasi_number' => trim((string) ($rawRow['po_donasi_number'] ?? '')) !== '' ? $rawRow['po_donasi_number'] : ($existingBatch['po_donasi_number'] ?? ''),
                'po_donasi_date' => trim((string) ($rawRow['po_donasi_date'] ?? '')) !== '' ? $rawRow['po_donasi_date'] : ($existingBatch['po_donasi_date'] ?? ''),
                'po_donasi_value' => trim((string) ($rawRow['po_donasi_value'] ?? '')) !== '' ? $rawRow['po_donasi_value'] : ($existingBatch['po_donasi_value'] ?? ''),
                'po_donasi_status' => trim((string) ($rawRow['po_donasi_status'] ?? '')) !== '' ? $rawRow['po_donasi_status'] : ($existingBatch['po_donasi_status'] ?? ''),
                'invoice_donasi_number' => trim((string) ($rawRow['invoice_donasi_number'] ?? '')) !== '' ? $rawRow['invoice_donasi_number'] : ($existingBatch['invoice_donasi_number'] ?? ''),
                'invoice_donasi_date' => trim((string) ($rawRow['invoice_donasi_date'] ?? '')) !== '' ? $rawRow['invoice_donasi_date'] : ($existingBatch['invoice_donasi_date'] ?? ''),
                'invoice_donasi_value' => trim((string) ($rawRow['invoice_donasi_value'] ?? '')) !== '' ? $rawRow['invoice_donasi_value'] : ($existingBatch['invoice_donasi_value'] ?? ''),
                'invoice_donasi_status' => trim((string) ($rawRow['invoice_donasi_status'] ?? '')) !== '' ? $rawRow['invoice_donasi_status'] : ($existingBatch['invoice_donasi_status'] ?? ''),
                'invoice_donasi_remark' => trim((string) ($rawRow['invoice_donasi_remark'] ?? '')) !== '' ? $rawRow['invoice_donasi_remark'] : ($existingBatch['invoice_donasi_remark'] ?? ''),
                'pic_name' => (string) ($rawRow['pic_name'] ?? ''),
                'pic_phone' => (string) ($rawRow['pic_phone'] ?? ''),
                'pic_position' => (string) ($rawRow['pic_position'] ?? ''),
                'pic_period' => (string) ($rawRow['pic_period'] ?? ''),
                'status' => empty($rowErrors) ? 'valid' : 'invalid',
                'message' => empty($rowErrors) ? 'Siap diimport' : implode(', ', array_unique($rowErrors)),
                'errors' => $rowErrors,
            ]);
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
        if (in_array($stagingStatus, ['REJECTED', 'HOLD', 'RELEASED'], true)) {
            return $stagingStatus;
        }

        if ($stagingStatus === 'NEED_REVISE') {
            return 'WAITING HO';
        }

        if (in_array($stagingStatus, [
            'WAITING_FINANCE_RELEASE',
            'PRE_ZEYN_DOC_APPROVED',
            'PRE_ZEYN_FINANCE_ON_REVIEW',
            'PRE_ZEYN_FINANCE_APPROVED',
        ], true)) {
            return 'WAITING FINANCE';
        }

        if (in_array($stagingStatus, [
            'WAITING_POST_ZEYN_DOC',
            'POST_ZEYN_DOC_ON_REVIEW',
            'POST_ZEYN_DOC_APPROVED',
            'POST_ZEYN_FINANCE_ON_REVIEW',
            'WAITING_ASTRI_SUBMISSION',
            'ASTRI_ON_REVIEW',
            'ASTRI_APPROVED',
            'PO_DONASI',
            'INVOICE',
            'COMPLETED',
        ], true)) {
            return 'DONE BATCH APPROVAL';
        }

        if ($stagingStatus !== '') {
            return 'WAITING HO';
        }

        return 'VALSAL';
    }

    private function normalizeDate($date)
    {
        $date = trim((string) $date);
        if ($date === '' || $date === '0000-00-00') {
            return null;
        }

        if (is_numeric($date) && (float) $date > 25000 && (float) $date < 90000) {
            $timestamp = ((float) $date - 25569) * 86400;
            return gmdate('Y-m-d', (int) $timestamp);
        }

        $monthMap = [
            'JANUARI' => 'JAN', 'FEBRUARI' => 'FEB', 'MARET' => 'MAR', 'APRIL' => 'APR',
            'MEI' => 'MAY', 'JUNI' => 'JUN', 'JULI' => 'JUL', 'AGUSTUS' => 'AUG',
            'AGU' => 'AUG', 'SEPTEMBER' => 'SEP', 'OKTOBER' => 'OCT', 'NOVEMBER' => 'NOV', 'DESEMBER' => 'DEC',
        ];
        $upperDate = strtoupper($date);
        foreach ($monthMap as $idMonth => $enMonth) {
            $upperDate = str_replace($idMonth, $enMonth, $upperDate);
        }

        $timestamp = strtotime($upperDate);
        return $timestamp ? date('Y-m-d', $timestamp) : $date;
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

    private function collectPicsFromImportRow(array $row)
    {
        $pics = [];
        for ($picNo = 1; $picNo <= 5; $picNo++) {
            $name = trim((string) ($row['pic_' . $picNo . '_name'] ?? ''));
            if ($name === '' && $picNo === 1) {
                $name = trim((string) ($row['pic_name'] ?? $row['recipient_name'] ?? ''));
            }
            if ($name === '') {
                continue;
            }

            $pics[] = [
                'pic_no' => $picNo,
                'pic_name' => $name,
                'pic_phone' => trim((string) ($row['pic_' . $picNo . '_phone'] ?? ($picNo === 1 ? ($row['pic_phone'] ?? $row['recipient_phone'] ?? '') : ''))) ?: null,
                'pic_position' => trim((string) ($row['pic_' . $picNo . '_position'] ?? ($picNo === 1 ? ($row['pic_position'] ?? $row['recipient_position'] ?? '') : ''))) ?: null,
                'pic_period' => trim((string) ($row['pic_' . $picNo . '_period'] ?? ($picNo === 1 ? ($row['pic_period'] ?? $row['recipient_period'] ?? '') : ''))) ?: null,
                'is_primary' => $picNo === 1 ? 1 : 0,
            ];
        }

        return $pics;
    }

    private function importDonationDocuments($clusterId, array $row, $userId)
    {
        if (!$this->MBatch_Approval_MyRep->batchDocumentTablesReady()) {
            return;
        }

        $groups = [
            'pre_doc' => 'PRE_ZEYN',
            'post_doc' => 'POST_ZEYN',
        ];

        foreach ($groups as $prefix => $groupKey) {
            $documents = $this->MBatch_Approval_MyRep->getDonationDocumentRows((int) $clusterId, $groupKey);
            foreach ($documents as $index => $document) {
                $docNo = $index + 1;
                $filePath = trim((string) ($row[$prefix . '_' . $docNo . '_file_path'] ?? ''));
                $fileName = trim((string) ($row[$prefix . '_' . $docNo . '_file_name'] ?? ''));
                $status = $this->normalizeImportedDocumentStatus($row[$prefix . '_' . $docNo . '_status'] ?? '');
                $financeStatus = $this->normalizeImportedDocumentStatus($row[$prefix . '_' . $docNo . '_finance_status'] ?? '');
                $astriStatus = $prefix === 'post_doc'
                    ? $this->normalizeImportedAstriStatus($row['astri_doc_' . $docNo . '_status'] ?? '')
                    : '';
                if ($prefix === 'post_doc' && $status === '' && in_array($astriStatus, ['ON REVIEW', 'APPROVED', 'REJECTED'], true)) {
                    $status = 'APPROVED';
                }
                if ($filePath === '' && $fileName === '' && $status === '' && $financeStatus === '' && $astriStatus === '') {
                    continue;
                }

                if ($status === '' && ($filePath !== '' || $fileName !== '')) {
                    $status = 'UPLOADED';
                }
                if ($status === '') {
                    continue;
                }
                if ($fileName === '' && $filePath !== '') {
                    $fileName = basename(str_replace('\\', '/', $filePath));
                }

                $fileId = $this->MBatch_Approval_MyRep->saveDonationFileUpload((int) $clusterId, (int) ($document['id_doc_item'] ?? 0), [
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'status_file' => $status,
                    'remark' => (string) ($row[$prefix . '_' . $docNo . '_remark'] ?? ''),
                    'uploaded_by' => (int) $userId,
                    'uploaded_at' => $this->normalizeDateTimeInput($row[$prefix . '_' . $docNo . '_uploaded_at'] ?? '') ?: date('Y-m-d H:i:s'),
                    'preserve_existing_file' => 1,
                ]);

                if ($fileId > 0 && $status === 'APPROVED') {
                    $this->MBatch_Approval_MyRep->updateBatchFileStatus($fileId, [
                        'status_file' => 'APPROVED',
                        'remark' => (string) ($row[$prefix . '_' . $docNo . '_remark'] ?? ''),
                        'approved_by' => (int) $userId,
                    ]);
                    if (in_array($financeStatus, ['APPROVED', 'REJECTED'], true)) {
                        $financeReviewedAt = $this->normalizeDateTimeInput($row[$prefix . '_' . $docNo . '_finance_approved_at'] ?? '')
                            ?: $this->normalizeDateTimeInput($row[$prefix . '_' . $docNo . '_uploaded_at'] ?? '');
                        $this->MBatch_Approval_MyRep->updateDonationFinanceStatus($fileId, $financeStatus, (int) $userId, (string) ($row[$prefix . '_' . $docNo . '_finance_remark'] ?? ''), $financeReviewedAt);
                    }
                }

                if ($fileId > 0 && $prefix === 'post_doc') {
                    if ($astriStatus !== '') {
                        $this->MBatch_Approval_MyRep->updateDonationAstriStatus($fileId, [
                            'astri_status' => $astriStatus,
                            'astri_submitted_date' => $this->normalizeDate($row['astri_doc_' . $docNo . '_submitted_date'] ?? ''),
                            'astri_approved_date' => $this->normalizeDate($row['astri_doc_' . $docNo . '_approved_date'] ?? ''),
                            'astri_remark' => (string) ($row['astri_doc_' . $docNo . '_remark'] ?? ''),
                            'updated_by' => (int) $userId,
                        ]);
                    }
                }
            }
        }
    }

    private function normalizeImportedDocumentStatus($status)
    {
        $status = strtoupper(trim((string) $status));
        if ($status === '') {
            return '';
        }

        $status = str_replace(['-', '_'], ' ', $status);
        $status = preg_replace('/\s+/', ' ', $status);
        if (in_array($status, ['DONE', 'COMPLY', 'APPROVE', 'APPROVED', 'OK', 'FULL APPROVED'], true)) {
            return 'APPROVED';
        }
        if (in_array($status, ['ON REVIEW', 'UPLOADED', 'UPLOAD', 'SUBMIT', 'SUBMITTED'], true)) {
            return 'UPLOADED';
        }
        if (in_array($status, ['REJECT', 'REJECTED', 'REVISI', 'REVISION', 'CANCEL', 'CANCELED', 'CANCELLED'], true)) {
            return 'REJECTED';
        }
        if (in_array($status, ['NY', 'N Y', 'NOT YET', 'BELUM', 'BELUM UPLOAD', 'NOT UPLOADED'], true)) {
            return '';
        }

        return '';
    }

    private function normalizeImportedAstriStatus($status)
    {
        $status = strtoupper(trim((string) $status));
        if ($status === '') {
            return '';
        }

        $status = str_replace(['-', '_'], ' ', $status);
        $status = preg_replace('/\s+/', ' ', $status);
        if (in_array($status, ['DONE', 'COMPLY', 'APPROVE', 'APPROVED', 'OK', 'FULL APPROVED'], true)) {
            return 'APPROVED';
        }
        if (in_array($status, ['ASTRI', 'ON REVIEW', 'REVIEW', 'UPLOADED', 'UPLOAD', 'SUBMIT', 'SUBMITTED'], true)) {
            return 'ON REVIEW';
        }
        if (in_array($status, ['REJECT', 'REJECTED', 'REVISI', 'REVISION', 'CANCEL', 'CANCELED', 'CANCELLED'], true)) {
            return 'REJECTED';
        }
        if (in_array($status, ['NY', 'N Y', 'NOT YET', 'BELUM', 'BELUM UPLOAD', 'NOT UPLOADED'], true)) {
            return 'NY';
        }

        return '';
    }

    private function importDonationPoInvoice($clusterId, $batchId, array $row, $userId)
    {
        $poNumber = trim((string) ($row['po_donasi_number'] ?? ''));
        if ($poNumber === '') {
            return;
        }

        $poValue = $this->normalizeNullableNumber($row['po_donasi_value'] ?? '');
        $invoiceNumber = trim((string) ($row['invoice_donasi_number'] ?? ''));
        $invoiceValue = $this->normalizeNullableNumber($row['invoice_donasi_value'] ?? '');

        $result = $this->MBatch_Approval_MyRep->saveDonationPoInvoice((int) $clusterId, (int) $batchId, [
            'staging_status' => $invoiceNumber !== '' ? 'INVOICE' : 'PO_DONASI',
            'po_donasi_number' => $poNumber,
            'po_donasi_date' => $this->normalizeDate($row['po_donasi_date'] ?? ''),
            'po_donasi_value' => $poValue,
            'po_donasi_status' => trim((string) ($row['po_donasi_status'] ?? '')) ?: 'ISSUED',
            'invoice_donasi_number' => $invoiceNumber !== '' ? $invoiceNumber : null,
            'invoice_donasi_date' => $this->normalizeDate($row['invoice_donasi_date'] ?? ''),
            'invoice_donasi_value' => $invoiceValue,
            'invoice_donasi_status' => trim((string) ($row['invoice_donasi_status'] ?? '')) ?: ($invoiceNumber !== '' ? 'BILLED' : null),
            'invoice_donasi_remark' => trim((string) ($row['invoice_donasi_remark'] ?? '')) ?: null,
            'updated_by' => (int) $userId,
        ], [
            'po_number' => $poNumber,
            'po_date' => $this->normalizeDate($row['po_donasi_date'] ?? ''),
            'po_value' => $poValue ?: 0,
            'status_po' => trim((string) ($row['po_donasi_status'] ?? '')) ?: 'ISSUED',
            'remark_po' => 'PT EMR - DONASI',
            'created_by' => (int) $userId,
            'updated_by' => (int) $userId,
        ], [
            'termin_value' => $poValue ?: 0,
            'status_termin' => $invoiceNumber !== '' ? 'BILLED' : 'READY BILLING',
            'invoice_number' => $invoiceNumber !== '' ? $invoiceNumber : null,
            'invoice_date' => $this->normalizeDate($row['invoice_donasi_date'] ?? ''),
            'invoice_value' => $invoiceValue,
            'remark_termin' => trim((string) ($row['invoice_donasi_remark'] ?? '')),
            'created_by' => (int) $userId,
            'updated_by' => (int) $userId,
        ]);

        if ($result && $poNumber !== '') {
            $this->load->model('MPO_Monitor');
            $this->MPO_Monitor->syncMyRepClaimsForPoNumber($poNumber, (int) $userId, true);
        }
    }

    private function setDonationStageFromSystem($clusterId, $stage, array $extraBatchPayload = [])
    {
        $batch = $this->MBatch_Approval_MyRep->getBatchByClusterId((int) $clusterId);
        if (empty($batch['id_batch_approval'])) {
            return false;
        }

        $stage = $this->normalizeStagingStatus($stage, false);
        $batchPayload = array_merge([
            'staging_status' => $stage,
            'updated_by' => (int) $this->session->userdata('id_user'),
        ], $extraBatchPayload);

        return $this->MBatch_Approval_MyRep->updateBatchStage(
            (int) $clusterId,
            (int) $batch['id_batch_approval'],
            $batchPayload,
            [
                'status_current' => $this->mapClusterStatusFromStaging($stage),
                'updated_by' => (int) $this->session->userdata('id_user'),
            ]
        );
    }

    private function syncDonationUploadReviewStage($clusterId, $groupKeyOrLabel)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return false;
        }

        $groupKeyOrLabel = strtoupper(trim((string) $groupKeyOrLabel));
        $batch = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        if (empty($batch)) {
            return false;
        }

        $currentStage = strtoupper(trim((string) ($batch['staging_status'] ?? '')));
        $summary = $this->MBatch_Approval_MyRep->getDonationDocumentSummary($clusterId);

        if (in_array($groupKeyOrLabel, ['PRE_ZEYN', 'PRE ZEYN DOCUMENT'], true)
            && in_array($currentStage, ['BATCH_APPROVED', 'PRE_ZEYN_DOC_APPROVED', 'PRE_ZEYN_FINANCE_ON_REVIEW', 'PRE_ZEYN_FINANCE_APPROVED', 'WAITING_FINANCE_RELEASE'], true)) {
            $preSummary = $summary['PRE_ZEYN'] ?? [];
            $required = (int) ($preSummary['required'] ?? 0);
            if ($required > 0
                && (int) ($preSummary['uploaded'] ?? 0) >= $required
                && (int) ($preSummary['approved'] ?? 0) < $required) {
                $synced = $this->setDonationStageFromSystem($clusterId, 'PRE_ZEYN_DOC_ON_REVIEW');
                if ($synced) {
                    $clusterDetail = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
                    $this->sendBatchNotification('full_upload', $clusterDetail, 'On Review Dokumen Tahap 1');
                }
                return $synced;
            }
        }

        if (in_array($groupKeyOrLabel, ['POST_ZEYN', 'POST PAYMENT ZEYN DOCUMENT'], true)
            && in_array($currentStage, ['PRE_ZEYN_FINANCE_APPROVED', 'RELEASED', 'WAITING_POST_ZEYN_DOC', 'POST_ZEYN_DOC_APPROVED', 'POST_ZEYN_FINANCE_ON_REVIEW', 'WAITING_ASTRI_SUBMISSION', 'ASTRI_ON_REVIEW'], true)) {
            if ($this->isPostDonationActionLocked($clusterId, $groupKeyOrLabel)) {
                return false;
            }
            $postSummary = $summary['POST_ZEYN'] ?? [];
            $required = (int) ($postSummary['required'] ?? 0);
            if ($required > 0
                && (int) ($postSummary['uploaded'] ?? 0) >= $required
                && (int) ($postSummary['approved'] ?? 0) < $required) {
                $synced = $this->setDonationStageFromSystem($clusterId, 'POST_ZEYN_DOC_ON_REVIEW');
                if ($synced) {
                    $clusterDetail = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
                    $this->sendBatchNotification('full_upload', $clusterDetail, 'On Review Dokumen Tahap 2');
                }
                return $synced;
            }
        }

        return false;
    }

    private function syncDonationApprovalStage($clusterId, $groupKeyOrLabel)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return false;
        }

        $groupKeyOrLabel = strtoupper(trim((string) $groupKeyOrLabel));
        $batch = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        if (empty($batch)) {
            return false;
        }

        $currentStage = strtoupper(trim((string) ($batch['staging_status'] ?? '')));
        $summary = $this->MBatch_Approval_MyRep->getDonationDocumentSummary($clusterId);

        if (in_array($groupKeyOrLabel, ['PRE_ZEYN', 'PRE ZEYN DOCUMENT'], true)
            && in_array($currentStage, ['BATCH_APPROVED', 'PRE_ZEYN_DOC_ON_REVIEW', 'PRE_ZEYN_DOC_APPROVED'], true)) {
            $preSummary = $summary['PRE_ZEYN'] ?? [];
            $required = (int) ($preSummary['required'] ?? 0);
            if ($required > 0 && (int) ($preSummary['approved'] ?? 0) >= $required) {
                return $this->setDonationStageFromSystem($clusterId, 'PRE_ZEYN_FINANCE_ON_REVIEW', [
                    'pre_zeyn_doc_approved_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if (in_array($groupKeyOrLabel, ['POST_ZEYN', 'POST PAYMENT ZEYN DOCUMENT'], true)
            && in_array($currentStage, ['PRE_ZEYN_FINANCE_APPROVED', 'RELEASED', 'WAITING_POST_ZEYN_DOC', 'POST_ZEYN_DOC_ON_REVIEW', 'POST_ZEYN_DOC_APPROVED'], true)) {
            if ($this->isPostDonationActionLocked($clusterId, $groupKeyOrLabel)) {
                return false;
            }
            $postSummary = $summary['POST_ZEYN'] ?? [];
            $required = (int) ($postSummary['required'] ?? 0);
            if ($required > 0 && (int) ($postSummary['approved'] ?? 0) >= $required) {
                $financeRequired = (int) ($postSummary['finance_required'] ?? $required);
                if ($financeRequired > 0 && (int) ($postSummary['finance_approved'] ?? 0) >= $financeRequired) {
                    return $this->setDonationStageFromSystem(
                        $clusterId,
                        (int) ($postSummary['astri_rejected'] ?? 0) > 0 ? 'ASTRI_ON_REVIEW' : 'WAITING_ASTRI_SUBMISSION',
                        ['post_zeyn_doc_approved_at' => date('Y-m-d H:i:s')]
                    );
                }
                return $this->setDonationStageFromSystem($clusterId, 'POST_ZEYN_FINANCE_ON_REVIEW', [
                    'post_zeyn_doc_approved_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return false;
    }

    private function syncDonationFinanceApprovalStage($clusterId, $groupKeyOrLabel)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return false;
        }

        $groupKeyOrLabel = strtoupper(trim((string) $groupKeyOrLabel));
        $batch = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        if (empty($batch)) {
            return false;
        }

        $currentStage = strtoupper(trim((string) ($batch['staging_status'] ?? '')));

        if (in_array($groupKeyOrLabel, ['PRE_ZEYN', 'PRE ZEYN DOCUMENT'], true)
            && in_array($currentStage, ['BATCH_APPROVED', 'PRE_ZEYN_DOC_APPROVED', 'PRE_ZEYN_FINANCE_ON_REVIEW'], true)
            && $this->MBatch_Approval_MyRep->areDonationRequiredDocumentsFinanceApproved($clusterId, 'PRE_ZEYN')) {
            return $this->setDonationStageFromSystem($clusterId, 'PRE_ZEYN_FINANCE_APPROVED');
        }

        if (in_array($groupKeyOrLabel, ['POST_ZEYN', 'POST PAYMENT ZEYN DOCUMENT'], true)
            && in_array($currentStage, ['PRE_ZEYN_FINANCE_APPROVED', 'POST_ZEYN_DOC_ON_REVIEW', 'POST_ZEYN_DOC_APPROVED', 'POST_ZEYN_FINANCE_ON_REVIEW', 'RELEASED'], true)
            && $this->MBatch_Approval_MyRep->areDonationRequiredDocumentsFinanceApproved($clusterId, 'POST_ZEYN')) {
            if ($this->isPostDonationActionLocked($clusterId, $groupKeyOrLabel)) {
                return false;
            }
            return $this->setDonationStageFromSystem($clusterId, 'WAITING_ASTRI_SUBMISSION', [
                'post_zeyn_doc_approved_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return false;
    }

    private function isPostDonationActionLocked($clusterId, $groupKeyOrLabel)
    {
        $groupKeyOrLabel = strtoupper(trim((string) $groupKeyOrLabel));
        if (!in_array($groupKeyOrLabel, ['POST_ZEYN', 'POST PAYMENT ZEYN DOCUMENT'], true)) {
            return false;
        }

        $batch = $this->MBatch_Approval_MyRep->getBatchByClusterId((int) $clusterId);
        if (empty($batch)) {
            return true;
        }

        return trim((string) ($batch['released_at'] ?? '')) === ''
            || (float) ($batch['nominal_release_finance'] ?? 0) <= 0;
    }

    private function handleDonationAjaxOrRedirect($success, $message, $redirectPath)
    {
        if ($this->isAjaxRequest()) {
            $this->jsonResponse((bool) $success, (string) $message, base_url((string) $redirectPath));
            return;
        }

        $this->session->set_flashdata($success ? 'success' : 'error', (string) $message);
        redirect($redirectPath);
    }

    private function resolveDonationAllowedTypes($docName, $groupLabel)
    {
        $docName = strtoupper(trim((string) $docName));
        $groupLabel = strtoupper(trim((string) $groupLabel));
        if (strpos($docName, 'SCREENSHOT') !== false) {
            return 'jpg|jpeg|png';
        }
        if ($groupLabel === 'POST PAYMENT ZEYN DOCUMENT') {
            return 'pdf';
        }

        return 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png|rar|zip';
    }

    private function resolveStageTimestamp($existingTimestamp, $shouldSet)
    {
        if (!$shouldSet) {
            return $existingTimestamp;
        }

        return !empty($existingTimestamp) ? $existingTimestamp : date('Y-m-d H:i:s');
    }

    private function validateDonationStageGate($clusterId, $targetStage)
    {
        $targetStage = strtoupper(trim((string) $targetStage));
        if (in_array($targetStage, ['WAITING_FINANCE_RELEASE', 'RELEASED'], true)
            && !$this->MBatch_Approval_MyRep->areDonationRequiredDocumentsFinanceApproved((int) $clusterId, 'PRE_ZEYN')) {
            return 'Status belum bisa lanjut ke finance/release karena 9 dokumen pra-finance belum full approved Finance.';
        }

        if (in_array($targetStage, ['POST_ZEYN_DOC_APPROVED', 'WAITING_ASTRI_SUBMISSION', 'ASTRI_ON_REVIEW'], true)
            && !$this->MBatch_Approval_MyRep->areDonationRequiredDocumentsFinanceApproved((int) $clusterId, 'POST_ZEYN')) {
            return 'Status belum bisa lanjut ke Astri karena 6 dokumen setelah pembayaran belum full approved Finance.';
        }

        $donationSummary = $this->MBatch_Approval_MyRep->getDonationDocumentSummary((int) $clusterId);
        $totalAstriRejected = (int) ($donationSummary['PRE_ZEYN']['astri_rejected'] ?? 0)
            + (int) ($donationSummary['POST_ZEYN']['astri_rejected'] ?? 0);
        if (in_array($targetStage, ['ASTRI_APPROVED', 'PO_DONASI', 'INVOICE'], true)
            && $totalAstriRejected > 0) {
            return 'Status belum bisa lanjut ke PO/Invoice karena masih ada dokumen Astri rejected.';
        }

        if (in_array($targetStage, ['ASTRI_APPROVED', 'PO_DONASI', 'INVOICE'], true)
            && !$this->MBatch_Approval_MyRep->areAllDonationDocumentsAstriApproved((int) $clusterId)) {
            return 'Status belum bisa lanjut ke PO/Invoice karena dokumen Astri belum full approved.';
        }

        return '';
    }

    public function deleteCluster()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Data Batch Approval tidak valid.');
            redirect('Batch_Approval_MyRep');
            return;
        }

        $deleted = $this->MBatch_Approval_MyRep->deleteBatchApprovalOnly($clusterId);
        $message = $deleted
            ? 'Data Batch Approval berhasil dihapus. Cluster MyRep tetap tersimpan.'
            : ($this->MBatch_Approval_MyRep->getLastErrorMessage() ?: 'Gagal menghapus data Batch Approval.');
        $this->session->set_flashdata($deleted ? 'success' : 'error', $message);
        redirect('Batch_Approval_MyRep');
    }

    private function isApprover()
    {
        if ($this->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        if ($this->session->userdata('lokasi_user') !== 'HO') {
            return false;
        }

        if (isset($this->myrepAccess)) {
            return $this->myrepAccess->hasPermission('Batch_Approval_MyRep', 'APPROVAL');
        }

        return true;
    }

    private function jsonDataTableResponse($recordsTotal, $recordsFiltered, array $data, array $extra = [])
    {
        $payload = [
            'draw' => (int) $this->input->post('draw'),
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $data,
        ];
        if (!empty($extra)) {
            $payload = array_merge($payload, $extra);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function buildBatchApprovalTableRow(array $row, $no, array $clusterReviewPicMap)
    {
        $hasBatch = (int) ($row['id_batch_approval'] ?? 0) > 0;
        $stageCode = strtoupper(trim((string) ($row['display_staging_status'] ?? $row['staging_status'] ?? 'DRAFT')));
        $stageLabel = $hasBatch ? $this->getIndonesianStagingLabel($stageCode) : $this->getIndonesianStagingLabel('WAITING INPUT');
        $isWaitingInputStage = !$hasBatch || $stageCode === 'WAITING INPUT';
        $batchDocLabel = $hasBatch ? $this->getBatchListDocLabel($row) : 'BELUM ADA DOC';
        $uploadBy = trim((string) ($row['batch_doc_uploaded_by_name'] ?? ''));
        $picApproval = trim((string) ($clusterReviewPicMap[(int) ($row['id_myrep_cluster'] ?? 0)] ?? ''));
        $batchPics = $hasBatch ? (array) $this->MBatch_Approval_MyRep->getBatchPics((int) ($row['id_batch_approval'] ?? 0)) : [];
        $myrepPicNames = [];
        foreach ($batchPics as $batchPic) {
            $picName = trim((string) ($batchPic['pic_name'] ?? ''));
            if ($picName !== '') {
                $myrepPicNames[] = $picName;
            }
        }

        $nominalRelease = $row['nominal_release_finance'] ?? null;
        $hasReleaseNominal = $nominalRelease !== null && $nominalRelease !== '';
        $useReleaseNominal = in_array($stageCode, ['RELEASED', 'DONE BATCH APPROVAL', 'COMPLETED'], true) && $hasReleaseNominal;
        $displayNominalDonasi = $useReleaseNominal ? (float) $nominalRelease : (float) ($row['nominal_pengajuan_area'] ?? 0);
        $hpDonasi = (float) ($row['hp_donasi'] ?? 0);
        $displayNominalPerHomepass = $hpDonasi > 0 ? $displayNominalDonasi / $hpDonasi : null;
        $slaInfo = $this->getBatchListSlaInfo($row);
        $canEdit = $hasBatch && $this->canEditBatchApprovalDetail($row);
        $canHapus = $hasBatch && $this->hasBatchPermission('HAPUS');

        $clusterName = htmlspecialchars((string) ($row['cluster_name'] ?? '-'), ENT_QUOTES, 'UTF-8');
        if (!empty($row['id_myrep_cluster']) && !$isWaitingInputStage) {
            $clusterHtml = '<a href="' . base_url('Batch_Approval_MyRep/detail/' . (int) $row['id_myrep_cluster']) . '" class="font-weight-bold">' . $clusterName . '</a>';
        } else {
            $clusterHtml = '<strong>' . $clusterName . '</strong>';
        }
        if (!empty($row['cluster_code'])) {
            $clusterHtml .= '<div class="text-muted small">' . htmlspecialchars((string) $row['cluster_code'], ENT_QUOTES, 'UTF-8') . '</div>';
        }

        $picHtml = '<div class="batch-pic-summary">'
            . '<div><strong>Area:</strong> ' . htmlspecialchars($uploadBy !== '' ? $uploadBy : '-', ENT_QUOTES, 'UTF-8') . '</div>'
            . '<div><strong>HO:</strong> ' . htmlspecialchars($picApproval !== '' ? $picApproval : '-', ENT_QUOTES, 'UTF-8') . '</div>'
            . '<div><strong>MyRep:</strong> ' . htmlspecialchars(!empty($myrepPicNames) ? implode(', ', array_unique($myrepPicNames)) : '-', ENT_QUOTES, 'UTF-8') . '</div>'
            . '</div>';

        $docHtml = '<div class="batch-doc-status-stack"><div class="batch-doc-status-stack__item">'
            . '<span class="batch-doc-name">RAR:</span> '
            . '<span class="badge badge-' . $this->getBatchListBadgeClass($batchDocLabel) . ' batch-doc-status-badge">'
            . htmlspecialchars($batchDocLabel, ENT_QUOTES, 'UTF-8')
            . '</span></div></div>';

        $slaHtml = '<div class="batch-sla-aging-cell">'
            . '<span class="badge badge-' . $this->getBatchListSlaBadgeClass($slaInfo) . '">SLA 17 Hari</span> '
            . '<span class="badge badge-' . $this->getBatchListAgingBadgeClass($slaInfo['aging_days']) . '">'
            . (($slaInfo['aging_days'] === null) ? 'Aging -' : 'Aging ' . (int) $slaInfo['aging_days'] . ' Hari')
            . '</span></div>';

        $actionHtml = '';
        if ($hasBatch) {
            if ($canEdit) {
                $actionHtml .= $this->buildBatchApprovalEditButton($row, $batchPics);
            }
            $actionHtml .= ' <a href="' . base_url('Batch_Approval_MyRep/detail/' . (int) $row['id_myrep_cluster']) . '" class="btn btn-sm btn-outline-secondary mt-1">Detail</a>';
            if ($canHapus) {
                $actionHtml .= ' <form method="post" action="' . base_url('Batch_Approval_MyRep/deleteCluster') . '" class="d-inline" onsubmit="return confirm(\'Hapus data Batch Approval ini? Cluster MyRep tetap tersimpan.\');">'
                    . '<input type="hidden" name="cluster_id" value="' . (int) $row['id_myrep_cluster'] . '">'
                    . '<button type="submit" class="btn btn-sm btn-outline-danger mt-1">Hapus Batch</button>'
                    . '</form>';
            }
        } elseif ($this->hasBatchPermission('TAMBAH')) {
            $actionHtml = '<button type="button" class="btn btn-sm btn-outline-primary js-start-batch" data-toggle="modal" data-target="#modal-batch-create" data-cluster_id="' . (int) $row['id_myrep_cluster'] . '" data-city_name="' . htmlspecialchars((string) ($row['city_name'] ?? ''), ENT_QUOTES, 'UTF-8') . '">Input Batch</button>';
        } else {
            $actionHtml = '<span class="text-muted small">Menunggu proses tahap berikutnya</span>';
        }

        return [
            $no,
            $clusterHtml,
            htmlspecialchars((string) ($row['regional_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars((string) ($row['city_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
            number_format($hpDonasi, 0, ',', '.'),
            number_format($displayNominalDonasi, 0, ',', '.'),
            $displayNominalPerHomepass !== null ? number_format($displayNominalPerHomepass, 0, ',', '.') : '-',
            $slaHtml,
            '<span class="badge badge-' . $this->getBatchListBadgeClass($stageCode) . '">' . htmlspecialchars($stageLabel, ENT_QUOTES, 'UTF-8') . '</span>',
            $picHtml,
            $docHtml,
            '<span class="badge badge-' . $this->getBatchListBadgeClass($row['status_current'] ?? 'DRAFT') . '">' . htmlspecialchars((string) ($row['status_current'] ?? 'DRAFT'), ENT_QUOTES, 'UTF-8') . '</span>',
            $actionHtml,
        ];
    }

    private function calculateBatchApprovalTableTotals(array $rows)
    {
        $totals = [
            'hp_donasi' => 0,
            'nominal_donasi' => 0,
            'nominal_per_homepass' => 0,
        ];

        foreach ($rows as $row) {
            $stageCode = strtoupper(trim((string) ($row['display_staging_status'] ?? $row['staging_status'] ?? 'DRAFT')));
            $nominalRelease = $row['nominal_release_finance'] ?? null;
            $hasReleaseNominal = $nominalRelease !== null && $nominalRelease !== '';
            $useReleaseNominal = in_array($stageCode, ['RELEASED', 'DONE BATCH APPROVAL', 'COMPLETED'], true) && $hasReleaseNominal;
            $nominalDonasi = $useReleaseNominal ? (float) $nominalRelease : (float) ($row['nominal_pengajuan_area'] ?? 0);
            $hpDonasi = (float) ($row['hp_donasi'] ?? 0);

            $totals['hp_donasi'] += $hpDonasi;
            $totals['nominal_donasi'] += $nominalDonasi;
        }

        $totals['nominal_per_homepass'] = $totals['hp_donasi'] > 0
            ? $totals['nominal_donasi'] / $totals['hp_donasi']
            : 0;

        return $totals;
    }

    private function sortBatchApprovalTableRows(array $rows, array $order)
    {
        $column = isset($order['column']) ? (int) $order['column'] : 0;
        $dir = strtolower((string) ($order['dir'] ?? 'asc')) === 'desc' ? -1 : 1;
        if ($column <= 0) {
            return $rows;
        }

        usort($rows, function ($left, $right) use ($column, $dir) {
            $leftValue = $this->getBatchApprovalSortValue($left, $column);
            $rightValue = $this->getBatchApprovalSortValue($right, $column);

            if (is_numeric($leftValue) && is_numeric($rightValue)) {
                $result = $leftValue <=> $rightValue;
            } else {
                $result = strnatcasecmp((string) $leftValue, (string) $rightValue);
            }

            return $result * $dir;
        });

        return $rows;
    }

    private function getBatchApprovalSortValue(array $row, $column)
    {
        $stageCode = strtoupper(trim((string) ($row['display_staging_status'] ?? $row['staging_status'] ?? 'DRAFT')));
        $nominalRelease = $row['nominal_release_finance'] ?? null;
        $hasReleaseNominal = $nominalRelease !== null && $nominalRelease !== '';
        $useReleaseNominal = in_array($stageCode, ['RELEASED', 'DONE BATCH APPROVAL', 'COMPLETED'], true) && $hasReleaseNominal;
        $nominalDonasi = $useReleaseNominal ? (float) $nominalRelease : (float) ($row['nominal_pengajuan_area'] ?? 0);
        $hpDonasi = (float) ($row['hp_donasi'] ?? 0);

        switch ((int) $column) {
            case 1:
                return $row['cluster_name'] ?? '';
            case 2:
                return $row['regional_name'] ?? '';
            case 3:
                return $row['city_name'] ?? '';
            case 4:
                return $hpDonasi;
            case 5:
                return $nominalDonasi;
            case 6:
                return $hpDonasi > 0 ? $nominalDonasi / $hpDonasi : 0;
            case 8:
                return $this->getIndonesianStagingLabel($stageCode);
            case 11:
                return $row['status_current'] ?? '';
            default:
                return $row['created_at'] ?? '';
        }
    }

    private function buildBatchApprovalEditButton(array $row, array $batchPics)
    {
        $attrs = [
            'id_myrep_cluster' => (int) ($row['id_myrep_cluster'] ?? 0),
            'id_batch_approval' => (int) ($row['id_batch_approval'] ?? 0),
            'cluster_name' => (string) ($row['cluster_name'] ?? ''),
            'regional_name' => (string) ($row['regional_name'] ?? ''),
            'province_name' => (string) ($row['province_name'] ?? ''),
            'city_name' => (string) ($row['city_name'] ?? ''),
            'district_name' => (string) ($row['district_name'] ?? ''),
            'village_name' => (string) ($row['village_name'] ?? ''),
            'submission_date' => (string) ($row['submission_date'] ?? ''),
            'homepass_valsal' => (int) ($row['homepass_valsal'] ?? 0),
            'hp_donasi' => (int) ($row['hp_donasi'] ?? 0),
            'nominal_pengajuan_area' => (string) ($row['nominal_pengajuan_area'] ?? ''),
            'nominal_nego_emr' => (string) ($row['nominal_nego_emr'] ?? ''),
            'nominal_release_finance' => (string) ($row['nominal_release_finance'] ?? ''),
            'bank_name' => (string) ($row['bank_name'] ?? ''),
            'bank_account_number' => (string) ($row['bank_account_number'] ?? ''),
            'recipient_name' => (string) ($row['recipient_name'] ?? ''),
            'recipient_phone' => (string) ($row['recipient_phone'] ?? ''),
            'recipient_position' => (string) ($row['recipient_position'] ?? ''),
            'recipient_period' => (string) ($row['recipient_period'] ?? ''),
            'free_wifi_qty' => (string) ($row['free_wifi_qty'] ?? ''),
            'free_wifi_period_month' => (string) ($row['free_wifi_period_month'] ?? ''),
            'astri_batch_number' => (string) ($row['astri_batch_number'] ?? ''),
            'staging_status' => (string) ($row['staging_status'] ?? 'DRAFT'),
            'remark_batch_approval' => (string) ($row['remark_batch_approval'] ?? ''),
        ];

        $html = '<button type="button" class="btn btn-sm btn-outline-primary js-edit-batch" data-toggle="modal" data-target="#modal-batch-edit" data-role-guard-exempt="1"';
        foreach ($attrs as $key => $value) {
            $html .= ' data-' . $key . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
        }
        $html .= " data-pics='" . htmlspecialchars(json_encode($batchPics), ENT_QUOTES, 'UTF-8') . "'>Edit</button>";

        return $html;
    }

    private function isNyDrmBatchRow(array $row)
    {
        $currentStatus = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
        $stage = strtoupper(trim((string) ($row['display_staging_status'] ?? $row['staging_status'] ?? 'DRAFT')));
        $hasBatch = (int) ($row['id_batch_approval'] ?? 0) > 0;

        return !in_array($currentStatus, ['DRM', 'RFS', 'ATP', 'DONE'], true)
            && (!$hasBatch || (!in_array($stage, ['COMPLETED', 'INVOICE'], true) && $stage !== 'REJECTED'));
    }

    private function getBatchListDocLabel(array $row)
    {
        if ((int) ($row['batch_doc_not_required'] ?? 0) === 1) {
            return 'TIDAK BUTUH DOKUMENT';
        }

        $status = strtoupper(trim((string) ($row['batch_doc_status'] ?? '')));
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        if ($status !== '') {
            return $status;
        }

        return !empty($row['batch_doc_file_name']) ? 'UPLOADED' : 'BELUM UPLOAD';
    }

    private function getBatchListSlaInfo(array $row)
    {
        $approvedValsalDate = trim((string) ($row['valsal_approved_at'] ?? ''));
        if ($approvedValsalDate === '') {
            $approvedValsalDate = trim((string) ($row['valsal_date'] ?? ''));
        }

        if ($approvedValsalDate === '' || $approvedValsalDate === '0000-00-00') {
            return ['start_date' => null, 'aging_days' => null];
        }

        $approvalEmrDate = trim((string) ($row['submitted_to_finance_at'] ?? ''));
        return [
            'start_date' => substr($approvedValsalDate, 0, 10),
            'aging_days' => $this->countBatchListCalendarDays($approvedValsalDate, $approvalEmrDate !== '' ? $approvalEmrDate : date('Y-m-d')),
        ];
    }

    private function countBatchListCalendarDays($startDateString, $endDateString = null)
    {
        if (empty($startDateString) || $startDateString === '0000-00-00') {
            return null;
        }

        try {
            $start = new DateTimeImmutable(substr((string) $startDateString, 0, 10));
            $end = new DateTimeImmutable(substr((string) ($endDateString ?: date('Y-m-d')), 0, 10));
        } catch (Exception $e) {
            return null;
        }

        return $start > $end ? 0 : (int) $start->diff($end)->days;
    }

    private function getBatchListSlaBadgeClass(array $slaInfo)
    {
        if (($slaInfo['aging_days'] ?? null) === null) {
            return 'secondary';
        }

        return (int) $slaInfo['aging_days'] > 17 ? 'danger' : 'success';
    }

    private function getBatchListAgingBadgeClass($agingDays)
    {
        if ($agingDays === null) {
            return 'secondary';
        }

        return (int) $agingDays > 17 ? 'danger' : 'success';
    }

    private function getBatchListBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'APPROVED':
            case 'RELEASED':
            case 'DONE BATCH APPROVAL':
            case 'COMPLETED':
            case 'BATCH_APPROVED':
            case 'PRE_ZEYN_DOC_APPROVED':
            case 'PRE_ZEYN_FINANCE_APPROVED':
            case 'POST_ZEYN_DOC_APPROVED':
            case 'ASTRI_APPROVED':
            case 'PO_DONASI':
            case 'INVOICE':
                return 'success';
            case 'WAITING INPUT':
            case 'WAITING_BATCH_APPROVAL':
            case 'WAITING_ASTRI_SUBMISSION':
                return 'info';
            case 'HOLD':
            case 'WAITING DOC':
            case 'WAITING_PRE_ZEYN_DOC':
            case 'PRE_ZEYN_DOC_ON_REVIEW':
            case 'PRE_ZEYN_FINANCE_ON_REVIEW':
            case 'WAITING_FINANCE_RELEASE':
            case 'WAITING_POST_ZEYN_DOC':
            case 'POST_ZEYN_DOC_ON_REVIEW':
            case 'POST_ZEYN_FINANCE_ON_REVIEW':
            case 'ASTRI_ON_REVIEW':
            case 'ON REVIEW':
                return 'warning';
            case 'REJECTED':
            case 'NEED_REVISE':
                return 'danger';
            case 'WAITING HO':
            case 'WAITING MYREP':
            case 'WAITING FINANCE':
                return 'info';
            default:
                return 'secondary';
        }
    }

    private function isSitacHoUser()
    {
        if ($this->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        if (!isset($this->myrepAccess) || !method_exists($this->myrepAccess, 'getCurrentRoleKeys')) {
            return false;
        }

        return in_array('SITAC_HO', (array) $this->myrepAccess->getCurrentRoleKeys(), true);
    }

    private function canDonationInternalApprovalAction()
    {
        if ($this->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        if ($this->isFinanceHoUser()) {
            return false;
        }

        return $this->isApprover();
    }

    private function canSubmitDonationFinanceRequest()
    {
        if ($this->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        if (!isset($this->myrepAccess) || !method_exists($this->myrepAccess, 'getCurrentRoleKeys')) {
            return false;
        }

        $roleKeys = (array) $this->myrepAccess->getCurrentRoleKeys();
        return in_array('SITAC_HO', $roleKeys, true)
            || in_array('ADMIN_AREA', $roleKeys, true)
            || in_array('SM_AREA', $roleKeys, true)
            || in_array('FINANCE_HO', $roleKeys, true);
    }

    private function resolveDonationChecklistPrintStatus(array $rows)
    {
        $requiredCount = 0;
        $financeApprovedCount = 0;

        foreach ($rows as $row) {
            $isRequired = (int) ($row['is_required'] ?? 1) === 1;
            $sitacStatus = strtoupper(trim((string) ($row['status_file'] ?? '')));
            $financeStatus = strtoupper(trim((string) ($row['finance_status'] ?? 'NY')));

            if ($sitacStatus === 'REJECTED' || $financeStatus === 'REJECTED') {
                return 'REJECTED';
            }

            if (!$isRequired) {
                continue;
            }

            $requiredCount++;
            if ($sitacStatus === 'APPROVED' && $financeStatus === 'APPROVED') {
                $financeApprovedCount++;
            }
        }

        if ($requiredCount > 0 && $financeApprovedCount >= $requiredCount) {
            return 'APPROVED';
        }

        return 'ON REVIEW';
    }

    private function buildDonationChecklistSignatures(array $rows)
    {
        $sitacStatus = $this->resolveDonationChecklistLayerStatus($rows, 'status_file');
        $financeStatus = $this->resolveDonationChecklistLayerStatus($rows, 'finance_status');

        return [
            'sitac' => [
                'label' => 'SITAC HO',
                'name' => $this->resolveLatestDonationApprovalUserName($rows, 'approved_by', 'approved_at'),
                'status' => $sitacStatus,
                'answer' => $this->buildDonationChecklistAnswer($sitacStatus),
            ],
            'finance' => [
                'label' => 'COST CONTROL',
                'name' => $this->resolveMasterUserNameById(202),
                'status' => $financeStatus,
                'answer' => $this->buildDonationChecklistAnswer($financeStatus),
            ],
        ];
    }

    private function resolveDonationChecklistLayerStatus(array $rows, $statusField)
    {
        $requiredCount = 0;
        $approvedCount = 0;

        foreach ($rows as $row) {
            $isRequired = (int) ($row['is_required'] ?? 1) === 1;
            $status = strtoupper(trim((string) ($row[$statusField] ?? '')));

            if ($status === 'REJECTED') {
                return 'REJECTED';
            }

            if (!$isRequired) {
                continue;
            }

            $requiredCount++;
            if ($status === 'APPROVED') {
                $approvedCount++;
            }
        }

        if ($requiredCount > 0 && $approvedCount >= $requiredCount) {
            return 'APPROVED';
        }

        return 'ON REVIEW';
    }

    private function buildDonationChecklistAnswer($status)
    {
        $status = strtoupper(trim((string) $status));
        if ($status === 'APPROVED') {
            return 'Disetujui';
        }
        if ($status === 'REJECTED') {
            return 'Ditolak';
        }
        return 'Dalam Review';
    }

    private function resolveLatestDonationApprovalUserName(array $rows, $userField, $dateField)
    {
        $latestUserId = 0;
        $latestTimestamp = 0;

        foreach ($rows as $row) {
            $userId = (int) ($row[$userField] ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $timestamp = strtotime((string) ($row[$dateField] ?? '')) ?: 0;
            if ($timestamp >= $latestTimestamp) {
                $latestTimestamp = $timestamp;
                $latestUserId = $userId;
            }
        }

        if ($latestUserId <= 0 || !$this->db->table_exists('tb_master_user_new')) {
            return '-';
        }

        $user = $this->db
            ->select('nama_karyawan, username_user, nik')
            ->from('tb_master_user_new')
            ->where('id', $latestUserId)
            ->limit(1)
            ->get()
            ->row_array();

        return (string) ($user['nama_karyawan'] ?? $user['username_user'] ?? $user['nik'] ?? '-');
    }

    private function resolveMasterUserNameById($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0 || !$this->db->table_exists('tb_master_user_new')) {
            return '-';
        }

        $user = $this->db
            ->select('nama_karyawan, username_user, nik')
            ->from('tb_master_user_new')
            ->where('id', $userId)
            ->limit(1)
            ->get()
            ->row_array();

        return (string) ($user['nama_karyawan'] ?? $user['username_user'] ?? $user['nik'] ?? '-');
    }

    private function isFinanceHoUser()
    {
        if ($this->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        if (!isset($this->myrepAccess) || !method_exists($this->myrepAccess, 'getCurrentRoleKeys')) {
            return false;
        }

        return in_array('FINANCE_HO', (array) $this->myrepAccess->getCurrentRoleKeys(), true);
    }

    private function canUploadDonationDocument()
    {
        if ($this->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        if (!isset($this->myrepAccess) || !method_exists($this->myrepAccess, 'getCurrentRoleKeys')) {
            return false;
        }

        $roleKeys = (array) $this->myrepAccess->getCurrentRoleKeys();
        return in_array('ADMIN_AREA', $roleKeys, true) || in_array('SITAC_HO', $roleKeys, true);
    }

    private function canEditBatchApprovalDetail(array $cluster = [])
    {
        $level = strtoupper(trim((string) $this->session->userdata('nama_level')));
        if (in_array($level, ['SUPER ADMIN', 'ADMIN'], true)) {
            return true;
        }

        if (!$this->db->table_exists('tb_master_user_new') || !$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return false;
        }

        $user = $this->db
            ->select('nik')
            ->from('tb_master_user_new')
            ->where('id', (int) $this->session->userdata('id_user'))
            ->limit(1)
            ->get()
            ->row_array();
        $nik = trim((string) ($user['nik'] ?? ''));
        if ($nik === '') {
            return false;
        }

        $cityName = strtoupper(trim((string) ($cluster['city_name'] ?? '')));
        $provinceName = strtoupper(trim((string) ($cluster['province_name'] ?? '')));
        $regionalName = strtoupper(trim((string) ($cluster['regional_name'] ?? '')));
        if ($cityName === '') {
            return false;
        }

        $this->db->from('tb_myrep_pic_mapping_city')->where('UPPER(city_name)', $cityName);
        if ($provinceName !== '') {
            $this->db->where('UPPER(province_name)', $provinceName);
        }
        if ($regionalName !== '') {
            $this->db->where('UPPER(regional_name)', $regionalName);
        }
        if ($this->db->field_exists('is_active', 'tb_myrep_pic_mapping_city')) {
            $this->db->where('is_active', 1);
        }
        $this->db->group_start()
            ->where(myrep_pic_column_contains_sql($this->db, '`sitac_ho`', $nik), null, false)
            ->or_where(myrep_pic_column_contains_sql($this->db, '`admin_area`', $nik), null, false)
            ->or_where(myrep_pic_column_contains_sql($this->db, '`snd_area`', $nik), null, false)
            ->group_end();

        $found = $this->db->select('1 AS hit', false)->limit(1)->get()->row_array();
        if (!empty($found)) {
            return true;
        }

        if (!isset($this->myrepAccess) || !method_exists($this->myrepAccess, 'getCurrentRoleKeys')) {
            return false;
        }

        $roleKeys = (array) $this->myrepAccess->getCurrentRoleKeys();
        return empty($cluster) && (
            in_array('SITAC_HO', $roleKeys, true)
            || in_array('ADMIN_AREA', $roleKeys, true)
            || in_array('SND_AREA', $roleKeys, true)
        );
    }

    private function hasBatchPermission($actionKey)
    {
        if (!isset($this->myrepAccess) || !method_exists($this->myrepAccess, 'hasPermission')) {
            return true;
        }

        return (bool) $this->myrepAccess->hasPermission('Batch_Approval_MyRep', (string) $actionKey);
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
            'max_size' => 20480,
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

        $homepass = (int) ($cluster['hp_donasi'] ?? 0);
        $donationTotal = (float) ($cluster['nominal_pengajuan_area'] ?? 0);
        $nominalPerHomepass = (float) ($cluster['nominal_per_homepass'] ?? 0);
        if (strtolower(trim((string) $eventName)) === 'propose_donation') {
            $nominalApprovalEmr = (float) ($cluster['nominal_nego_emr'] ?? 0);
            if ($nominalApprovalEmr > 0) {
                $donationTotal = $nominalApprovalEmr;
                $nominalPerHomepass = $homepass > 0 ? ($nominalApprovalEmr / $homepass) : 0;
            }
        }

        $this->myrepNotifier->notify('Batch_Approval_MyRep', $eventName, [
            'module_label' => 'Batch Approval',
            'document_label' => (string) $documentLabel,
            'regional_name' => (string) ($cluster['regional_name'] ?? ''),
            'city_name' => (string) ($cluster['city_name'] ?? ''),
            'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
            'homepass' => $homepass,
            'donation_total' => $donationTotal,
            'nominal_per_homepass' => $nominalPerHomepass,
            'sender_name' => (string) $this->session->userdata('nama_user'),
            'detail_url' => base_url('Batch_Approval_MyRep/detail/' . $clusterId),
        ]);
    }

    private function queueDonationUploadNotification($clusterId, $groupKeyOrLabel, array $documentLabels)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0 || empty($documentLabels)) {
            return;
        }

        $cluster = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        if (empty($cluster)) {
            return;
        }

        $groupKeyOrLabel = strtoupper(trim((string) $groupKeyOrLabel));
        $groupKey = in_array($groupKeyOrLabel, ['POST_ZEYN', 'POST PAYMENT ZEYN DOCUMENT'], true) ? 'POST_ZEYN' : 'PRE_ZEYN';
        $groupLabel = $groupKey === 'POST_ZEYN' ? 'Dokumen Tahap 2' : 'Dokumen Tahap 1';
        $homepass = (int) ($cluster['hp_donasi'] ?? 0);
        $donationTotal = (float) ($cluster['nominal_pengajuan_area'] ?? 0);

        $this->myrepNotifier->enqueueDelayed('Batch_Approval_MyRep', 'document_masuk', [
            'module_label' => 'Batch Approval',
            'cluster_id' => $clusterId,
            'group_key' => $groupKey,
            'group_label' => $groupLabel,
            'regional_name' => (string) ($cluster['regional_name'] ?? ''),
            'city_name' => (string) ($cluster['city_name'] ?? ''),
            'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
            'homepass' => $homepass,
            'donation_total' => $donationTotal,
            'nominal_per_homepass' => $homepass > 0 ? ($donationTotal / $homepass) : 0,
            'sender_name' => (string) $this->session->userdata('nama_user'),
            'detail_url' => base_url('Batch_Approval_MyRep/detail/' . $clusterId),
        ], [
            'delay_minutes' => 3,
            'group_key' => $groupKey,
            'group_label' => $groupLabel,
            'document_labels' => $documentLabels,
        ]);
    }
}
