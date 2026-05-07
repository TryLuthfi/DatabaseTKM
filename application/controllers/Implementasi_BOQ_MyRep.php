<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Implementasi_BOQ_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MImplementasi_BOQ_MyRep');
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

        $data['title'] = 'Implementasi BOQ MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['isReady'] = $this->MImplementasi_BOQ_MyRep->tablesReady();
        $data['cityOptions'] = $this->MImplementasi_BOQ_MyRep->getCityOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MImplementasi_BOQ_MyRep->getRows($selectedCity, $selectedStatus)
            : [];
        $data['summary'] = $this->MImplementasi_BOQ_MyRep->getDashboardSummary($data['clusterRows']);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Implementasi_BOQ_MyRep/index', $data);
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
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $cluster = $this->MImplementasi_BOQ_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data implementasi cluster tidak ditemukan.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $compareRows = $this->MImplementasi_BOQ_MyRep->getBaselineCompareRows($clusterId);
        $historyMap = $this->MImplementasi_BOQ_MyRep->getProgressHistoryMap($clusterId);

        $data['title'] = 'Detail Implementasi BOQ MyRep';
        $data['cluster'] = $cluster;
        $data['compareRows'] = $compareRows;
        $data['historyMap'] = $historyMap;
        $data['canApprove'] = $this->isApprover();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Implementasi_BOQ_MyRep/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function printComplyPdf($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $cluster = $this->MImplementasi_BOQ_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data implementasi cluster tidak ditemukan.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $complyGroups = $this->MImplementasi_BOQ_MyRep->getApprovedComplyPrintGroups($clusterId);
        if (empty($complyGroups)) {
            $this->session->set_flashdata('error', 'Belum ada foto comply APPROVED yang bisa dicetak.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId . '#impl-comply-pane');
            return;
        }

        if (!class_exists('TCPDF')) {
            $tcpdfPath = $this->resolveTcpdfPath();
            if ($tcpdfPath === '') {
                redirect('Implementasi_BOQ_MyRep/previewComplyPdf/' . $clusterId);
                return;
            }

            require_once $tcpdfPath;
        }

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('DatabaseTKM');
        $pdf->SetAuthor('DatabaseTKM');
        $pdf->SetTitle('Foto Comply - ' . (string) ($cluster['cluster_name'] ?? 'Cluster'));
        $pdf->SetSubject('Foto Comply Implementasi BOQ MyRep');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(true, 8);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($complyGroups as $sectionTitle => $photos) {
            $photoChunks = array_chunk(array_values($photos), 6);
            foreach ($photoChunks as $photoChunk) {
                $pdf->AddPage();
                $this->renderComplyPdfHeader($pdf, $cluster, (string) $sectionTitle);
                $this->renderComplyPdfPhotos($pdf, $photoChunk);
            }
        }

        $fileName = 'Foto_Comply_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($cluster['cluster_name'] ?? ('Cluster_' . $clusterId))) . '.pdf';
        $pdf->Output($fileName, 'I');
    }

    public function previewComplyPdf($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $cluster = $this->MImplementasi_BOQ_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data implementasi cluster tidak ditemukan.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $complyGroups = $this->MImplementasi_BOQ_MyRep->getApprovedComplyPrintGroups($clusterId);
        if (empty($complyGroups)) {
            $this->session->set_flashdata('error', 'Belum ada foto comply APPROVED yang bisa dipreview.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId . '#impl-comply-pane');
            return;
        }

        $data['title'] = 'Preview Foto Comply';
        $data['cluster'] = $cluster;
        $data['complyGroups'] = $complyGroups;
        $data['pdfUrl'] = base_url('Implementasi_BOQ_MyRep/printComplyPdf/' . $clusterId);
        $data['tcpdfAvailable'] = class_exists('TCPDF') || $this->resolveTcpdfPath() !== '';

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Implementasi_BOQ_MyRep/preview_comply_pdf', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveProgress()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MImplementasi_BOQ_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel implementasi BOQ belum tersedia.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $progressDate = $this->normalizeDate($this->input->post('progress_date'));
        $statusProgress = strtoupper(trim((string) $this->input->post('status_progress')));
        $remarkProgress = trim((string) $this->input->post('remark_progress'));
        $progressItems = (array) $this->input->post('progress_items');

        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Cluster implementasi tidak valid.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        if ($progressDate === null) {
            $this->session->set_flashdata('error', 'Tanggal progress wajib diisi.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        if (!in_array($statusProgress, ['ON PROGRESS', 'DONE'], true)) {
            $statusProgress = 'ON PROGRESS';
        }

        if (empty($progressItems)) {
            $this->session->set_flashdata('error', 'Minimal 1 item implementasi wajib dipilih.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        $savedCount = 0;
        $compareRows = $this->MImplementasi_BOQ_MyRep->getBaselineCompareRows($clusterId);
        $compareRowMap = [];
        foreach ($compareRows as $compareRow) {
            $compareRowMap[(int) ($compareRow['id_boq_baseline_item'] ?? 0)] = $compareRow;
        }

        foreach ($progressItems as $itemKey => $itemPayload) {
            $baselineItemId = (int) ($itemPayload['id_boq_baseline_item'] ?? $itemKey);
            $qtyProgress = $this->normalizeNumber($itemPayload['qty_progress'] ?? 0);
            $itemContext = $compareRowMap[$baselineItemId] ?? [];

            if ($baselineItemId <= 0 || $qtyProgress <= 0 || empty($itemContext)) {
                continue;
            }

            $photoRows = $this->uploadProgressPhotos($clusterId, $baselineItemId, (string) $itemKey, $itemContext, $qtyProgress);
            if ($photoRows === false) {
                redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
                return;
            }

            if (empty($photoRows)) {
                $this->session->set_flashdata('error', 'Setiap item wajib memiliki minimal 1 foto evidence.');
                redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
                return;
            }

            $result = $this->MImplementasi_BOQ_MyRep->createProgressEntry($clusterId, $baselineItemId, [
                'progress_date' => $progressDate,
                'qty_progress' => $qtyProgress,
                'status_progress' => $statusProgress,
                'remark_progress' => $remarkProgress,
                'created_by' => $userId,
                'updated_by' => $userId,
            ], $photoRows);

            if ($result > 0) {
                $savedCount++;
            }
        }

        $this->session->set_flashdata($savedCount > 0 ? 'success' : 'error', $savedCount > 0 ? ('Progress implementasi berhasil disimpan untuk ' . $savedCount . ' item.') : 'Gagal menyimpan progress implementasi.');
        redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
    }

    public function deleteProgress()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MImplementasi_BOQ_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel implementasi BOQ belum tersedia.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $progressItemId = (int) $this->input->post('progress_item_id');

        if ($clusterId <= 0 || $progressItemId <= 0) {
            $this->session->set_flashdata('error', 'Data progress implementasi tidak valid.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $deleted = $this->MImplementasi_BOQ_MyRep->deleteProgressEntry($clusterId, $progressItemId);
        $this->session->set_flashdata(
            $deleted ? 'success' : 'error',
            $deleted ? 'History progress implementasi berhasil dihapus.' : 'Gagal menghapus history progress implementasi.'
        );

        redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
    }

    public function approveComplyPhoto()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve foto comply.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $photoId = (int) $this->input->post('photo_id');
        $remark = trim((string) $this->input->post('review_remark'));

        $photo = $this->MImplementasi_BOQ_MyRep->getProgressPhotoById($photoId);
        if ($clusterId <= 0 || empty($photo) || (int) ($photo['id_myrep_cluster'] ?? 0) !== $clusterId) {
            $this->session->set_flashdata('error', 'Foto comply tidak ditemukan.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        if (strtoupper(trim((string) ($photo['photo_category'] ?? 'HARIAN'))) !== 'COMPLY') {
            $this->session->set_flashdata('error', 'Hanya foto comply yang bisa di-review HO.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $result = $this->MImplementasi_BOQ_MyRep->updateProgressPhotoReviewStatus($photoId, 'APPROVED', (int) $this->session->userdata('id_user'), $remark);
        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Foto comply berhasil di-approve.' : 'Gagal approve foto comply.');
        redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
    }

    public function rejectComplyPhoto()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject foto comply.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $photoId = (int) $this->input->post('photo_id');
        $remark = trim((string) $this->input->post('review_remark'));

        $photo = $this->MImplementasi_BOQ_MyRep->getProgressPhotoById($photoId);
        if ($clusterId <= 0 || empty($photo) || (int) ($photo['id_myrep_cluster'] ?? 0) !== $clusterId) {
            $this->session->set_flashdata('error', 'Foto comply tidak ditemukan.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        if (strtoupper(trim((string) ($photo['photo_category'] ?? 'HARIAN'))) !== 'COMPLY') {
            $this->session->set_flashdata('error', 'Hanya foto comply yang bisa di-review HO.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        if ($remark === '') {
            $this->session->set_flashdata('error', 'Alasan reject foto comply wajib diisi.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $result = $this->MImplementasi_BOQ_MyRep->updateProgressPhotoReviewStatus($photoId, 'REJECTED', (int) $this->session->userdata('id_user'), $remark);
        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Foto comply berhasil di-reject.' : 'Gagal reject foto comply.');
        redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
    }

    public function uploadComplyPhoto()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MImplementasi_BOQ_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel implementasi BOQ belum tersedia.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $baselineItemId = (int) $this->input->post('baseline_item_id');
        $complyLabel = trim((string) $this->input->post('comply_label'));
        $progressDate = date('Y-m-d');
        $redirectUrl = 'Implementasi_BOQ_MyRep/detail/' . $clusterId . '#impl-comply-pane';

        if ($clusterId <= 0 || $baselineItemId <= 0) {
            $this->session->set_flashdata('error', 'Item comply tidak valid.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        if ($complyLabel === '') {
            $this->session->set_flashdata('error', 'Nama / nomor comply wajib diisi.');
            redirect($redirectUrl);
            return;
        }

        $compareRows = $this->MImplementasi_BOQ_MyRep->getBaselineCompareRows($clusterId);
        $itemContext = [];
        foreach ($compareRows as $compareRow) {
            if ((int) ($compareRow['id_boq_baseline_item'] ?? 0) === $baselineItemId) {
                $itemContext = $compareRow;
                break;
            }
        }

        if (empty($itemContext) || (int) ($itemContext['comply_enabled'] ?? 0) !== 1) {
            $this->session->set_flashdata('error', 'Item yang dipilih tidak mendukung foto comply.');
            redirect($redirectUrl);
            return;
        }

        $photoRows = $this->uploadStandaloneComplyPhotos($clusterId, $baselineItemId, $itemContext, $complyLabel);
        if ($photoRows === false) {
            redirect($redirectUrl);
            return;
        }

        if (empty($photoRows)) {
            $this->session->set_flashdata('error', 'Minimal 1 foto comply wajib diupload.');
            redirect($redirectUrl);
            return;
        }

        $created = $this->MImplementasi_BOQ_MyRep->createProgressEntry($clusterId, $baselineItemId, [
            'progress_date' => $progressDate,
            'qty_progress' => 0,
            'status_progress' => 'ON PROGRESS',
            'remark_progress' => 'Upload Foto Comply - ' . $complyLabel,
            'created_by' => (int) $this->session->userdata('id_user'),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ], $photoRows);

        $this->session->set_flashdata(
            $created > 0 ? 'success' : 'error',
            $created > 0 ? 'Foto comply berhasil diupload dan menunggu approval HO.' : 'Gagal menyimpan foto comply.'
        );
        redirect($redirectUrl);
    }

    private function uploadProgressPhotos($clusterId, $baselineItemId, $itemKey, $itemContext, $qtyProgress)
    {
        $clusterId = (int) $clusterId;
        $baselineItemId = (int) $baselineItemId;
        $itemName = trim((string) ($itemContext['item_name'] ?? 'Item'));
        $complyEnabled = (int) ($itemContext['comply_enabled'] ?? 0) === 1;
        $complyMode = strtoupper(trim((string) ($itemContext['comply_entry_limit_mode'] ?? 'NONE')));
        $complyPhotoPerLabel = (int) ($itemContext['comply_photo_per_label'] ?? 0);
        $allComplyLabels = (array) $this->input->post('comply_labels');
        $complyLabels = isset($allComplyLabels[$itemKey]) && is_array($allComplyLabels[$itemKey]) ? $allComplyLabels[$itemKey] : [];
        $files = $_FILES['progress_photos'] ?? null;
        if (empty($files['name'][$itemKey]) || !is_array($files['name'][$itemKey])) {
            return [];
        }

        $uploadDir = './uploads/myrep_boq_progress/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedRows = [];
        $totalFiles = count($files['name'][$itemKey]);

        for ($i = 0; $i < $totalFiles; $i++) {
            if (empty($files['name'][$itemKey][$i])) {
                continue;
            }

            $_FILES['single_progress_photo'] = [
                'name' => $files['name'][$itemKey][$i],
                'type' => $files['type'][$itemKey][$i],
                'tmp_name' => $files['tmp_name'][$itemKey][$i],
                'error' => $files['error'][$itemKey][$i],
                'size' => $files['size'][$itemKey][$i],
            ];

            $extension = pathinfo((string) $files['name'][$itemKey][$i], PATHINFO_EXTENSION);
            $fileName = 'BOQ_PROGRESS_' . $clusterId . '_' . $baselineItemId . '_' . date('YmdHis') . '_' . ($i + 1) . '.' . $extension;
            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size' => 30720,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('single_progress_photo')) {
                $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
                return false;
            }

            $fileData = $this->upload->data();
            $uploadedRows[] = [
                'file_name' => $fileData['file_name'],
                'file_path' => 'uploads/myrep_boq_progress/' . $fileData['file_name'],
                'caption' => '',
                'photo_category' => 'HARIAN',
                'comply_label' => null,
            ];
        }

        if ($complyEnabled && $qtyProgress > 0) {
            $requiredEntries = $complyMode === 'MATCH_QTY' ? (int) ceil($qtyProgress) : 0;
            $complyFiles = $_FILES['comply_photos'] ?? null;
            $validEntries = 0;

            foreach ($complyLabels as $entryIndex => $rawLabel) {
                $label = trim((string) $rawLabel);
                $entryFileNames = $complyFiles['name'][$itemKey][$entryIndex] ?? [];
                $entryHasFiles = is_array($entryFileNames) && count(array_filter($entryFileNames, static function ($value) {
                    return trim((string) $value) !== '';
                })) > 0;

                if ($label === '' && !$entryHasFiles) {
                    continue;
                }

                if ($label === '') {
                    $this->session->set_flashdata('error', 'Label comply untuk item ' . $itemName . ' wajib diisi.');
                    return false;
                }

                if (!$entryHasFiles) {
                    $this->session->set_flashdata('error', 'Foto comply untuk ' . $label . ' pada item ' . $itemName . ' wajib diupload.');
                    return false;
                }

                $entryTotalFiles = count($entryFileNames);
                $entryUploadedCount = 0;
                for ($i = 0; $i < $entryTotalFiles; $i++) {
                    if (empty($entryFileNames[$i])) {
                        continue;
                    }

                    $_FILES['single_progress_photo'] = [
                        'name' => $complyFiles['name'][$itemKey][$entryIndex][$i],
                        'type' => $complyFiles['type'][$itemKey][$entryIndex][$i],
                        'tmp_name' => $complyFiles['tmp_name'][$itemKey][$entryIndex][$i],
                        'error' => $complyFiles['error'][$itemKey][$entryIndex][$i],
                        'size' => $complyFiles['size'][$itemKey][$entryIndex][$i],
                    ];

                    $extension = pathinfo((string) $complyFiles['name'][$itemKey][$entryIndex][$i], PATHINFO_EXTENSION);
                    $safeLabel = preg_replace('/[^A-Za-z0-9_\-]/', '_', $label);
                    $fileName = 'BOQ_COMPLY_' . $clusterId . '_' . $baselineItemId . '_' . $safeLabel . '_' . date('YmdHis') . '_' . ($i + 1) . '.' . $extension;
                    $config = [
                        'upload_path' => $uploadDir,
                        'allowed_types' => 'jpg|jpeg|png|webp',
                        'max_size' => 30720,
                        'file_name' => $fileName,
                        'overwrite' => true,
                    ];

                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('single_progress_photo')) {
                        $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
                        return false;
                    }

                    $fileData = $this->upload->data();
                    $uploadedRows[] = [
                        'file_name' => $fileData['file_name'],
                        'file_path' => 'uploads/myrep_boq_progress/' . $fileData['file_name'],
                        'caption' => 'Comply - ' . $label,
                        'photo_category' => 'COMPLY',
                        'comply_label' => $label,
                        'status_photo' => 'UPLOADED',
                    ];
                    $entryUploadedCount++;
                }

                if ($entryUploadedCount < max($complyPhotoPerLabel, 1)) {
                    $this->session->set_flashdata('error', 'Minimal ' . max($complyPhotoPerLabel, 1) . ' foto comply wajib diupload untuk ' . $label . ' pada item ' . $itemName . '.');
                    return false;
                }

                $validEntries++;
            }

            if ($complyMode === 'MATCH_QTY' && $validEntries !== $requiredEntries) {
                $this->session->set_flashdata('error', 'Jumlah entry comply untuk item ' . $itemName . ' harus mengikuti qty implementasi, yaitu ' . $requiredEntries . ' entry.');
                return false;
            }

            if ($complyMode === 'FLEXIBLE' && $validEntries <= 0) {
                $this->session->set_flashdata('error', 'Minimal 1 entry foto comply wajib diisi untuk item ' . $itemName . '.');
                return false;
            }
        }

        return $uploadedRows;
    }

    private function uploadStandaloneComplyPhotos($clusterId, $baselineItemId, $itemContext, $label)
    {
        $clusterId = (int) $clusterId;
        $baselineItemId = (int) $baselineItemId;
        $itemName = trim((string) ($itemContext['item_name'] ?? 'Item'));
        $requiredPhotoCount = max((int) ($itemContext['comply_photo_per_label'] ?? 0), 1);
        $files = $_FILES['comply_photos_single'] ?? null;

        if (empty($files['name']) || !is_array($files['name'])) {
            return [];
        }

        $validFileNames = array_values(array_filter($files['name'], static function ($value) {
            return trim((string) $value) !== '';
        }));

        if (count($validFileNames) < $requiredPhotoCount) {
            $this->session->set_flashdata('error', 'Minimal ' . $requiredPhotoCount . ' foto comply wajib diupload untuk item ' . $itemName . '.');
            return false;
        }

        $uploadDir = './uploads/myrep_boq_progress/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedRows = [];
        $safeLabel = preg_replace('/[^A-Za-z0-9_\-]/', '_', $label);
        $uploadIndex = 0;

        foreach ($files['name'] as $index => $rawFileName) {
            if (trim((string) $rawFileName) === '') {
                continue;
            }

            $_FILES['single_progress_photo'] = [
                'name' => $files['name'][$index],
                'type' => $files['type'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'error' => $files['error'][$index],
                'size' => $files['size'][$index],
            ];

            $extension = pathinfo((string) $files['name'][$index], PATHINFO_EXTENSION);
            $fileName = 'BOQ_COMPLY_' . $clusterId . '_' . $baselineItemId . '_' . $safeLabel . '_' . date('YmdHis') . '_' . ($uploadIndex + 1) . '.' . $extension;
            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size' => 30720,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('single_progress_photo')) {
                $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
                return false;
            }

            $fileData = $this->upload->data();
            $uploadedRows[] = [
                'file_name' => $fileData['file_name'],
                'file_path' => 'uploads/myrep_boq_progress/' . $fileData['file_name'],
                'caption' => 'Comply - ' . $label,
                'photo_category' => 'COMPLY',
                'comply_label' => $label,
                'status_photo' => 'UPLOADED',
            ];
            $uploadIndex++;
        }

        return $uploadedRows;
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function normalizeNumber($value)
    {
        $normalized = preg_replace('/[^0-9.,-]/', '', (string) $value);
        if ($normalized === '' || $normalized === null) {
            return 0;
        }

        $hasComma = strpos($normalized, ',') !== false;
        $dotCount = substr_count($normalized, '.');

        if ($hasComma) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif ($dotCount > 1) {
            $normalized = str_replace('.', '', $normalized);
        }

        return (float) $normalized;
    }

    private function isApprover()
    {
        return $this->session->userdata('lokasi_user') === 'HO'
            || $this->session->userdata('nama_level') === 'Super Admin';
    }

    private function renderComplyPdfHeader($pdf, $cluster, $sectionTitle)
    {
        $tkmLogo = $this->resolvePdfLogoFile([
            'assets/dist/img/logotkmsolid.png',
            'assets/dist/img/logo_size.jpg',
            'assets/dist/img/logo_size_invert.jpg',
        ]);
        $myrepLogo = $this->resolvePdfLogoFile([
            'assets/dist/img/logoweb.png',
            'assets/dist/img/logo_size.jpg',
            'assets/dist/img/logo_size_invert.jpg',
        ]);
        $region = trim((string) ($cluster['regional_name'] ?? '-'));
        $oltName = trim((string) ($cluster['nama_olt'] ?? '-'));
        $clusterName = trim((string) ($cluster['cluster_name'] ?? '-'));
        $clusterCode = trim((string) ($cluster['cluster_code'] ?? '-'));
        $left = 8;
        $top = 8;
        $width = 194;
        $headerHeight = 24;

        $pdf->SetLineWidth(0.25);
        $pdf->Rect($left, $top, $width, $headerHeight);
        $pdf->Rect($left, $top, 72, $headerHeight);
        $pdf->Rect($left + 72, $top, 20, $headerHeight);
        $pdf->Rect($left + 92, $top, 102, $headerHeight);

        if ($tkmLogo !== '') {
            $pdf->Image($tkmLogo, $left + 2, $top + 2, 26, 8, '', '', '', false, 150, '', false, false, 0, false, false, false);
        }
        if ($myrepLogo !== '') {
            $pdf->Image($myrepLogo, $left + 31, $top + 2, 26, 8, '', '', '', false, 150, '', false, false, 0, false, false, false);
        }

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetXY($left, $top + 14);
        $pdf->Cell(72, 5, 'EMR FTTH PROJECT', 0, 0, 'C');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY($left + 72, $top + 8);
        $pdf->MultiCell(20, 4, "FOTO\nCOMPLY", 0, 'C', false, 1);

        $labels = ['Region', 'OLT Name', 'Cluster Name', 'Cluster ID'];
        $values = [$region, $oltName !== '' ? $oltName : '-', $clusterName, $clusterCode !== '' ? $clusterCode : (string) ($cluster['id_myrep_cluster'] ?? '-')];
        for ($i = 0; $i < 4; $i++) {
            $y = $top + ($i * 6);
            $pdf->Rect($left + 92, $y, 30, 6);
            $pdf->Rect($left + 122, $y, 72, 6);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetXY($left + 93, $y + 1.2);
            $pdf->Cell(28, 3, $labels[$i], 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetXY($left + 123, $y + 1.2);
            $pdf->Cell(70, 3, $values[$i], 0, 0, 'L');
        }

        $pdf->Rect($left, $top + $headerHeight, 24, 8);
        $pdf->Rect($left + 24, $top + $headerHeight, 170, 8);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY($left, $top + $headerHeight + 1);
        $pdf->Cell(24, 5, $sectionTitle, 0, 0, 'C');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetXY($left + 26, $top + $headerHeight + 1);
        $pdf->Cell(60, 3, 'Foto Comply Approved', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY($left + 26, $top + $headerHeight + 4.5);
        $pdf->Cell(120, 3, 'Cluster: ' . $clusterName, 0, 0, 'L');
    }

    private function renderComplyPdfPhotos($pdf, $photos)
    {
        $startY = 42;
        $leftX = 8;
        $rightX = 106;
        $tileWidth = 90;
        $tileHeight = 72;
        $imageHeight = 60;
        $rowGap = 6;

        foreach (array_values($photos) as $index => $photo) {
            $column = $index % 2;
            $row = (int) floor($index / 2);
            $x = $column === 0 ? $leftX : $rightX;
            $y = $startY + ($row * ($tileHeight + $rowGap));

            $description = trim((string) ($photo['comply_label'] ?? '')) !== '' ? (string) $photo['comply_label'] : (string) ($photo['file_name'] ?? 'Foto Comply');
            $caption = trim((string) ($photo['caption'] ?? ''));
            $metaLine = $caption !== '' ? $caption : ('Comply - ' . $description);
            $imagePath = $this->resolvePdfImageFile((string) ($photo['file_path'] ?? ''));

            $pdf->Rect($x, $y, $tileWidth, $tileHeight);
            $pdf->Rect($x, $y, $tileWidth, $imageHeight);
            $pdf->Rect($x, $y + $imageHeight, $tileWidth, 6);
            $pdf->Rect($x, $y + $imageHeight + 6, $tileWidth, 6);

            if ($imagePath !== '') {
                try {
                    $pdf->Image($imagePath, $x + 1.5, $y + 1.5, $tileWidth - 3, $imageHeight - 3, '', '', '', false, 150, '', false, false, 1, false, false, false);
                } catch (\Throwable $e) {
                    $pdf->SetFont('helvetica', '', 8);
                    $pdf->SetXY($x + 4, $y + 26);
                    $pdf->Cell($tileWidth - 8, 5, 'Gagal memuat gambar', 0, 0, 'C');
                }
            } else {
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetXY($x + 4, $y + 26);
                $pdf->Cell($tileWidth - 8, 5, 'File foto tidak ditemukan', 0, 0, 'C');
            }

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetXY($x + 1, $y + $imageHeight + 1.2);
            $pdf->Cell($tileWidth - 2, 4, 'Description: ' . $description, 0, 0, 'C');
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetXY($x + 1, $y + $imageHeight + 7.2);
            $pdf->Cell($tileWidth - 2, 4, $metaLine, 0, 0, 'C');
        }
    }

    private function resolvePdfImageFile($relativePath)
    {
        $relativePath = trim((string) $relativePath);
        if ($relativePath === '') {
            return '';
        }

        $fullPath = FCPATH . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
        if (!is_file($fullPath)) {
            return '';
        }

        if ((int) @filesize($fullPath) <= 0) {
            return '';
        }

        $extension = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return '';
        }

        if ($extension === 'png' && $this->isPngWithAlphaChannel($fullPath)) {
            return '';
        }

        if (@getimagesize($fullPath) === false) {
            return '';
        }

        return $fullPath;
    }

    private function resolvePdfLogoFile($relativePaths)
    {
        foreach ((array) $relativePaths as $relativePath) {
            $resolvedPath = $this->resolvePdfImageFile((string) $relativePath);
            if ($resolvedPath !== '') {
                return $resolvedPath;
            }
        }

        return '';
    }

    private function isPngWithAlphaChannel($fullPath)
    {
        $handle = @fopen($fullPath, 'rb');
        if ($handle === false) {
            return false;
        }

        $header = fread($handle, 29);
        fclose($handle);

        if ($header === false || strlen($header) < 29) {
            return false;
        }

        $signature = substr($header, 0, 8);
        if ($signature !== "\x89PNG\x0D\x0A\x1A\x0A") {
            return false;
        }

        $chunkType = substr($header, 12, 4);
        if ($chunkType !== 'IHDR') {
            return false;
        }

        $colorType = ord($header[25]);

        return in_array($colorType, [4, 6], true);
    }

    private function resolveTcpdfPath()
    {
        $candidatePaths = [
            'D:/XAMPP/phpMyAdmin/vendor/tecnickcom/tcpdf/tcpdf.php',
            dirname(FCPATH) . '/phpMyAdmin/vendor/tecnickcom/tcpdf/tcpdf.php',
            FCPATH . '../phpMyAdmin/vendor/tecnickcom/tcpdf/tcpdf.php',
            'D:/XAMPP/phpMyAdmin/vendor/tecnickcom/tcpdf/tcpdf.php',
            'D:/XAMPP/htdocs/DatabaseTKM/application/third_party/tcpdf/tcpdf.php',
        ];

        foreach ($candidatePaths as $candidatePath) {
            $normalizedPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, (string) $candidatePath);
            if (is_file($normalizedPath)) {
                return $normalizedPath;
            }

            $resolvedPath = realpath($normalizedPath);
            if ($resolvedPath !== false && is_file($resolvedPath)) {
                return $resolvedPath;
            }
        }

        $scanDirectories = [
            'D:/XAMPP/phpMyAdmin/vendor/tecnickcom/tcpdf',
            dirname(FCPATH) . '/phpMyAdmin/vendor/tecnickcom/tcpdf',
            FCPATH . '../phpMyAdmin/vendor/tecnickcom/tcpdf',
        ];

        foreach ($scanDirectories as $scanDirectory) {
            $normalizedDirectory = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, (string) $scanDirectory);
            if (!is_dir($normalizedDirectory)) {
                $resolvedDirectory = realpath($normalizedDirectory);
                if ($resolvedDirectory === false || !is_dir($resolvedDirectory)) {
                    continue;
                }
                $normalizedDirectory = $resolvedDirectory;
            }

            $tcpdfFile = rtrim($normalizedDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'tcpdf.php';
            if (is_file($tcpdfFile)) {
                return $tcpdfFile;
            }
        }

        return '';
    }
}
