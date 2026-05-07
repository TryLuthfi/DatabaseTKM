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

        $tcpdfPath = 'D:\\XAMPP\\phpMyAdmin\\vendor\\tecnickcom\\tcpdf\\tcpdf.php';
        if (!class_exists('TCPDF')) {
            if (!is_file($tcpdfPath)) {
                $this->session->set_flashdata('error', 'Library PDF TCPDF tidak ditemukan di server.');
                redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId . '#impl-comply-pane');
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

        $headerHtml = $this->buildComplyPdfHeaderHtml($cluster);
        $tileWidth = '48%';

        foreach ($complyGroups as $sectionTitle => $photos) {
            $pdf->AddPage();
            $html = $headerHtml;
            $html .= '<table cellpadding="4" cellspacing="0" border="1" width="100%" style="font-size:9px;">';
            $html .= '<tr>';
            $html .= '<td width="22%" align="center" style="font-weight:bold; font-size:16px;">' . htmlspecialchars((string) $sectionTitle, ENT_QUOTES) . '</td>';
            $html .= '<td width="78%" style="font-size:9px;">';
            $html .= '<strong>Foto Comply Approved</strong><br>';
            $html .= 'Cluster: ' . htmlspecialchars((string) ($cluster['cluster_name'] ?? '-'), ENT_QUOTES);
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</table><br>';

            $html .= '<table cellpadding="4" cellspacing="0" border="0" width="100%">';
            foreach (array_values($photos) as $index => $photo) {
                if ($index % 2 === 0) {
                    $html .= '<tr>';
                }

                $imagePath = $this->toPdfImagePath((string) ($photo['file_path'] ?? ''));
                $description = trim((string) ($photo['comply_label'] ?? '')) !== '' ? (string) $photo['comply_label'] : (string) ($photo['file_name'] ?? 'Foto Comply');
                $caption = trim((string) ($photo['caption'] ?? ''));
                $metaLine = $caption !== '' ? $caption : ('Description: ' . $description);

                $html .= '<td width="' . $tileWidth . '" valign="top">';
                $html .= '<table cellpadding="3" cellspacing="0" border="1" width="100%">';
                $html .= '<tr><td align="center" style="height:118mm;">';
                if ($imagePath !== '') {
                    $html .= '<img src="' . htmlspecialchars($imagePath, ENT_QUOTES) . '" style="width:84mm; height:108mm; object-fit:contain;">';
                } else {
                    $html .= '<div style="color:#6b7280; font-size:10px;">File foto tidak ditemukan</div>';
                }
                $html .= '</td></tr>';
                $html .= '<tr><td align="center" style="font-size:9px;"><strong>Description: ' . htmlspecialchars($description, ENT_QUOTES) . '</strong></td></tr>';
                $html .= '<tr><td align="center" style="font-size:8px;">' . htmlspecialchars($metaLine, ENT_QUOTES) . '</td></tr>';
                $html .= '</table>';
                $html .= '</td>';

                if ($index % 2 === 1) {
                    $html .= '</tr><tr><td colspan="2" height="4"></td></tr>';
                }
            }

            if (count($photos) % 2 === 1) {
                $html .= '<td width="' . $tileWidth . '"></td></tr>';
            }
            $html .= '</table>';

            $pdf->writeHTML($html, true, false, true, false, '');
        }

        $fileName = 'Foto_Comply_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($cluster['cluster_name'] ?? ('Cluster_' . $clusterId))) . '.pdf';
        $pdf->Output($fileName, 'I');
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

    private function buildComplyPdfHeaderHtml($cluster)
    {
        $tkmLogo = $this->toPdfImagePath('assets/dist/img/logotkmsolid.png');
        $myrepLogo = $this->toPdfImagePath('assets/dist/img/logoweb.png');
        $region = trim((string) ($cluster['regional_name'] ?? '-'));
        $oltName = trim((string) ($cluster['nama_olt'] ?? '-'));
        $clusterName = trim((string) ($cluster['cluster_name'] ?? '-'));
        $clusterCode = trim((string) ($cluster['cluster_code'] ?? '-'));

        $html = '<table cellpadding="4" cellspacing="0" border="1" width="100%" style="font-size:9px;">';
        $html .= '<tr>';
        $html .= '<td width="35%" align="center">';
        if ($tkmLogo !== '') {
            $html .= '<img src="' . htmlspecialchars($tkmLogo, ENT_QUOTES) . '" style="height:16mm;">';
        }
        if ($myrepLogo !== '') {
            $html .= '&nbsp;&nbsp;<img src="' . htmlspecialchars($myrepLogo, ENT_QUOTES) . '" style="height:14mm;">';
        }
        $html .= '<div style="font-size:15px; font-weight:bold; margin-top:4px;">EMR FTTH PROJECT</div>';
        $html .= '</td>';
        $html .= '<td width="13%" align="center" style="font-size:14px; font-weight:bold;">FOTO COMPLY</td>';
        $html .= '<td width="52%">';
        $html .= '<table cellpadding="2" cellspacing="0" border="1" width="100%" style="font-size:9px;">';
        $html .= '<tr><td width="35%"><strong>Region</strong></td><td width="65%">' . htmlspecialchars($region, ENT_QUOTES) . '</td></tr>';
        $html .= '<tr><td width="35%"><strong>OLT Name</strong></td><td width="65%">' . htmlspecialchars($oltName !== '' ? $oltName : '-', ENT_QUOTES) . '</td></tr>';
        $html .= '<tr><td width="35%"><strong>Cluster Name</strong></td><td width="65%">' . htmlspecialchars($clusterName, ENT_QUOTES) . '</td></tr>';
        $html .= '<tr><td width="35%"><strong>Cluster ID</strong></td><td width="65%">' . htmlspecialchars($clusterCode !== '' ? $clusterCode : (string) ($cluster['id_myrep_cluster'] ?? '-'), ENT_QUOTES) . '</td></tr>';
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table><br>';

        return $html;
    }

    private function toPdfImagePath($relativePath)
    {
        $relativePath = trim((string) $relativePath);
        if ($relativePath === '') {
            return '';
        }

        $fullPath = FCPATH . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
        if (!is_file($fullPath)) {
            return '';
        }

        return 'file:///' . str_replace('\\', '/', $fullPath);
    }
}
