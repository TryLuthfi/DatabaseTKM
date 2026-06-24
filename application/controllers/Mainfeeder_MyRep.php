<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mainfeeder_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MMainfeeder_MyRep');
        $this->load->model('MImplementasi_BOQ_MyRep');
        $this->load->model('MChecklist_Dokument_MyRep');
        $this->load->library('upload');
    }

    public function index()
    {
        $this->requireLogin();
        redirect('MyRepublik_Project');
    }

    public function save()
    {
        $this->requireLogin();

        $userId = (int) $this->session->userdata('id_user');
        $payload = $this->collectMainfeederPayload($userId);
        $id = $this->MMainfeeder_MyRep->saveMainfeeder($payload);
        $this->session->set_flashdata($id > 0 ? 'success' : 'error', $id > 0 ? 'Mainfeeder berhasil disimpan.' : 'Mainfeeder gagal disimpan.');
        redirect($id > 0 ? 'DRM_MyRep/mainfeeder/' . $id : 'MyRepublik_Project');
    }

    public function import()
    {
        $this->requireLogin();

        $config = [
            'upload_path' => './uploads/',
            'allowed_types' => 'xls|xlsx|csv',
            'max_size' => 10240,
            'encrypt_name' => true,
        ];
        if (!is_dir($config['upload_path'])) {
            @mkdir($config['upload_path'], 0777, true);
        }

        $this->upload->initialize($config);
        if (!$this->upload->do_upload('file_excel')) {
            $this->session->set_flashdata('error', strip_tags($this->upload->display_errors('', '')));
            redirect('MyRepublik_Project');
            return;
        }

        $fileData = $this->upload->data();
        $filePath = $fileData['full_path'];
        try {
            $sheetData = strtolower(pathinfo($fileData['file_name'], PATHINFO_EXTENSION)) === 'csv'
                ? $this->readCsvSheetData($filePath)
                : $this->readFirstSheetData($filePath);
        } catch (Exception $e) {
            @unlink($filePath);
            $this->session->set_flashdata('error', 'File import mainfeeder tidak bisa dibaca.');
            redirect('MyRepublik_Project');
            return;
        }
        @unlink($filePath);

        $result = $this->importMainfeederRows($sheetData, (int) $this->session->userdata('id_user'));
        $this->session->set_flashdata(
            $result['saved'] > 0 ? 'success' : 'error',
            $result['saved'] . ' mainfeeder tersimpan, ' . (int) ($result['po_saved'] ?? 0) . ' PO diproses, ' . (int) ($result['termin_saved'] ?? 0) . ' termin diupdate. ' . $result['skipped'] . ' baris dilewati.'
        );
        redirect('MyRepublik_Project');
    }

    public function previewImport()
    {
        $this->requireLogin();

        $config = [
            'upload_path' => './uploads/',
            'allowed_types' => 'xls|xlsx|csv',
            'max_size' => 10240,
            'encrypt_name' => true,
        ];
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
            $sheetData = strtolower(pathinfo($fileData['file_name'], PATHINFO_EXTENSION)) === 'csv'
                ? $this->readCsvSheetData($filePath)
                : $this->readFirstSheetData($filePath);
        } catch (Exception $e) {
            @unlink($filePath);
            $this->jsonResponse(false, 'File import mainfeeder tidak bisa dibaca.');
            return;
        }
        @unlink($filePath);

        $preview = $this->buildMainfeederImportPreview($sheetData);
        if (empty($preview['status'])) {
            $this->jsonResponse(false, (string) ($preview['message'] ?? 'Preview import gagal.'));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($preview));
    }

    public function saveImport()
    {
        $this->requireLogin();

        $rows = json_decode((string) $this->input->post('rows_json'), true);
        if (empty($rows) || !is_array($rows)) {
            $this->jsonResponse(false, 'Tidak ada data valid untuk disimpan.');
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        $saved = 0;
        $poSaved = 0;
        $terminSaved = 0;
        $skipped = 0;
        $errorRows = [];
        foreach ($rows as $index => $row) {
            $row = is_array($row) ? $this->normalizeMainfeederImportRow($row) : [];
            $errors = $this->validateMainfeederImportRow($row);
            if (!empty($errors)) {
                $skipped++;
                $errorRows[] = [
                    'row_number' => $index + 1,
                    'mainfeeder_name' => (string) ($row['mainfeeder_name'] ?? ''),
                    'message' => implode(', ', $errors),
                ];
                continue;
            }

            $result = $this->saveFullMainfeederImportRow($row, $userId);
            if (!empty($result['mainfeeder_id'])) {
                $saved++;
                $poSaved += (int) ($result['po_saved'] ?? 0);
                $terminSaved += (int) ($result['termin_saved'] ?? 0);
            } else {
                $skipped++;
                $errorRows[] = [
                    'row_number' => $index + 1,
                    'mainfeeder_name' => (string) ($row['mainfeeder_name'] ?? ''),
                    'message' => (string) ($result['message'] ?? 'Gagal menyimpan mainfeeder.'),
                ];
            }
        }

        if ($saved <= 0) {
            $this->jsonResponse(false, 'Tidak ada mainfeeder yang berhasil disimpan.', [
                'saved' => $saved,
                'po_saved' => $poSaved,
                'termin_saved' => $terminSaved,
                'skipped' => $skipped,
                'error_rows' => $errorRows,
            ]);
            return;
        }

        $this->jsonResponse(true, $saved . ' mainfeeder berhasil diimport, ' . $poSaved . ' PO diproses, ' . $terminSaved . ' termin diupdate. ' . $skipped . ' baris dilewati.', [
            'saved' => $saved,
            'po_saved' => $poSaved,
            'termin_saved' => $terminSaved,
            'skipped' => $skipped,
            'error_rows' => $errorRows,
        ]);
    }

    public function downloadImportTemplate()
    {
        $this->requireLogin();

        $filename = 'template_import_myrep_mainfeeder_' . date('Ymd_His') . '.csv';
        $headers = $this->getMainfeederImportHeaders();
        $examples = [
            ['DRM', 'MALANG', '', '', 'Main Feeder - OLT A to OLT B', '', '5328/FEEDER/PROCUREMENT/VII/2024', '2024-07-29', '1200', '2024-09-24', 'OLT A', 'Contoh DRM', '', '', '', '', '', '', '', 'INITIAL', 'PARTIAL PAYMENT', '0', '7400000001', '2024-10-10', '100000000', '', 'Contoh PO', '2024-10-25', '20000000', '20000000', '2024-11-25', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['CHECKLIST DOKUMENT', 'MANADO', '', '', 'Main Feeder - OLT C to OLT D', 'FMND0001', '5759/FEEDER/PROCUREMENT/VIII/2024', '2024-08-30', '900', '2024-09-24', '', '', '2026-01-14', '2026-01-14', 'DONE', 'CLOSED', 'CLOSED', 'CLOSED', '', 'FINAL', 'PARTIAL PAYMENT', '0', '7400000002', '2024-10-10', '120000000', '110000000', '', '2024-10-25', '24000000', '24000000', '2026-04-30', '2026-04-30', '', '30000000', '2026-04-30', '2026-06-18', '', '18000000', '', 'FULL UPLOAD', '', '36000000', '', '', '', '12000000'],
        ];

        $this->output
            ->set_content_type('text/csv')
            ->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate');

        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        foreach ($examples as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
    }

    public function downloadCurrentSnapshot()
    {
        $this->requireLogin();

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedStatus = strtoupper(trim((string) $this->input->get('status')));
        $headers = $this->getMainfeederImportHeaders();
        $rows = $this->MMainfeeder_MyRep->getRows($selectedCity, $selectedStatus);
        $filename = 'update_import_myrep_mainfeeder_current_' . date('Ymd_His') . '.csv';

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
        foreach ($rows as $row) {
            fputcsv($output, $this->buildMainfeederSnapshotLine($row, $headers));
        }
        fclose($output);
        exit;
    }

    public function detail($mainfeederId = 0)
    {
        $this->requireLogin();

        $mainfeederId = (int) $mainfeederId;
        $mainfeeder = $this->MMainfeeder_MyRep->getById($mainfeederId);
        if (empty($mainfeeder)) {
            $this->session->set_flashdata('error', 'Data mainfeeder tidak ditemukan.');
            redirect('MyRepublik_Project');
            return;
        }

        redirect($this->stageDetailUri($mainfeeder));
    }

    public function saveDrm($mainfeederId = 0)
    {
        $this->requireLogin();
        $mainfeederId = (int) $mainfeederId;
        $ok = $this->MMainfeeder_MyRep->saveDrmMetadata($mainfeederId, [
            'drm_date' => $this->normalizeDate($this->input->post('drm_date')),
            'nama_olt' => $this->input->post('nama_olt'),
            'status_drm' => $this->input->post('status_drm') ?: 'SUBMITTED',
            'remark_drm' => $this->input->post('remark_drm'),
            'created_by' => (int) $this->session->userdata('id_user'),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ]);
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Metadata DRM berhasil disimpan.' : 'Metadata DRM gagal disimpan.');
        $this->redirectBack('DRM_MyRep/mainfeeder/' . $mainfeederId);
    }

    public function uploadDrmDocument($mainfeederId = 0)
    {
        $this->requireLogin();
        $mainfeederId = (int) $mainfeederId;
        $docItemId = (int) $this->input->post('id_doc_item');
        $docName = trim((string) $this->input->post('doc_name'));
        $isNoDoc = (int) $this->input->post('is_document_not_required') === 1;
        $fileName = '';
        $filePath = '';

        if (!$isNoDoc) {
            $upload = $this->uploadFile('file', './uploads/myrep_mainfeeder_drm/', 'MF_DRM_' . $mainfeederId . '_' . $docItemId . '_' . $this->safeName($docName));
            if (empty($upload['status'])) {
                $this->session->set_flashdata('error', (string) ($upload['message'] ?? 'Upload gagal.'));
                $this->redirectBack('DRM_MyRep/mainfeeder/' . $mainfeederId);
                return;
            }
            $fileName = $upload['file_name'];
            $filePath = $upload['file_path'];
        }

        $fileId = $this->MMainfeeder_MyRep->saveDrmFileUpload($mainfeederId, $docItemId, [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status_file' => 'UPLOADED',
            'remark' => $this->input->post('remark'),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
            'is_document_not_required' => $isNoDoc ? 1 : 0,
        ]);
        $this->session->set_flashdata($fileId > 0 ? 'success' : 'error', $fileId > 0 ? 'Dokumen DRM mainfeeder berhasil diupload.' : 'Dokumen DRM mainfeeder gagal diupload.');
        $this->redirectBack('DRM_MyRep/mainfeeder/' . $mainfeederId);
    }

    public function reviewDrmDocument($mainfeederId = 0)
    {
        $this->requireLogin();
        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approval dokumen.');
            $this->redirectBack('DRM_MyRep/mainfeeder/' . (int) $mainfeederId);
            return;
        }
        $fileId = (int) $this->input->post('id_doc_file_mainfeeder_flow');
        $status = strtoupper(trim((string) $this->input->post('status_file')));
        $ok = $this->MMainfeeder_MyRep->updateDrmFileStatus($fileId, [
            'status_file' => $status,
            'remark' => $this->input->post('remark'),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Status dokumen DRM berhasil disimpan.' : 'Status dokumen DRM gagal disimpan.');
        $this->redirectBack('DRM_MyRep/mainfeeder/' . (int) $mainfeederId);
    }

    public function uploadDrmBoq($mainfeederId = 0)
    {
        $this->requireLogin();
        $mainfeederId = (int) $mainfeederId;
        $docItemId = (int) $this->input->post('id_doc_item');

        $upload = $this->uploadFile('boq_file', './uploads/myrep_mainfeeder_drm/', 'MF_BOQ_' . $mainfeederId);
        if (empty($upload['status'])) {
            $this->session->set_flashdata('error', (string) ($upload['message'] ?? 'Upload BOQ gagal.'));
            $this->redirectBack('DRM_MyRep/mainfeeder/' . $mainfeederId);
            return;
        }

        $fileId = 0;
        if ($docItemId > 0) {
            $fileId = $this->MMainfeeder_MyRep->saveDrmFileUpload($mainfeederId, $docItemId, [
                'file_name' => $upload['file_name'],
                'file_path' => $upload['file_path'],
                'status_file' => 'UPLOADED',
                'remark' => 'APD BOQ Mainfeeder',
                'uploaded_by' => (int) $this->session->userdata('id_user'),
            ]);
        }

        $items = $this->parseBoqMainfeeder(FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $upload['file_path']));
        if (empty($items)) {
            $this->session->set_flashdata('error', 'BOQ tidak menghasilkan item yang cocok dengan master BOQ.');
            $this->redirectBack('DRM_MyRep/mainfeeder/' . $mainfeederId);
            return;
        }

        $ok = $this->MMainfeeder_MyRep->saveDrmBoqDraft($mainfeederId, $fileId, $items, (int) $this->session->userdata('id_user'), true);
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? count($items) . ' item BOQ mainfeeder berhasil disubmit ke HO.' : 'BOQ mainfeeder gagal disimpan.');
        $this->redirectBack('DRM_MyRep/mainfeeder/' . $mainfeederId);
    }

    public function reviewDrmBoq($mainfeederId = 0)
    {
        $this->requireLogin();
        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approval BOQ.');
            $this->redirectBack('DRM_MyRep/mainfeeder/' . (int) $mainfeederId);
            return;
        }
        $action = strtoupper(trim((string) $this->input->post('action_review')));
        $remark = trim((string) $this->input->post('remark'));
        if ($action === 'APPROVE') {
            $ok = $this->MMainfeeder_MyRep->approveDrmBoq((int) $mainfeederId, (int) $this->session->userdata('id_user'), $remark);
        } else {
            $ok = $this->MMainfeeder_MyRep->rejectDrmBoq((int) $mainfeederId, (int) $this->session->userdata('id_user'), $remark);
        }
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Review BOQ berhasil disimpan.' : 'Review BOQ gagal disimpan.');
        if ($ok && $action === 'APPROVE') {
            redirect('Implementasi_BOQ_MyRep/mainfeeder/' . (int) $mainfeederId);
            return;
        }
        $this->redirectBack('DRM_MyRep/mainfeeder/' . (int) $mainfeederId);
    }

    public function saveDailyActivity($mainfeederId = 0)
    {
        $this->requireLogin();
        $mainfeederId = (int) $mainfeederId;
        $activityCode = strtoupper(trim((string) $this->input->post('activity_code')));
        $definitions = $this->MImplementasi_BOQ_MyRep->getDailyActivityDefinitions();
        $definitionMap = [];
        foreach ($definitions as $definition) {
            $definitionMap[(string) $definition['activity_code']] = $definition;
        }
        if (!isset($definitionMap[$activityCode])) {
            $this->session->set_flashdata('error', 'Activity tidak valid.');
            $this->redirectBack('Implementasi_BOQ_MyRep/mainfeeder/' . $mainfeederId);
            return;
        }
        $photos = $this->uploadMultipleFiles('activity_photos', './uploads/myrep_mainfeeder_activity/', 'MF_ACT_' . $mainfeederId . '_' . $activityCode);
        if (empty($photos)) {
            $this->session->set_flashdata('error', 'Minimal satu foto aktivitas wajib diupload.');
            $this->redirectBack('Implementasi_BOQ_MyRep/mainfeeder/' . $mainfeederId);
            return;
        }

        $definition = $definitionMap[$activityCode];
        $activityId = $this->MMainfeeder_MyRep->createDailyActivity($mainfeederId, [
            'activity_date' => $this->normalizeDate($this->input->post('activity_date')),
            'activity_code' => $activityCode,
            'activity_name' => (string) $definition['activity_name'],
            'activity_detail' => $this->input->post('activity_detail'),
            'boq_type' => (string) $definition['boq_type'],
            'qty_activity' => $this->normalizeNumber($this->input->post('qty_activity')),
            'unit_activity' => (string) $definition['default_unit'],
            'team_count' => (int) $this->input->post('team_count'),
            'worker_count' => (int) $this->input->post('worker_count'),
            'remark_activity' => $this->input->post('remark_activity'),
            'created_by' => (int) $this->session->userdata('id_user'),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ], $photos);
        $this->session->set_flashdata($activityId > 0 ? 'success' : 'error', $activityId > 0 ? 'Aktivitas implementasi berhasil disimpan.' : 'Aktivitas implementasi gagal disimpan.');
        $this->redirectBack('Implementasi_BOQ_MyRep/mainfeeder/' . $mainfeederId);
    }

    public function saveAtp($mainfeederId = 0)
    {
        $this->requireLogin();
        $mainfeederId = (int) $mainfeederId;
        $statusAtp = strtoupper(trim((string) $this->input->post('status_atp')));
        $existing = $this->MMainfeeder_MyRep->getById($mainfeederId);
        $recordExists = $this->MMainfeeder_MyRep->hasAtpDocument($mainfeederId, 'RECORD_PUNCLIST');
        $rectificationExists = $this->MMainfeeder_MyRep->hasAtpDocument($mainfeederId, 'BA_RECTIFICATION');

        if ($statusAtp === 'PUNCLIST' && !$recordExists && empty($_FILES['record_punclist_file']['name'])) {
            $this->session->set_flashdata('error', 'Status ATP PUNCLIST wajib upload Record Punclist.');
            $this->redirectBack('ATP_MyRep/mainfeeder/' . $mainfeederId);
            return;
        }
        if (strtoupper((string) ($existing['status_atp'] ?? '')) === 'PUNCLIST' && $statusAtp === 'DONE' && !$rectificationExists && empty($_FILES['ba_rectification_file']['name'])) {
            $this->session->set_flashdata('error', 'Perubahan ATP ke DONE wajib upload BA Rectification.');
            $this->redirectBack('ATP_MyRep/mainfeeder/' . $mainfeederId);
            return;
        }

        $this->handleAtpUpload($mainfeederId, 'record_punclist_file', 'RECORD_PUNCLIST', $this->input->post('record_punclist_remark'));
        $this->handleAtpUpload($mainfeederId, 'ba_rectification_file', 'BA_RECTIFICATION', $this->input->post('ba_rectification_remark'));
        $ok = $this->MMainfeeder_MyRep->updateAtp($mainfeederId, [
            'email_atp_date' => $this->normalizeDate($this->input->post('email_atp_date')),
            'atp_date' => $this->normalizeDate($this->input->post('atp_date')),
            'status_atp' => $statusAtp,
            'updated_by' => (int) $this->session->userdata('id_user'),
        ]);
        if ($ok && $statusAtp === 'DONE') {
            $mainfeeder = $this->MMainfeeder_MyRep->getById($mainfeederId);
            $this->MChecklist_Dokument_MyRep->ensureMainfeederPackages($mainfeederId, $mainfeeder['atp_date'] ?? null);
        }
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'ATP mainfeeder berhasil disimpan.' : 'ATP mainfeeder gagal disimpan.');
        if ($ok && $statusAtp === 'DONE') {
            redirect('Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
            return;
        }
        $this->redirectBack('ATP_MyRep/mainfeeder/' . $mainfeederId);
    }

    public function savePo($mainfeederId = 0)
    {
        $this->requireLogin();
        $mainfeederId = (int) $mainfeederId;
        $poNumber = trim((string) $this->input->post('po_number'));
        $poDate = $this->normalizeDate($this->input->post('po_date'));
        $poValue = $this->normalizeNumber($this->input->post('po_value'));
        if ($poNumber === '' || $poDate === null || $poValue <= 0) {
            $this->session->set_flashdata('error', 'Nomor PO, tanggal PO, dan nilai PO wajib diisi.');
            $this->redirectBack('PO_MyRep/mainfeeder/' . $mainfeederId);
            return;
        }
        $poId = $this->MMainfeeder_MyRep->createPoHeader($mainfeederId, [
            'parent_po_header_id' => (int) $this->input->post('parent_po_header_id'),
            'po_category' => strtoupper(trim((string) $this->input->post('po_category'))) ?: 'INITIAL',
            'po_number' => $poNumber,
            'po_date' => $poDate,
            'po_value' => $poValue,
            'status_po' => strtoupper(trim((string) $this->input->post('status_po'))) ?: 'ISSUED',
            'po_version_label' => $this->input->post('po_version_label'),
            'remark_po' => $this->input->post('remark_po'),
            'created_by' => (int) $this->session->userdata('id_user'),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ]);
        $this->session->set_flashdata($poId > 0 ? 'success' : 'error', $poId > 0 ? 'PO mainfeeder berhasil disimpan.' : 'PO mainfeeder gagal disimpan.');
        $this->redirectBack('PO_MyRep/mainfeeder/' . $mainfeederId);
    }

    public function updateTermin($mainfeederId = 0)
    {
        $this->requireLogin();
        $terminId = (int) $this->input->post('id_po_termin');
        $ok = $this->MMainfeeder_MyRep->updateTermin($terminId, [
            'status_termin' => strtoupper(trim((string) $this->input->post('status_termin'))) ?: 'NOT READY',
            'invoice_number' => $this->input->post('invoice_number'),
            'invoice_date' => $this->normalizeDate($this->input->post('invoice_date')),
            'invoice_value' => $this->normalizeNumber($this->input->post('invoice_value')) ?: null,
            'bast_date' => $this->normalizeDate($this->input->post('bast_date')),
            'payment_date' => $this->normalizeDate($this->input->post('payment_date')),
            'remark_termin' => $this->input->post('remark_termin'),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ]);
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Termin PO berhasil diupdate.' : 'Termin PO gagal diupdate.');
        $this->redirectBack('PO_MyRep/mainfeeder/' . (int) $mainfeederId);
    }

    public function saveTerminCertificate($mainfeederId = 0)
    {
        $this->requireLogin();
        $ok = $this->MMainfeeder_MyRep->updateTerminCertificate((int) $this->input->post('id_po_termin'), trim((string) $this->input->post('sertifikat_invoice')), (int) $this->session->userdata('id_user'));
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Sertifikat termin berhasil disimpan.' : 'Sertifikat termin gagal disimpan.');
        $this->redirectBack('PO_MyRep/mainfeeder/' . (int) $mainfeederId);
    }

    public function markDone($mainfeederId = 0)
    {
        $this->requireLogin();
        $ok = $this->MMainfeeder_MyRep->markDone((int) $mainfeederId, (int) $this->session->userdata('id_user'));
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Mainfeeder ditandai DONE.' : 'Gagal menandai mainfeeder DONE.');
        $this->redirectBack('Checklist_Dokument_MyRep/detailMainfeeder/' . (int) $mainfeederId);
    }

    private function collectMainfeederPayload($userId)
    {
        return [
            'cluster_code' => $this->input->post('cluster_code'),
            'mainfeeder_name' => $this->input->post('mainfeeder_name'),
            'current_status' => $this->input->post('current_status') ?: 'DRM',
            'year_num' => (int) $this->input->post('year_num'),
            'month_num' => (int) $this->input->post('month_num'),
            'regional_name' => $this->input->post('regional_name'),
            'province_name' => $this->input->post('province_name'),
            'city_name' => $this->input->post('city_name'),
            'team_name' => $this->input->post('team_name'),
            'chief' => $this->input->post('chief'),
            'rpm' => $this->input->post('rpm'),
            'sm' => $this->input->post('sm'),
            'spv' => $this->input->post('spv'),
            'vendor_name' => $this->input->post('vendor_name'),
            'length_meter' => $this->normalizeNumber($this->input->post('length_meter')),
            'atp_date' => $this->normalizeDate($this->input->post('atp_date')),
            'remark_mainfeeder' => $this->input->post('remark_mainfeeder'),
            'created_by' => (int) $userId,
            'updated_by' => (int) $userId,
        ];
    }

    private function importMainfeederRows(array $sheetData, $userId)
    {
        $preview = $this->buildMainfeederImportPreview($sheetData);
        $validRows = !empty($preview['valid_rows']) && is_array($preview['valid_rows']) ? $preview['valid_rows'] : [];
        $saved = 0;
        $poSaved = 0;
        $terminSaved = 0;
        foreach ($validRows as $row) {
            $result = $this->saveFullMainfeederImportRow($row, $userId);
            if (!empty($result['mainfeeder_id'])) {
                $saved++;
                $poSaved += (int) ($result['po_saved'] ?? 0);
                $terminSaved += (int) ($result['termin_saved'] ?? 0);
            }
        }
        return [
            'saved' => $saved,
            'po_saved' => $poSaved,
            'termin_saved' => $terminSaved,
            'skipped' => max(0, count($preview['rows'] ?? []) - $saved),
        ];
    }

    private function buildMainfeederImportPreview(array $sheetData)
    {
        if (count($sheetData) < 2) {
            return ['status' => false, 'message' => 'File import tidak memiliki data.'];
        }

        $headerRow = reset($sheetData);
        $headers = [];
        $normalizedHeaders = [];
        foreach ($headerRow as $col => $headerText) {
            $headerText = trim((string) $headerText);
            if ($headerText === '') {
                continue;
            }
            $headers[$col] = $headerText;
            $normalizedHeaders[$col] = $this->normalizeHeaderName($headerText);
        }

        foreach (['mainfeeder_name'] as $required) {
            if (!in_array($required, $normalizedHeaders, true)) {
                return ['status' => false, 'message' => 'Header wajib memuat: mainfeeder_name'];
            }
        }

        $previewRows = [];
        $validRows = [];
        $errorRows = [];
        foreach ($sheetData as $rowIndex => $excelRow) {
            if ((int) $rowIndex === 1) {
                continue;
            }

            $rowRaw = [];
            $rowNormalized = [];
            foreach ($headers as $col => $headerText) {
                $value = isset($excelRow[$col]) ? trim((string) $excelRow[$col]) : '';
                $rowRaw[$headerText] = $value;
                $rowNormalized[$this->normalizeHeaderName($headerText)] = $value;
            }

            if (trim(implode('', $rowNormalized)) === '') {
                continue;
            }

            $rowNormalized = $this->normalizeMainfeederImportRow($rowNormalized);
            $errors = $this->validateMainfeederImportRow($rowNormalized);
            $previewRows[] = [
                'row_number' => $rowIndex,
                'status' => empty($errors) ? 'valid' : 'invalid',
                'message' => empty($errors) ? 'Siap diimport' : implode(', ', $errors),
                'raw' => $rowRaw,
            ];

            if (empty($errors)) {
                $validRows[] = $rowNormalized;
            } else {
                $errorRows[] = ['row_number' => $rowIndex, 'errors' => $errors];
            }
        }

        return [
            'status' => true,
            'message' => count($validRows) . ' data valid dari ' . count($previewRows) . ' baris',
            'headers' => array_values($headers),
            'rows' => $previewRows,
            'valid_rows' => $validRows,
            'error_rows' => $errorRows,
        ];
    }

    private function getMainfeederImportHeaders()
    {
        return [
            'status_current',
            'city_name',
            'district_name',
            'village_name',
            'mainfeeder_name',
            'cluster_code',
            'nomor_ntp',
            'tanggal_ntp',
            'homepass_drm',
            'drm_date',
            'nama_olt',
            'remark_drm',
            'email_atp_date',
            'actual_atp_date',
            'status_atp',
            'mainfeeder_cwatp',
            'mainfeeder_fullopm',
            'mainfeeder_rfs',
            'mainfeeder_rfs_nro_flow',
            'po_mainfeeder_category',
            'po_mainfeeder_status',
            'po_mainfeeder_on_target',
            'po_mainfeeder_number',
            'po_mainfeeder_date',
            'po_mainfeeder_value',
            'po_mainfeeder_value_final',
            'po_mainfeeder_remark',
            'po_mainfeeder_termin1_plan_invoice',
            'po_mainfeeder_termin1_submit_invoice',
            'po_mainfeeder_termin1_nilai_invoice',
            'po_mainfeeder_termin2_plan_invoice',
            'po_mainfeeder_termin2_submit_invoice',
            'po_mainfeeder_termin2_sertifikat_invoice',
            'po_mainfeeder_termin2_nilai_invoice',
            'po_mainfeeder_termin3_plan_invoice',
            'po_mainfeeder_termin3_submit_invoice',
            'po_mainfeeder_termin3_sertifikat_invoice',
            'po_mainfeeder_termin3_nilai_invoice',
            'po_mainfeeder_termin4_plan_invoice',
            'po_mainfeeder_termin4_submit_invoice',
            'po_mainfeeder_termin4_sertifikat_invoice',
            'po_mainfeeder_termin4_nilai_invoice',
            'po_mainfeeder_termin5_plan_invoice',
            'po_mainfeeder_termin5_submit_invoice',
            'po_mainfeeder_termin5_sertifikat_invoice',
            'po_mainfeeder_termin5_nilai_invoice',
        ];
    }

    private function normalizeMainfeederImportRow(array $row)
    {
        $aliases = [
            'year_num' => ['year_num', 'year', 'tahun'],
            'month_num' => ['month_num', 'month', 'bulan'],
            'regional_name' => ['regional_name', 'regional'],
            'province_name' => ['province_name', 'province', 'provinsi'],
            'city_name' => ['city_name', 'city', 'kota'],
            'district_name' => ['district_name', 'district', 'kecamatan'],
            'village_name' => ['village_name', 'village', 'kelurahan', 'desa'],
            'team_name' => ['team_name', 'team'],
            'vendor_name' => ['vendor_name', 'vendor'],
            'mainfeeder_name' => ['mainfeeder_name', 'cluster_name', 'project_name', 'nama_mainfeeder'],
            'current_status' => ['current_status', 'status_current', 'status'],
            'length_meter' => ['length_meter', 'length', 'panjang_meter', 'panjang', 'homepass_drm'],
            'atp_date' => ['atp_date', 'actual_atp_date'],
            'remark_mainfeeder' => ['remark_mainfeeder', 'remark', 'remarks'],
        ];

        foreach ($aliases as $target => $keys) {
            if (isset($row[$target]) && trim((string) $row[$target]) !== '') {
                continue;
            }
            foreach ($keys as $key) {
                if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                    $row[$target] = $row[$key];
                    break;
                }
            }
        }

        $row['cluster_code'] = strtoupper(trim((string) ($row['cluster_code'] ?? '')));
        $row['mainfeeder_name'] = trim((string) ($row['mainfeeder_name'] ?? ''));
        $row['current_status'] = $this->normalizeImportStatus($row['current_status'] ?? 'DRM');
        $row['_cluster_code_generated'] = false;
        if ($row['cluster_code'] === '') {
            $row['cluster_code'] = $this->generateMainfeederImportCode($row);
            $row['_cluster_code_generated'] = true;
        }
        if (empty($row['year_num'])) {
            $poDate = $this->normalizeDate($row['po_mainfeeder_date'] ?? '');
            $drmDate = $this->normalizeDate($row['drm_date'] ?? '');
            $date = $poDate ?: $drmDate;
            $row['year_num'] = $date ? (int) date('Y', strtotime($date)) : (int) date('Y');
        }
        if (empty($row['month_num'])) {
            $poDate = $this->normalizeDate($row['po_mainfeeder_date'] ?? '');
            $drmDate = $this->normalizeDate($row['drm_date'] ?? '');
            $date = $poDate ?: $drmDate;
            $row['month_num'] = $date ? (int) date('n', strtotime($date)) : (int) date('n');
        }

        return $row;
    }

    private function validateMainfeederImportRow(array $row)
    {
        $errors = [];
        if (trim((string) ($row['mainfeeder_name'] ?? '')) === '') {
            $errors[] = 'mainfeeder_name wajib diisi';
        }
        if (trim((string) ($row['city_name'] ?? '')) === '') {
            $errors[] = 'city_name wajib diisi';
        }

        $status = strtoupper(trim((string) ($row['current_status'] ?? 'DRM')));
        if ($status !== '' && !in_array($status, $this->MMainfeeder_MyRep->getStatusOptions(), true)) {
            $errors[] = 'current_status tidak valid';
        }

        $month = trim((string) ($row['month_num'] ?? ''));
        if ($month !== '' && ((int) $month < 1 || (int) $month > 12)) {
            $errors[] = 'month_num harus 1-12';
        }

        return $errors;
    }

    private function mainfeederImportPayload(array $row, $userId)
    {
        $row = $this->normalizeMainfeederImportRow($row);
        return [
            'cluster_code' => $row['cluster_code'] ?? '',
            '_cluster_code_generated' => !empty($row['_cluster_code_generated']),
            'mainfeeder_name' => $row['mainfeeder_name'] ?? '',
            'current_status' => $row['current_status'] ?? 'DRM',
            'year_num' => $row['year_num'] ?? '',
            'month_num' => $row['month_num'] ?? '',
            'regional_name' => $row['regional_name'] ?? '',
            'province_name' => $row['province_name'] ?? '',
            'city_name' => $row['city_name'] ?? '',
            'team_name' => $row['team_name'] ?? '',
            'chief' => $row['chief'] ?? '',
            'rpm' => $row['rpm'] ?? '',
            'sm' => $row['sm'] ?? '',
            'spv' => $row['spv'] ?? '',
            'vendor_name' => $row['vendor_name'] ?? '',
            'length_meter' => $this->normalizeNumber($row['length_meter'] ?? 0),
            'atp_date' => $this->normalizeDate($row['atp_date'] ?? ''),
            'email_atp_date' => $this->normalizeDate($row['email_atp_date'] ?? ''),
            'status_atp' => $this->normalizeAtpStatus($row['status_atp'] ?? ''),
            'remark_mainfeeder' => $this->buildMainfeederImportRemark($row),
            'created_by' => (int) $userId,
            'updated_by' => (int) $userId,
        ];
    }

    private function saveFullMainfeederImportRow(array $row, $userId)
    {
        $row = $this->normalizeMainfeederImportRow($row);
        $mainfeederId = $this->MMainfeeder_MyRep->saveMainfeeder($this->mainfeederImportPayload($row, $userId));
        if ($mainfeederId <= 0) {
            return ['mainfeeder_id' => 0, 'message' => 'Gagal menyimpan mainfeeder.'];
        }

        $this->saveImportDrmMetadata($mainfeederId, $row, $userId);

        $poSaved = 0;
        $terminSaved = 0;
        $poNumber = trim((string) ($row['po_mainfeeder_number'] ?? ''));
        if ($poNumber !== '') {
            $poValue = $this->getImportPoValue($row);
            $poId = $this->MMainfeeder_MyRep->upsertPoHeader($mainfeederId, [
                'po_category' => $this->normalizePoCategory($row['po_mainfeeder_category'] ?? 'INITIAL'),
                'po_number' => $poNumber,
                'po_date' => $this->normalizeDate($row['po_mainfeeder_date'] ?? ''),
                'po_value' => $poValue,
                'status_po' => $this->normalizePoStatus($row['po_mainfeeder_status'] ?? 'ISSUED'),
                'on_target' => $this->normalizeBooleanInt($row['po_mainfeeder_on_target'] ?? 1),
                'remark_po' => $row['po_mainfeeder_remark'] ?? '',
                'created_by' => (int) $userId,
                'updated_by' => (int) $userId,
            ]);
            if ($poId > 0) {
                $poSaved = 1;
                $terminSaved = $this->saveImportTerminRows($poId, $row, $userId);
            }
        }

        return [
            'mainfeeder_id' => $mainfeederId,
            'po_saved' => $poSaved,
            'termin_saved' => $terminSaved,
        ];
    }

    private function saveImportDrmMetadata($mainfeederId, array $row, $userId)
    {
        $hasDrm = trim((string) ($row['drm_date'] ?? '')) !== ''
            || trim((string) ($row['nama_olt'] ?? '')) !== ''
            || trim((string) ($row['remark_drm'] ?? '')) !== '';
        if (!$hasDrm) {
            return false;
        }

        $status = in_array((string) ($row['current_status'] ?? 'DRM'), ['IMPLEMENTASI', 'ATP', 'CHECKLIST', 'DONE'], true) ? 'DONE' : 'SUBMITTED';
        return $this->MMainfeeder_MyRep->saveDrmMetadata($mainfeederId, [
            'drm_date' => $this->normalizeDate($row['drm_date'] ?? ''),
            'nama_olt' => $row['nama_olt'] ?? '',
            'status_drm' => $status,
            'remark_drm' => $row['remark_drm'] ?? '',
            'created_by' => (int) $userId,
            'updated_by' => (int) $userId,
        ]);
    }

    private function saveImportTerminRows($poId, array $row, $userId)
    {
        $saved = 0;
        for ($terminNo = 1; $terminNo <= 5; $terminNo++) {
            $prefix = 'po_mainfeeder_termin' . $terminNo . '_';
            $planRaw = trim((string) ($row[$prefix . 'plan_invoice'] ?? ''));
            $submitRaw = trim((string) ($row[$prefix . 'submit_invoice'] ?? ''));
            $sertifikatRaw = trim((string) ($row[$prefix . 'sertifikat_invoice'] ?? ''));
            $nilaiRaw = trim((string) ($row[$prefix . 'nilai_invoice'] ?? ''));
            if ($planRaw === '' && $submitRaw === '' && $sertifikatRaw === '' && $nilaiRaw === '') {
                continue;
            }

            $invoiceDate = $this->normalizeDate($submitRaw);
            $nilai = $this->normalizeNumber($nilaiRaw);
            $payload = [
                'status_termin' => $invoiceDate || $nilai > 0 ? 'BILLED' : 'READY BILLING',
                'updated_by' => (int) $userId,
            ];
            if ($invoiceDate) {
                $payload['invoice_date'] = $invoiceDate;
            }
            if ($nilai > 0) {
                $payload['termin_value'] = $nilai;
                $payload['invoice_value'] = $nilai;
            }
            $remarks = [];
            if ($planRaw !== '') {
                $remarks[] = 'Plan Invoice: ' . $planRaw;
            }
            if ($sertifikatRaw !== '') {
                $remarks[] = 'Sertifikat: ' . $sertifikatRaw;
                $payload['sertifikat_invoice_date'] = $sertifikatRaw;
            }
            if (!empty($remarks)) {
                $payload['remark_termin'] = implode(' | ', $remarks);
            }

            if ($this->MMainfeeder_MyRep->updateTerminByPoAndNo($poId, $terminNo, $payload)) {
                $saved++;
            }
        }
        return $saved;
    }

    private function buildMainfeederSnapshotLine(array $row, array $headers)
    {
        $lineMap = array_fill_keys($headers, '');
        $lineMap['status_current'] = $this->exportMainfeederStatus($row['current_status'] ?? '');
        $lineMap['city_name'] = $row['city_name'] ?? '';
        $lineMap['mainfeeder_name'] = $row['mainfeeder_name'] ?? '';
        $lineMap['cluster_code'] = $row['cluster_code'] ?? '';
        $lineMap['homepass_drm'] = $row['length_meter'] ?? '';
        $lineMap['drm_date'] = $row['drm_date'] ?? '';
        $lineMap['email_atp_date'] = $row['email_atp_date'] ?? '';
        $lineMap['actual_atp_date'] = $row['atp_date'] ?? '';
        $lineMap['status_atp'] = $row['status_atp'] ?? '';

        $poHeaders = !empty($row['id_mainfeeder']) ? $this->MMainfeeder_MyRep->getPoHeaders((int) $row['id_mainfeeder']) : [];
        $po = !empty($poHeaders[0]) ? $poHeaders[0] : [];
        if (!empty($po)) {
            $lineMap['po_mainfeeder_category'] = $po['po_category'] ?? '';
            $lineMap['po_mainfeeder_status'] = $po['status_po'] ?? '';
            $lineMap['po_mainfeeder_on_target'] = isset($po['on_target']) ? (string) $po['on_target'] : '';
            $lineMap['po_mainfeeder_number'] = $po['po_number'] ?? '';
            $lineMap['po_mainfeeder_date'] = $po['po_date'] ?? '';
            $lineMap['po_mainfeeder_value'] = $po['po_value'] ?? '';
            if (strtoupper((string) ($po['po_category'] ?? '')) === 'FINAL') {
                $lineMap['po_mainfeeder_value_final'] = $po['po_value'] ?? '';
            }
            $lineMap['po_mainfeeder_remark'] = $po['remark_po'] ?? '';

            $terminRows = $this->MMainfeeder_MyRep->getTerminRowsByPoId((int) ($po['id_po_header'] ?? 0));
            foreach ($terminRows as $termin) {
                $terminNo = (int) ($termin['termin_no'] ?? 0);
                if ($terminNo < 1 || $terminNo > 5) {
                    continue;
                }
                $prefix = 'po_mainfeeder_termin' . $terminNo . '_';
                $lineMap[$prefix . 'plan_invoice'] = $this->extractImportPlanInvoice($termin['remark_termin'] ?? '');
                $lineMap[$prefix . 'submit_invoice'] = $termin['invoice_date'] ?? '';
                $lineMap[$prefix . 'nilai_invoice'] = $termin['invoice_value'] ?? ($termin['termin_value'] ?? '');
                if (isset($lineMap[$prefix . 'sertifikat_invoice'])) {
                    $lineMap[$prefix . 'sertifikat_invoice'] = $termin['sertifikat_invoice_date'] ?? '';
                }
            }
        }

        $line = [];
        foreach ($headers as $header) {
            $line[] = isset($lineMap[$header]) ? (string) $lineMap[$header] : '';
        }
        return $line;
    }

    private function extractImportPlanInvoice($remark)
    {
        $remark = (string) $remark;
        if (preg_match('/Plan Invoice:\s*([^|]+)/i', $remark, $match)) {
            return trim((string) $match[1]);
        }
        return '';
    }

    private function normalizeImportStatus($value)
    {
        $status = strtoupper(trim((string) $value));
        $status = preg_replace('/\s+/', ' ', $status);
        if ($status === '') {
            return 'DRM';
        }
        $map = [
            'DMR' => 'DRM',
            'DOKUMENT' => 'CHECKLIST',
            'DOKUMEN' => 'CHECKLIST',
            'CHECKLIST DOKUMENT' => 'CHECKLIST',
            'CHECKLIST DOKUMEN' => 'CHECKLIST',
            'FAC' => 'CHECKLIST',
            'CLOSED' => 'DONE',
            'CONSTRUCTION' => 'IMPLEMENTASI',
            'CONSTRUCT' => 'IMPLEMENTASI',
            'FINISHING' => 'IMPLEMENTASI',
            'RECTIF' => 'IMPLEMENTASI',
            'RECTIFICATION' => 'IMPLEMENTASI',
        ];
        if (isset($map[$status])) {
            return $map[$status];
        }
        return in_array($status, ['DRM', 'IMPLEMENTASI', 'ATP', 'CHECKLIST', 'DONE'], true) ? $status : 'DRM';
    }

    private function exportMainfeederStatus($value)
    {
        $status = strtoupper(trim((string) $value));
        return $status === 'CHECKLIST' ? 'CHECKLIST DOKUMENT' : $status;
    }

    private function generateMainfeederImportCode(array $row)
    {
        $poNumber = strtoupper(trim((string) ($row['po_mainfeeder_number'] ?? '')));
        if ($poNumber !== '') {
            $code = preg_replace('/[^A-Z0-9]+/', '', $poNumber);
            return 'MFPO' . substr($code, 0, 40);
        }
        $city = strtoupper(trim((string) ($row['city_name'] ?? '')));
        $name = strtoupper(trim((string) ($row['mainfeeder_name'] ?? '')));
        $hash = strtoupper(substr(md5($city . '|' . $name), 0, 10));
        $cityCode = preg_replace('/[^A-Z0-9]+/', '', $city);
        return 'MF' . substr($cityCode, 0, 8) . $hash;
    }

    private function normalizeAtpStatus($value)
    {
        $status = strtoupper(trim((string) $value));
        if ($status === '') {
            return null;
        }
        if (in_array($status, ['DONE', 'PUNCLIST'], true)) {
            return $status;
        }
        if (in_array($status, ['PUNCHLIST', 'PUNCH LIST'], true)) {
            return 'PUNCLIST';
        }
        return null;
    }

    private function buildMainfeederImportRemark(array $row)
    {
        $remarks = [];
        $base = trim((string) ($row['remark_mainfeeder'] ?? ''));
        if ($base !== '') {
            $remarks[] = $base;
        }
        $ntp = trim((string) ($row['nomor_ntp'] ?? ''));
        if ($ntp !== '') {
            $remarks[] = 'Nomor NTP: ' . $ntp;
        }
        $tanggalNtp = trim((string) ($row['tanggal_ntp'] ?? ''));
        if ($tanggalNtp !== '') {
            $remarks[] = 'Tanggal NTP: ' . $tanggalNtp;
        }
        return implode(' | ', $remarks);
    }

    private function getImportPoValue(array $row)
    {
        $category = $this->normalizePoCategory($row['po_mainfeeder_category'] ?? 'INITIAL');
        $value = $this->normalizeNumber($row['po_mainfeeder_value'] ?? 0);
        $finalValue = $this->normalizeNumber($row['po_mainfeeder_value_final'] ?? 0);
        if ($category === 'FINAL' && $finalValue > 0) {
            return $finalValue;
        }
        return $value > 0 ? $value : $finalValue;
    }

    private function normalizePoCategory($value)
    {
        $category = strtoupper(trim((string) $value));
        return in_array($category, ['INITIAL', 'FINAL', 'AMANDMENT'], true) ? $category : 'INITIAL';
    }

    private function normalizePoStatus($value)
    {
        $status = strtoupper(trim((string) $value));
        if ($status === '') {
            return 'ISSUED';
        }
        $allowed = ['NOT ISSUED', 'ISSUED', 'PARTIAL PAYMENT', 'FULLY PAID', 'CLOSED'];
        return in_array($status, $allowed, true) ? $status : $status;
    }

    private function normalizeBooleanInt($value)
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '') {
            return 1;
        }
        return in_array($value, ['1', 'Y', 'YES', 'TRUE', 'ON TARGET'], true) ? 1 : 0;
    }

    private function parseBoqMainfeeder($fullPath)
    {
        $this->loadPHPExcel();
        $excel = PHPExcel_IOFactory::load($fullPath);
        $sheet = $excel->getSheetByName('BoQ NRO All Feeder');
        if (!$sheet) {
            $sheet = $excel->getActiveSheet();
        }
        $rows = $sheet->toArray(null, true, true, true);
        $master = $this->MMainfeeder_MyRep->getMasterBoqItems();
        $lookup = [];
        foreach ($master as $item) {
            foreach (['excel_item_name', 'item_name'] as $key) {
                $name = $this->normalizeBoqName($item[$key] ?? '');
                if ($name !== '') {
                    $lookup[$name] = $item;
                    $lookup[$this->compactBoqName($name)] = $item;
                }
            }
        }

        $items = [];
        foreach ($rows as $row) {
            $desc = $this->normalizeBoqName($row['B'] ?? '');
            $lookupKey = isset($lookup[$desc]) ? $desc : $this->compactBoqName($desc);
            if ($desc === '' || !isset($lookup[$lookupKey])) {
                continue;
            }
            $qty = $this->normalizeNumber($row['D'] ?? 0) + $this->normalizeNumber($row['E'] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            $masterItem = $lookup[$lookupKey];
            $boqItemId = (int) ($masterItem['id_boq_item'] ?? 0);
            if ($boqItemId <= 0) {
                continue;
            }
            if (!isset($items[$boqItemId])) {
                $items[$boqItemId] = [
                    'id_boq_item' => $boqItemId,
                    'qty_boq' => 0,
                    'jumlah_foto' => (int) ($masterItem['default_photo_qty'] ?? 0),
                    'remarks_rule' => (string) ($masterItem['remarks_rule'] ?? 'SESUAI ITEM'),
                    'target_foto_required' => 0,
                    'item_note' => null,
                ];
            }
            $items[$boqItemId]['qty_boq'] += $qty;
            $items[$boqItemId]['target_foto_required'] += (int) ($masterItem['default_photo_qty'] ?? 0);
        }

        return array_values($items);
    }

    private function handleAtpUpload($mainfeederId, $inputName, $docType, $remark)
    {
        if (empty($_FILES[$inputName]['name'])) {
            return;
        }
        $upload = $this->uploadFile($inputName, './uploads/atp_myrep_mainfeeder/', 'MF_ATP_' . (int) $mainfeederId . '_' . $docType);
        if (!empty($upload['status'])) {
            $this->MMainfeeder_MyRep->saveAtpFile((int) $mainfeederId, [
                'doc_type' => $docType,
                'file_name' => $upload['file_name'],
                'file_path' => $upload['file_path'],
                'remark' => $remark,
                'uploaded_by' => (int) $this->session->userdata('id_user'),
            ]);
        }
    }

    private function uploadFile($inputName, $uploadDir, $prefix)
    {
        if (empty($_FILES[$inputName]['name'])) {
            return ['status' => false, 'message' => 'File wajib dipilih.'];
        }
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        $extension = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
        $fileName = $this->safeName($prefix) . '_' . date('YmdHis') . '.' . $extension;
        $this->upload->initialize([
            'upload_path' => $uploadDir,
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png|csv',
            'max_size' => 102400,
            'file_name' => $fileName,
            'overwrite' => true,
        ]);
        if (!$this->upload->do_upload($inputName)) {
            return ['status' => false, 'message' => strip_tags($this->upload->display_errors())];
        }
        $fileData = $this->upload->data();
        return [
            'status' => true,
            'file_name' => $fileData['file_name'],
            'file_path' => trim($uploadDir, './') . '/' . $fileData['file_name'],
        ];
    }

    private function uploadMultipleFiles($inputName, $uploadDir, $prefix)
    {
        if (empty($_FILES[$inputName]['name']) || !is_array($_FILES[$inputName]['name'])) {
            return [];
        }
        $files = $_FILES[$inputName];
        $rows = [];
        foreach ($files['name'] as $index => $name) {
            if ($name === '') {
                continue;
            }
            $_FILES['single_upload'] = [
                'name' => $files['name'][$index],
                'type' => $files['type'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'error' => $files['error'][$index],
                'size' => $files['size'][$index],
            ];
            $upload = $this->uploadFile('single_upload', $uploadDir, $prefix . '_' . ($index + 1));
            if (!empty($upload['status'])) {
                $rows[] = [
                    'file_name' => $upload['file_name'],
                    'file_path' => $upload['file_path'],
                    'caption' => '',
                ];
            }
        }
        unset($_FILES['single_upload']);
        return $rows;
    }

    private function readFirstSheetData($filePath)
    {
        $this->loadPHPExcel();
        return PHPExcel_IOFactory::load($filePath)->getActiveSheet()->toArray(null, true, true, true);
    }

    private function readCsvSheetData($filePath)
    {
        $this->loadPHPExcel();
        $rows = [];
        if (($handle = fopen($filePath, 'r')) === false) {
            return $rows;
        }
        $rowIndex = 1;
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $row = [];
            foreach ($data as $index => $value) {
                $row[PHPExcel_Cell::stringFromColumnIndex($index)] = $value;
            }
            $rows[$rowIndex++] = $row;
        }
        fclose($handle);
        return $rows;
    }

    private function loadPHPExcel()
    {
        if (!class_exists('PHPExcel')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        }
        if (!class_exists('PHPExcel_IOFactory')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel/IOFactory.php';
        }
    }

    private function normalizeHeaderName($header)
    {
        $header = strtolower(trim((string) $header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        return trim($header, '_');
    }

    private function normalizeBoqName($value)
    {
        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/\s+/', ' ', $value);
        return $value;
    }

    private function compactBoqName($value)
    {
        $value = $this->normalizeBoqName($value);
        $value = str_replace([' CORE ', ' CORES ', '  TYPE '], [' ', ' ', ' TYPE '], ' ' . $value . ' ');
        $value = str_replace(['G.652.D-ADSS', 'G652DADSS'], 'G652DADSS', $value);
        return preg_replace('/[^A-Z0-9]+/', '', $value);
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (is_numeric($value)) {
            try {
                if (!class_exists('PHPExcel_Shared_Date')) {
                    require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel/Shared/Date.php';
                }
                return PHPExcel_Shared_Date::ExcelToPHPObject($value)->format('Y-m-d');
            } catch (Exception $e) {
            }
        }
        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function normalizeNumber($value)
    {
        $normalized = preg_replace('/[^0-9.,-]/', '', (string) $value);
        if ($normalized === '' || $normalized === null || $normalized === '-') {
            return 0;
        }
        if (strpos($normalized, ',') !== false && strpos($normalized, '.') !== false) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } else {
            $normalized = str_replace(',', '.', $normalized);
        }
        return is_numeric($normalized) ? (float) $normalized : 0;
    }

    private function safeName($value)
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $value);
    }

    private function isApprover()
    {
        $level = (string) $this->session->userdata('nama_level');
        $lokasi = strtoupper(trim((string) $this->session->userdata('lokasi_user')));
        return $level === 'Super Admin' || $lokasi === 'HO';
    }

    private function redirectBack($fallbackUri)
    {
        $returnUrl = trim((string) $this->input->post('return_url'));
        if ($this->isInternalUrl($returnUrl)) {
            redirect($returnUrl);
            return;
        }
        redirect($fallbackUri);
    }

    private function isInternalUrl($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return false;
        }
        $baseUrl = rtrim(base_url(), '/') . '/';
        return strpos($url, $baseUrl) === 0;
    }

    private function stageDetailUri(array $mainfeeder)
    {
        $mainfeederId = (int) ($mainfeeder['id_mainfeeder'] ?? 0);
        $status = strtoupper(trim((string) ($mainfeeder['current_status'] ?? 'DRM')));
        if ($status === 'IMPLEMENTASI') {
            return 'Implementasi_BOQ_MyRep/mainfeeder/' . $mainfeederId;
        }
        if ($status === 'ATP') {
            return 'ATP_MyRep/mainfeeder/' . $mainfeederId;
        }
        if ($status === 'PO') {
            return 'PO_MyRep/mainfeeder/' . $mainfeederId;
        }
        if ($status === 'CHECKLIST' || $status === 'DONE') {
            return 'Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId;
        }
        return 'DRM_MyRep/mainfeeder/' . $mainfeederId;
    }

    private function jsonResponse($status, $message, $extra = [])
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array_merge([
                'status' => (bool) $status,
                'message' => (string) $message,
            ], $extra)));
    }

    private function requireLogin()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            exit;
        }
    }
}
