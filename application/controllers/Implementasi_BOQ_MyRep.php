<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Implementasi_BOQ_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MImplementasi_BOQ_MyRep');
        $this->load->model('MMainfeeder_MyRep');
        $this->load->library('upload');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('Implementasi_BOQ_MyRep');
            $this->myrepAccess->enforceByMethod('Implementasi_BOQ_MyRep', (string) $this->router->fetch_method(), [
                'approveComplyPhoto' => 'APPROVAL_FOTO_COMPLY',
                'rejectComplyPhoto' => 'APPROVAL_FOTO_COMPLY',
                'saveDailyActivity' => 'APPROVAL_DAILY',
                'rotateProgressPhoto' => 'TAMBAH',
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
        $data['activityReady'] = $this->MImplementasi_BOQ_MyRep->activityTablesReady();
        $data['activityDefinitions'] = $this->MImplementasi_BOQ_MyRep->getDailyActivityDefinitions();
        $data['dailyActivities'] = $data['activityReady'] ? $this->MImplementasi_BOQ_MyRep->getDailyActivities($clusterId) : [];
        $data['masterBoqItems'] = $this->MImplementasi_BOQ_MyRep->getMasterBoqItems();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Implementasi_BOQ_MyRep/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveDailyActivity()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Cluster implementasi tidak valid.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        if (!$this->MImplementasi_BOQ_MyRep->activityTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel activity harian implementasi belum tersedia.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $activityDate = $this->normalizeDate($this->input->post('activity_date'));
        $scopeType = strtoupper(trim((string) $this->input->post('scope_type'))) === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
        $teamCount = max(0, (int) $this->normalizeNumber($this->input->post('team_count')));
        $workerCount = max(0, (int) $this->normalizeNumber($this->input->post('worker_count')));
        $trackerRemark = trim((string) $this->input->post('tracker_remark'));
        if ($activityDate === null) {
            $this->session->set_flashdata('error', 'Tanggal aktivitas wajib diisi.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $definitions = $this->MImplementasi_BOQ_MyRep->getDailyActivityDefinitions();
        $definitionMap = [];
        foreach ($definitions as $definition) {
            $definitionMap[(string) ($definition['activity_code'] ?? '')] = $definition;
        }
        $activityItems = (array) $this->input->post('activity_items');
        if (empty($activityItems)) {
            // Backward compatibility for single-item submit.
            $activityCode = strtoupper(trim((string) $this->input->post('activity_code')));
            $qtyActivity = $this->normalizeNumber($this->input->post('qty_activity'));
            if ($activityCode !== '' && $qtyActivity > 0) {
                $activityItems[] = [
                    'activity_code' => $activityCode,
                    'qty_activity' => $qtyActivity,
                    'unit_activity' => (string) $this->input->post('unit_activity'),
                    'remark_activity' => (string) $this->input->post('remark_activity'),
                ];
            }
        }

        if (empty($activityItems)) {
            $this->session->set_flashdata('error', 'Minimal 1 kategori aktivitas wajib ditambahkan.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $createdCount = 0;
        $allocatedQtyTotal = 0;
        $userId = (int) $this->session->userdata('id_user');
        $createdActivitySummaries = [];

        foreach ($activityItems as $index => $item) {
            $activityCode = strtoupper(trim((string) ($item['activity_code'] ?? '')));
            $qtyActivity = $this->normalizeNumber($item['qty_activity'] ?? 0);
            if ($activityCode === '' || $qtyActivity <= 0) {
                continue;
            }

            $selectedDefinition = $definitionMap[$activityCode] ?? [];
            if (empty($selectedDefinition)) {
                continue;
            }
            $activityDetail = trim((string) ($item['activity_detail'] ?? ''));

            $photoRows = $this->uploadDailyActivityPhotos($clusterId, $activityCode, 'activity_item_photos_' . $index);
            if ($photoRows === false) {
                redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
                return;
            }
            if (empty($photoRows)) {
                $this->session->set_flashdata('error', 'Setiap kategori aktivitas wajib memiliki minimal 1 foto.');
                redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
                return;
            }

            $created = $this->MImplementasi_BOQ_MyRep->createDailyActivity($clusterId, [
                'activity_date' => $activityDate,
                'activity_code' => $activityCode,
                'activity_name' => (string) ($selectedDefinition['activity_name'] ?? $activityCode),
                'activity_detail' => $activityDetail,
                'boq_type' => (string) ($selectedDefinition['boq_type'] ?? ''),
                'scope_type' => $scopeType,
                'qty_activity' => $qtyActivity,
                'unit_activity' => (string) ($selectedDefinition['default_unit'] ?? ''),
                'team_count' => $teamCount,
                'worker_count' => $workerCount,
                'remark_activity' => trim((string) ($item['remark_activity'] ?? '')),
                'created_by' => $userId,
                'updated_by' => $userId,
            ], $photoRows);

            if ($created > 0) {
                $createdCount++;
                $createdActivitySummaries[] = [
                    'activity_name' => (string) ($selectedDefinition['activity_name'] ?? $activityCode),
                    'activity_detail' => $activityDetail !== '' ? $activityDetail : '-',
                    'qty_activity' => $qtyActivity,
                    'unit_activity' => (string) ($selectedDefinition['default_unit'] ?? ''),
                    'photo_count' => count($photoRows),
                ];
                $allocatedQtyTotal += (float) $this->MImplementasi_BOQ_MyRep->applyDailyActivityToBoqProgress(
                    $clusterId,
                    $activityDate,
                    $activityCode,
                    $activityDetail,
                    $qtyActivity,
                    $userId,
                    $scopeType,
                    $trackerRemark
                );
            }
        }

        if ($createdCount <= 0) {
            $this->session->set_flashdata('error', 'Gagal menyimpan progress harian aktivitas.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $autoBoqMessage = $allocatedQtyTotal > 0
            ? (' Auto BOQ: ' . number_format((float) $allocatedQtyTotal, 2, ',', '.') . ' teralokasi.')
            : '';
        $this->notifyDailyActivityCreated($clusterId, [
            'activity_date' => $activityDate,
            'scope_type' => $scopeType,
            'team_count' => $teamCount,
            'worker_count' => $workerCount,
            'created_count' => $createdCount,
            'allocated_qty_total' => $allocatedQtyTotal,
            'activities' => $createdActivitySummaries,
        ]);
        $this->session->set_flashdata('success', $createdCount . ' aktivitas harian berhasil disimpan.' . $autoBoqMessage);
        redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
    }

    public function mainfeeder($mainfeederId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $mainfeederId = (int) $mainfeederId;
        if ($mainfeederId <= 0) {
            $selectedCity = strtoupper(trim((string) $this->input->get('city')));
            $selectedStatus = strtoupper(trim((string) $this->input->get('status')));
            $data['title'] = 'Implementasi Mainfeeder';
            $data['section'] = 'implementasi';
            $data['moduleTitle'] = 'Implementasi Mainfeeder';
            $data['detailBase'] = 'Implementasi_BOQ_MyRep/mainfeeder';
            $data['isReady'] = $this->MMainfeeder_MyRep->tablesReady();
            $data['selectedCity'] = $selectedCity;
            $data['selectedStatus'] = $selectedStatus;
            $data['cityOptions'] = $data['isReady'] ? $this->MMainfeeder_MyRep->getCityOptions() : [];
            $data['statusOptions'] = $this->MMainfeeder_MyRep->getStatusOptions();
            $data['rows'] = $data['isReady'] ? $this->MMainfeeder_MyRep->getRows($selectedCity, $selectedStatus) : [];
            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Mainfeeder_MyRep/module_index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
            return;
        }

        $mainfeeder = $this->MMainfeeder_MyRep->getById($mainfeederId);
        if (empty($mainfeeder)) {
            $this->session->set_flashdata('error', 'Data mainfeeder tidak ditemukan.');
            redirect('Implementasi_BOQ_MyRep/mainfeeder');
            return;
        }

        $data['title'] = 'Implementasi Mainfeeder';
        $data['section'] = 'implementasi';
        $data['moduleTitle'] = 'Implementasi Mainfeeder';
        $data['mainfeeder'] = $mainfeeder;
        $data['compareRows'] = $this->MMainfeeder_MyRep->getBaselineCompareRows($mainfeederId);
        $data['activityDefinitions'] = $this->MImplementasi_BOQ_MyRep->getDailyActivityDefinitions();
        $data['dailyActivities'] = $this->MMainfeeder_MyRep->getDailyActivities($mainfeederId);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Mainfeeder_MyRep/module_detail', $data);
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
        $cluster['nama_olt'] = $this->resolveClusterOltName($clusterId, $cluster);

        $dailyGroups = $this->buildDailyProgressPrintGroups($clusterId);
        $complyGroups = $this->MImplementasi_BOQ_MyRep->getApprovedComplyPrintGroups($clusterId);
        $selectedCategory = trim((string) $this->input->get('category'));
        if ($selectedCategory !== '') {
            $complyGroups = $this->filterComplyGroupsByCategory($complyGroups, $selectedCategory);
        }
        if (empty($dailyGroups) && empty($complyGroups)) {
            $this->session->set_flashdata('error', 'Belum ada foto daily progress atau foto comply APPROVED yang bisa dicetak.');
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
        $pdf->SetTitle('Daily Progress & Foto Comply - ' . (string) ($cluster['cluster_name'] ?? 'Cluster'));
        $pdf->SetSubject('Daily Progress dan Foto Comply Implementasi BOQ MyRep');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(true, 8);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($dailyGroups as $sectionTitle => $photos) {
            $photoChunks = array_chunk(array_values($photos), 8);
            foreach ($photoChunks as $photoChunk) {
                $scopeTitle = $this->resolvePrintScopeTitle($sectionTitle, $photoChunk);
                $pdf->AddPage();
                $this->renderComplyPdfHeader($pdf, $cluster, $scopeTitle, 'IMPLE ' . $scopeTitle, 'Foto Daily Progress');
                $this->renderComplyPdfPhotos($pdf, $photoChunk, 'Daily Progress');
            }
        }

        foreach ($complyGroups as $sectionTitle => $photos) {
            $photoChunks = array_chunk(array_values($photos), 8);
            foreach ($photoChunks as $photoChunk) {
                $scopeTitle = $this->resolvePrintScopeTitle('', $photoChunk);
                $pdf->AddPage();
                $this->renderComplyPdfHeader($pdf, $cluster, $scopeTitle, (string) $sectionTitle, 'Foto Comply Approved');
                $this->renderComplyPdfPhotos($pdf, $photoChunk, 'Comply');
            }
        }

        $fileName = 'Daily_Progress_Foto_Comply_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($cluster['cluster_name'] ?? ('Cluster_' . $clusterId))) . '.pdf';
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
        $cluster['nama_olt'] = $this->resolveClusterOltName($clusterId, $cluster);

        $dailyGroups = $this->buildDailyProgressPrintGroups($clusterId);
        $complyGroups = $this->MImplementasi_BOQ_MyRep->getApprovedComplyPrintGroups($clusterId);
        $allCategoryTitles = array_keys($complyGroups);
        $selectedCategory = trim((string) $this->input->get('category'));
        if ($selectedCategory !== '') {
            $complyGroups = $this->filterComplyGroupsByCategory($complyGroups, $selectedCategory);
        }
        if (empty($dailyGroups) && empty($complyGroups)) {
            $this->session->set_flashdata('error', 'Belum ada foto daily progress atau foto comply APPROVED yang bisa dipreview.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId . '#impl-comply-pane');
            return;
        }

        $data['title'] = 'Preview Daily Progress & Foto Comply';
        $data['cluster'] = $cluster;
        $data['dailyGroups'] = $dailyGroups;
        $data['complyGroups'] = $complyGroups;
        $data['allCategoryTitles'] = $allCategoryTitles;
        $data['selectedCategory'] = $selectedCategory;
        $data['pdfUrl'] = base_url('Implementasi_BOQ_MyRep/printComplyPdf/' . $clusterId) . ($selectedCategory !== '' ? ('?category=' . rawurlencode($selectedCategory)) : '');
        $data['tcpdfAvailable'] = class_exists('TCPDF') || $this->resolveTcpdfPath() !== '';

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Implementasi_BOQ_MyRep/preview_comply_pdf', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function printProgressReportPdf($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $report = $this->buildProgressReportData((int) $clusterId, (string) $this->input->get('scope'));
        if (empty($report['cluster'])) {
            $this->session->set_flashdata('error', 'Data implementasi cluster tidak ditemukan.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        if (!class_exists('TCPDF')) {
            $tcpdfPath = $this->resolveTcpdfPath();
            if ($tcpdfPath === '') {
                redirect('Implementasi_BOQ_MyRep/previewProgressReportPdf/' . (int) $clusterId . '?scope=' . rawurlencode((string) $report['scope']));
                return;
            }

            require_once $tcpdfPath;
        }

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('DatabaseTKM');
        $pdf->SetAuthor('DatabaseTKM');
        $pdf->SetTitle('Progress Report ' . (string) $report['scope'] . ' - ' . (string) ($report['cluster']['cluster_name'] ?? 'Cluster'));
        $pdf->SetSubject('Progress report invoice implementasi BOQ MyRep');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(true, 8);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->AddPage();

        $this->renderProgressReportPdf($pdf, $report);

        $fileName = $this->buildProgressReportFileName($report) . '.pdf';
        $pdf->Output($fileName, 'I');
    }

    public function previewProgressReportPdf($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $report = $this->buildProgressReportData((int) $clusterId, (string) $this->input->get('scope'));
        if (empty($report['cluster'])) {
            $this->session->set_flashdata('error', 'Data implementasi cluster tidak ditemukan.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $data['printFileName'] = $this->buildProgressReportFileName($report);
        $data['title'] = $data['printFileName'];
        $data['report'] = $report;
        $data['cluster'] = $report['cluster'];
        $data['pdfUrl'] = base_url('Implementasi_BOQ_MyRep/printProgressReportPdf/' . (int) $clusterId . '?scope=' . rawurlencode((string) $report['scope']));
        $data['tcpdfAvailable'] = class_exists('TCPDF') || $this->resolveTcpdfPath() !== '';

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Implementasi_BOQ_MyRep/preview_progress_report_pdf', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    private function buildDailyProgressPrintGroups($clusterId)
    {
        $dailyActivities = $this->MImplementasi_BOQ_MyRep->getDailyActivities((int) $clusterId);
        if (empty($dailyActivities)) {
            return [];
        }

        $groups = [];
        foreach ($dailyActivities as $activity) {
            $photos = (array) ($activity['photos'] ?? []);
            if (empty($photos)) {
                continue;
            }

            $activityDate = trim((string) ($activity['activity_date'] ?? ''));
            $scopeType = strtoupper(trim((string) ($activity['scope_type'] ?? 'CLUSTER'))) === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
            $sectionTitle = $scopeType;
            if (!isset($groups[$sectionTitle])) {
                $groups[$sectionTitle] = [];
            }

            $activityName = trim((string) ($activity['activity_name'] ?? $activity['activity_code'] ?? 'Aktivitas'));
            $activityDetail = trim((string) ($activity['activity_detail'] ?? ''));
            $qty = (float) ($activity['qty_activity'] ?? 0);
            $unit = trim((string) ($activity['unit_activity'] ?? ''));
            $remark = trim((string) ($activity['remark_activity'] ?? ''));

            foreach ($photos as $photo) {
                $caption = trim((string) ($photo['caption'] ?? ''));
                $descriptionParts = array_filter([$activityName, $activityDetail], static function ($value) {
                    return trim((string) $value) !== '' && trim((string) $value) !== '-';
                });
                $description = !empty($descriptionParts) ? implode(' - ', $descriptionParts) : (string) ($photo['file_name'] ?? 'Foto Daily Progress');

                $metaParts = [];
                if ($activityDate !== '') {
                    $metaParts[] = $this->formatPrintDate($activityDate);
                }
                if ($qty > 0) {
                    $metaParts[] = rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',') . ($unit !== '' ? ' ' . $unit : '');
                }
                if ($scopeType !== '') {
                    $metaParts[] = $scopeType;
                }
                if ($caption !== '') {
                    $metaParts[] = $caption;
                } elseif ($remark !== '') {
                    $metaParts[] = $remark;
                }

                $groups[$sectionTitle][] = [
                    'file_name' => (string) ($photo['file_name'] ?? 'Foto Daily Progress'),
                    'file_path' => (string) ($photo['file_path'] ?? ''),
                    'caption' => $caption,
                    'description' => $description,
                    'meta_line' => !empty($metaParts) ? implode(' | ', $metaParts) : 'Daily Progress',
                ];
            }
        }

        $orderedGroups = [];
        foreach (['CLUSTER', 'SUBFEEDER'] as $scopeTitle) {
            if (isset($groups[$scopeTitle])) {
                $orderedGroups[$scopeTitle] = $groups[$scopeTitle];
            }
        }

        return $orderedGroups;
    }

    private function buildProgressReportData($clusterId, $scope)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return [];
        }

        $scope = strtoupper(trim((string) $scope)) === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
        $cluster = $this->MImplementasi_BOQ_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            return [];
        }
        $cluster['nama_olt'] = $this->resolveClusterOltName($clusterId, $cluster);

        $compareRows = $this->MImplementasi_BOQ_MyRep->getBaselineCompareRows($clusterId);
        $historyMap = $this->MImplementasi_BOQ_MyRep->getProgressHistoryMap($clusterId);
        $dailyActivities = $this->MImplementasi_BOQ_MyRep->activityTablesReady()
            ? $this->MImplementasi_BOQ_MyRep->getDailyActivities($clusterId)
            : [];

        $itemTypes = [];
        $planByType = [];
        foreach ((array) $compareRows as $row) {
            $itemType = strtoupper(trim((string) ($row['item_type'] ?? 'LAINNYA')));
            if ($itemType === '') {
                $itemType = 'LAINNYA';
            }
            if (!isset($planByType[$itemType])) {
                $planByType[$itemType] = 0.0;
                $itemTypes[] = $itemType;
            }

            $planByType[$itemType] += (float) ($scope === 'SUBFEEDER' ? ($row['qty_subfeeder'] ?? 0) : ($row['qty_cluster'] ?? 0));
        }

        $dateRows = [];
        $dailyActivityGroups = [];
        foreach ((array) $dailyActivities as $activity) {
            $activityDate = trim((string) ($activity['activity_date'] ?? ''));
            if ($activityDate === '') {
                continue;
            }

            $activityScope = strtoupper(trim((string) ($activity['scope_type'] ?? 'CLUSTER'))) === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
            if ($activityScope !== $scope) {
                continue;
            }

            if (!isset($dateRows[$activityDate])) {
                $dateRows[$activityDate] = [
                    'progress_date' => $activityDate,
                    'remark' => [],
                    'achieve' => [],
                    'daily_progress_remarks' => [],
                ];
            }

            $activityCode = strtoupper(trim((string) ($activity['activity_code'] ?? '')));
            $dailyRemark = trim((string) ($activity['activity_detail'] ?? ''));
            if ($dailyRemark === '' || $dailyRemark === '-') {
                $dailyRemark = trim((string) ($activity['activity_name'] ?? ''));
            }
            if ($dailyRemark === '' || $dailyRemark === '-') {
                $dailyRemark = str_replace('_', ' ', $activityCode);
            }

            if ($dailyRemark !== '') {
                $dateRows[$activityDate]['daily_progress_remarks'][] = strtoupper($dailyRemark);
            }

            if (!isset($dailyActivityGroups[$activityDate])) {
                $dailyActivityGroups[$activityDate] = [
                    'date' => $activityDate,
                    'rows' => [],
                ];
            }
            $dailyActivityGroups[$activityDate]['rows'][] = [
                'id_daily_activity' => (int) ($activity['id_daily_activity'] ?? 0),
                'activity_name' => trim((string) ($activity['activity_name'] ?? '')) !== '' ? (string) $activity['activity_name'] : '-',
                'activity_detail' => trim((string) ($activity['activity_detail'] ?? '')) !== '' ? (string) $activity['activity_detail'] : '-',
                'boq_type' => trim((string) ($activity['boq_type'] ?? '')) !== '' ? (string) $activity['boq_type'] : '-',
                'qty_activity' => (float) ($activity['qty_activity'] ?? 0),
                'unit_activity' => (string) ($activity['unit_activity'] ?? ''),
                'team_count' => (int) ($activity['team_count'] ?? 0),
                'worker_count' => (int) ($activity['worker_count'] ?? 0),
                'photo_count' => count((array) ($activity['photos'] ?? [])),
                'pic' => trim((string) ($activity['nama_user'] ?? '')) !== '' ? (string) $activity['nama_user'] : '-',
                'remark_activity' => trim((string) ($activity['remark_activity'] ?? '')) !== '' ? (string) $activity['remark_activity'] : '-',
            ];
        }

        foreach ((array) $compareRows as $row) {
            $itemType = strtoupper(trim((string) ($row['item_type'] ?? 'LAINNYA')));
            if ($itemType === '') {
                $itemType = 'LAINNYA';
            }

            $baselineItemId = (int) ($row['id_boq_baseline_item'] ?? 0);
            foreach ((array) ($historyMap[$baselineItemId] ?? []) as $entry) {
                $entryScope = $this->detectProgressReportScope($entry['scope_type'] ?? '', $entry['remark_progress'] ?? '');
                if ($entryScope !== $scope) {
                    continue;
                }

                $progressDate = trim((string) ($entry['progress_date'] ?? ''));
                if ($progressDate === '') {
                    continue;
                }

                if (!isset($dateRows[$progressDate])) {
                    $dateRows[$progressDate] = [
                        'progress_date' => $progressDate,
                        'remark' => [],
                        'achieve' => [],
                        'daily_progress_remarks' => [],
                    ];
                }
                if (!isset($dateRows[$progressDate]['achieve'][$itemType])) {
                    $dateRows[$progressDate]['achieve'][$itemType] = 0.0;
                }
                $dateRows[$progressDate]['achieve'][$itemType] += (float) ($entry['qty_progress'] ?? 0);

                $remarkValue = trim((string) ($entry['remark_progress'] ?? ''));
                if ($remarkValue !== '') {
                    $dateRows[$progressDate]['remark'][] = $remarkValue;
                }
            }
        }

        ksort($dateRows);
        ksort($dailyActivityGroups);
        foreach ($dailyActivityGroups as &$dailyGroup) {
            usort($dailyGroup['rows'], static function ($a, $b) {
                return (int) ($a['id_daily_activity'] ?? 0) <=> (int) ($b['id_daily_activity'] ?? 0);
            });
        }
        unset($dailyGroup);

        $initialDate = !empty($cluster['drm_date'])
            ? (string) $cluster['drm_date']
            : (!empty($cluster['boq_approved_at']) ? substr((string) $cluster['boq_approved_at'], 0, 10) : '-');
        $historyRows = [];
        if (!empty($itemTypes)) {
            $historyRows[] = [
                'progress_date' => $initialDate,
                'remark' => 'BOQ Awal',
                'achieve' => array_fill_keys($itemTypes, 0),
            ];
        }

        $finalAchieve = array_fill_keys($itemTypes, 0.0);
        foreach ($dateRows as $dateRow) {
            $dateAchieveTotal = 0.0;
            foreach ($itemTypes as $itemType) {
                $dailyAchieve = (float) ($dateRow['achieve'][$itemType] ?? 0);
                $finalAchieve[$itemType] += $dailyAchieve;
                $dateAchieveTotal += abs($dailyAchieve);
            }

            $remarkPool = array_values(array_filter(array_unique((array) ($dateRow['remark'] ?? [])), static function ($value) {
                return trim((string) $value) !== '';
            }));
            $manualRemarkPool = array_values(array_filter($remarkPool, static function ($value) {
                $upper = strtoupper(trim((string) $value));
                return strpos($upper, '[AUTO]') !== 0
                    && strpos($upper, '[DAILY]') !== 0
                    && strpos($upper, 'UPLOAD FOTO COMPLY -') !== 0;
            }));
            $dailyRemarkPool = array_values(array_filter(array_unique((array) ($dateRow['daily_progress_remarks'] ?? [])), static function ($value) {
                return trim((string) $value) !== '';
            }));

            if ($dateAchieveTotal < 0.00001 && empty($dailyRemarkPool) && empty($manualRemarkPool)) {
                continue;
            }

            $finalRemark = !empty($dailyRemarkPool)
                ? implode(' | ', $dailyRemarkPool)
                : (!empty($manualRemarkPool)
                    ? implode(' | ', $manualRemarkPool)
                    : 'Progress Harian');
            $historyRows[] = [
                'progress_date' => (string) ($dateRow['progress_date'] ?? '-'),
                'remark' => $finalRemark,
                'achieve' => (array) ($dateRow['achieve'] ?? []),
            ];
        }

        $totalPlan = array_sum($planByType);
        $totalAchieve = array_sum($finalAchieve);

        return [
            'cluster' => $cluster,
            'scope' => $scope,
            'itemTypes' => $itemTypes,
            'planByType' => $planByType,
            'historyRows' => $historyRows,
            'finalAchieve' => $finalAchieve,
            'totalPlan' => $totalPlan,
            'totalAchieve' => $totalAchieve,
            'totalGap' => $totalPlan - $totalAchieve,
            'percent' => $totalPlan > 0 ? ($totalAchieve / $totalPlan) * 100 : 0,
            'hasPlan' => abs($totalPlan) > 0.00001,
            'dailyActivityGroups' => $dailyActivityGroups,
        ];
    }

    private function notifyDailyActivityCreated($clusterId, array $summary)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return false;
        }

        $cluster = $this->MImplementasi_BOQ_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            return false;
        }

        $this->load->library('Myrep_notification_service', null, 'myrepNotification');

        try {
            return (bool) $this->myrepNotification->notify('Implementasi_BOQ_MyRep', 'daily_progress_masuk', [
                'module_label' => 'Implementasi BOQ',
                'document_label' => 'Daily Progress',
                'regional_name' => (string) ($cluster['regional_name'] ?? ''),
                'province_name' => (string) ($cluster['province_name'] ?? ''),
                'city_name' => (string) ($cluster['city_name'] ?? ''),
                'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
                'homepass' => (int) ($cluster['homepass_drm'] ?? ($cluster['homepass'] ?? 0)),
                'sender_name' => (string) ($this->session->userdata('nama_user') ?: $this->session->userdata('nama_karyawan') ?: 'System'),
                'timestamp' => date('Y-m-d H:i:s'),
                'detail_url' => base_url('Implementasi_BOQ_MyRep/detail/' . $clusterId . '#impl-breakdown-pane'),
                'activity_date' => (string) ($summary['activity_date'] ?? ''),
                'scope_type' => (string) ($summary['scope_type'] ?? ''),
                'team_count' => (int) ($summary['team_count'] ?? 0),
                'worker_count' => (int) ($summary['worker_count'] ?? 0),
                'created_count' => (int) ($summary['created_count'] ?? 0),
                'allocated_qty_total' => (float) ($summary['allocated_qty_total'] ?? 0),
                'daily_activity_summary' => (array) ($summary['activities'] ?? []),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Daily progress Telegram notification failed: ' . $e->getMessage());
            return false;
        }
    }

    private function detectProgressReportScope($scopeValue, $remarkValue = '')
    {
        $scope = strtoupper(trim((string) $scopeValue));
        if ($scope === 'CLUSTER' || $scope === 'SUBFEEDER') {
            return $scope;
        }

        $remark = strtoupper(trim((string) $remarkValue));
        if (strpos($remark, 'SUBFEEDER') !== false) {
            return 'SUBFEEDER';
        }
        if (strpos($remark, 'CLUSTER') !== false) {
            return 'CLUSTER';
        }

        return 'CLUSTER';
    }

    private function buildProgressReportFileName(array $report)
    {
        $cluster = (array) ($report['cluster'] ?? []);
        $scope = ucfirst(strtolower((string) ($report['scope'] ?? 'CLUSTER')));
        $clusterName = trim((string) ($cluster['cluster_name'] ?? 'Cluster'));
        $teamName = trim((string) ($cluster['spv'] ?? ''));
        if ($teamName === '') {
            $teamName = trim((string) ($cluster['team_name'] ?? ''));
        }
        if ($teamName === '') {
            $teamName = '-';
        }

        $fileName = 'Progress ' . $scope . ' ' . $clusterName . ' - ' . $teamName . ' - PT. TKM';
        $fileName = preg_replace('/[\\\\\/:*?"<>|]+/', '-', $fileName);
        $fileName = preg_replace('/\s+/', ' ', $fileName);

        return trim((string) $fileName);
    }

    private function formatPrintDate($date)
    {
        $timestamp = strtotime((string) $date);
        if ($timestamp === false) {
            return (string) $date;
        }

        return date('d/m/Y', $timestamp);
    }

    private function resolveClusterOltName($clusterId, array $cluster = [])
    {
        $currentOlt = trim((string) ($cluster['nama_olt'] ?? ''));
        if ($currentOlt !== '') {
            return $currentOlt;
        }

        if (!$this->db->table_exists('tb_myrep_drm') || !$this->db->field_exists('nama_olt', 'tb_myrep_drm')) {
            return '-';
        }

        $row = $this->db
            ->select('nama_olt')
            ->from('tb_myrep_drm')
            ->where('id_myrep_cluster', (int) $clusterId)
            ->where("TRIM(COALESCE(nama_olt, '')) <> ''", null, false)
            ->order_by('id_drm', 'DESC')
            ->get()
            ->row_array();

        return !empty($row['nama_olt']) ? (string) $row['nama_olt'] : '-';
    }

    private function resolvePrintScopeTitle($fallbackScope, array $photos = [])
    {
        $fallbackScope = strtoupper(trim((string) $fallbackScope));
        if ($fallbackScope === 'SUBFEEDER') {
            return 'SUBFEEDER';
        }

        foreach ($photos as $photo) {
            $text = strtoupper(trim(implode(' ', [
                (string) ($photo['caption'] ?? ''),
                (string) ($photo['meta_line'] ?? ''),
                (string) ($photo['comply_label'] ?? ''),
                (string) ($photo['description'] ?? ''),
            ])));

            if (strpos($text, 'SUBFEEDER') !== false) {
                return 'SUBFEEDER';
            }
        }

        return 'CLUSTER';
    }

    private function filterComplyGroupsByCategory(array $groups, $selectedCategory)
    {
        $selectedCategory = trim((string) $selectedCategory);
        if ($selectedCategory === '' || empty($groups)) {
            return $groups;
        }

        foreach ($groups as $title => $rows) {
            if (strcasecmp((string) $title, $selectedCategory) === 0) {
                return [$title => $rows];
            }
        }

        return [];
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

    public function deleteDailyActivity()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $activityDate = $this->normalizeDate($this->input->post('activity_date'));

        if ($clusterId <= 0 || $activityDate === null) {
            $this->session->set_flashdata('error', 'Data daily progress tidak valid.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        if (!$this->MImplementasi_BOQ_MyRep->activityTablesReady()) {
            $this->session->set_flashdata('error', 'Tabel activity harian implementasi belum tersedia.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $deleted = $this->MImplementasi_BOQ_MyRep->deleteDailyActivitiesByDate($clusterId, $activityDate);
        $this->session->set_flashdata(
            $deleted ? 'success' : 'error',
            $deleted ? ('Daily progress tanggal ' . $activityDate . ' berhasil dihapus.') : 'Gagal menghapus daily progress.'
        );

        redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId . '#impl-breakdown-pane');
    }

    public function approveComplyPhoto()
    {
        if (empty($this->session->userdata('id_user'))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Session habis. Silakan login ulang.', ['redirect_url' => base_url('Auth')], 401);
                return;
            }
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Anda tidak memiliki akses approve foto comply.', [], 403);
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve foto comply.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $photoId = (int) $this->input->post('photo_id');
        $remark = trim((string) $this->input->post('review_remark'));

        $photo = $this->MImplementasi_BOQ_MyRep->getProgressPhotoById($photoId);
        if ($clusterId <= 0 || empty($photo) || (int) ($photo['id_myrep_cluster'] ?? 0) !== $clusterId) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Foto comply tidak ditemukan.', [], 404);
                return;
            }
            $this->session->set_flashdata('error', 'Foto comply tidak ditemukan.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        if (strtoupper(trim((string) ($photo['photo_category'] ?? 'HARIAN'))) !== 'COMPLY') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Hanya foto comply yang bisa di-review HO.', [], 422);
                return;
            }
            $this->session->set_flashdata('error', 'Hanya foto comply yang bisa di-review HO.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $result = $this->MImplementasi_BOQ_MyRep->updateProgressPhotoReviewStatus($photoId, 'APPROVED', (int) $this->session->userdata('id_user'), $remark);
        if ($this->isAjaxRequest()) {
            $this->jsonResponse((bool) $result, $result ? 'Foto comply berhasil di-approve.' : 'Gagal approve foto comply.', [
                'photo' => $this->buildComplyPhotoReviewPayload($photoId, 'APPROVED', $remark),
            ], $result ? 200 : 500);
            return;
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Foto comply berhasil di-approve.' : 'Gagal approve foto comply.');
        redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId . '#impl-comply-pane');
    }

    public function rejectComplyPhoto()
    {
        if (empty($this->session->userdata('id_user'))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Session habis. Silakan login ulang.', ['redirect_url' => base_url('Auth')], 401);
                return;
            }
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Anda tidak memiliki akses reject foto comply.', [], 403);
                return;
            }
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject foto comply.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $photoId = (int) $this->input->post('photo_id');
        $remark = trim((string) $this->input->post('review_remark'));

        $photo = $this->MImplementasi_BOQ_MyRep->getProgressPhotoById($photoId);
        if ($clusterId <= 0 || empty($photo) || (int) ($photo['id_myrep_cluster'] ?? 0) !== $clusterId) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Foto comply tidak ditemukan.', [], 404);
                return;
            }
            $this->session->set_flashdata('error', 'Foto comply tidak ditemukan.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        if (strtoupper(trim((string) ($photo['photo_category'] ?? 'HARIAN'))) !== 'COMPLY') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Hanya foto comply yang bisa di-review HO.', [], 422);
                return;
            }
            $this->session->set_flashdata('error', 'Hanya foto comply yang bisa di-review HO.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        if ($remark === '') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Alasan reject foto comply wajib diisi.', [], 422);
                return;
            }
            $this->session->set_flashdata('error', 'Alasan reject foto comply wajib diisi.');
            redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId);
            return;
        }

        $result = $this->MImplementasi_BOQ_MyRep->updateProgressPhotoReviewStatus($photoId, 'REJECTED', (int) $this->session->userdata('id_user'), $remark);
        if ($this->isAjaxRequest()) {
            $this->jsonResponse((bool) $result, $result ? 'Foto comply berhasil di-reject.' : 'Gagal reject foto comply.', [
                'photo' => $this->buildComplyPhotoReviewPayload($photoId, 'REJECTED', $remark),
            ], $result ? 200 : 500);
            return;
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Foto comply berhasil di-reject.' : 'Gagal reject foto comply.');
        redirect('Implementasi_BOQ_MyRep/detail/' . $clusterId . '#impl-comply-pane');
    }

    public function rotateProgressPhoto()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session habis. Silakan login ulang.', ['redirect_url' => base_url('Auth')], 401);
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $photoId = (int) $this->input->post('photo_id');
        $rotation = (int) $this->input->post('rotation');
        $rotation = (($rotation % 360) + 360) % 360;

        if ($clusterId <= 0 || $photoId <= 0 || !in_array($rotation, [90, 180, 270], true)) {
            $this->jsonResponse(false, 'Data rotasi foto tidak valid.', [], 422);
            return;
        }

        $photo = $this->MImplementasi_BOQ_MyRep->getProgressPhotoById($photoId);
        if (empty($photo) || (int) ($photo['id_myrep_cluster'] ?? 0) !== $clusterId) {
            $this->jsonResponse(false, 'Foto tidak ditemukan.', [], 404);
            return;
        }

        $fullPath = $this->resolveProgressPhotoFullPath((string) ($photo['file_path'] ?? ''));
        if ($fullPath === '' || !is_file($fullPath)) {
            $this->jsonResponse(false, 'File foto tidak ditemukan di server.', [], 404);
            return;
        }

        $result = !empty($_FILES['rotated_photo']['tmp_name'])
            ? $this->replaceProgressPhotoFromUpload($fullPath, $_FILES['rotated_photo'])
            : $this->rotateImageFile($fullPath, $rotation);
        if (!$result) {
            $this->jsonResponse(false, 'Gagal menyimpan rotasi foto. Format gambar belum didukung server.', [], 500);
            return;
        }

        clearstatcache(true, $fullPath);
        $version = (string) (@filemtime($fullPath) ?: time());
        $imageUrl = base_url() . ltrim((string) ($photo['file_path'] ?? ''), '/') . '?v=' . rawurlencode($version);

        $this->jsonResponse(true, 'Rotasi foto berhasil disimpan.', [
            'photo_id' => $photoId,
            'image_url' => $imageUrl,
        ]);
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
        $progressDate = date('Y-m-d');
        $redirectUrl = 'Implementasi_BOQ_MyRep/detail/' . $clusterId . '#impl-comply-pane';

        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Cluster comply tidak valid.');
            redirect('Implementasi_BOQ_MyRep');
            return;
        }

        $compareRows = $this->MImplementasi_BOQ_MyRep->getBaselineCompareRows($clusterId);
        $itemContextMap = [];
        foreach ($compareRows as $compareRow) {
            $itemContextMap[(int) ($compareRow['id_boq_baseline_item'] ?? 0)] = $compareRow;
        }

        $entries = (array) $this->input->post('comply_entries');
        if (empty($entries)) {
            // Backward compatibility: single form payload.
            $entries[] = [
                'baseline_item_id' => (int) $this->input->post('baseline_item_id'),
                'comply_label' => trim((string) $this->input->post('comply_label')),
                'comply_photo_remarks' => trim((string) $this->input->post('comply_photo_remarks')),
                '__legacy_file_input' => 'comply_photos_single',
            ];
        }

        $createdCount = 0;
        foreach ($entries as $entryIndex => $entry) {
            $baselineItemId = (int) ($entry['baseline_item_id'] ?? 0);
            $complyLabel = trim((string) ($entry['comply_label'] ?? ''));
            if ($baselineItemId <= 0 || $complyLabel === '') {
                continue;
            }

            $itemContext = $itemContextMap[$baselineItemId] ?? [];
            if (empty($itemContext) || (int) ($itemContext['comply_enabled'] ?? 0) !== 1) {
                $this->session->set_flashdata('error', 'Item comply tidak valid / tidak mendukung comply.');
                redirect($redirectUrl);
                return;
            }

            $photoRemarksRaw = trim((string) ($entry['comply_photo_remarks'] ?? ''));
            $photoRemarks = [];
            if ($photoRemarksRaw !== '') {
                $photoRemarks = preg_split('/\r\n|\r|\n/', $photoRemarksRaw);
                $photoRemarks = array_values(array_map('trim', (array) $photoRemarks));
                $photoRemarks = array_values(array_filter($photoRemarks, static function ($line) {
                    return $line !== '';
                }));
            }

            $inputName = !empty($entry['__legacy_file_input'])
                ? (string) $entry['__legacy_file_input']
                : ('comply_entry_photos_' . $entryIndex);
            $photoRows = $this->uploadStandaloneComplyPhotos($clusterId, $baselineItemId, $itemContext, $complyLabel, $photoRemarks, $inputName);
            if ($photoRows === false) {
                redirect($redirectUrl);
                return;
            }

            if (empty($photoRows)) {
                continue;
            }

            $created = $this->MImplementasi_BOQ_MyRep->createProgressEntry($clusterId, $baselineItemId, [
                'progress_date' => $progressDate,
                'qty_progress' => 0,
                'status_progress' => 'ON PROGRESS',
                'remark_progress' => 'Upload Foto Comply - ' . $complyLabel,
                'created_by' => (int) $this->session->userdata('id_user'),
                'updated_by' => (int) $this->session->userdata('id_user'),
            ], $photoRows);
            if ($created > 0) {
                $createdCount++;
            }
        }

        $this->session->set_flashdata(
            $createdCount > 0 ? 'success' : 'error',
            $createdCount > 0
                ? ('Foto comply berhasil diupload (' . $createdCount . ' entry) dan menunggu approval HO.')
                : 'Gagal menyimpan foto comply.'
        );
        redirect($redirectUrl);
    }

    private function uploadProgressPhotos($clusterId, $baselineItemId, $itemKey, $itemContext, $qtyProgress)
    {
        $clusterId = (int) $clusterId;
        $baselineItemId = (int) $baselineItemId;
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
            $this->optimizeUploadedPhoto((string) ($fileData['full_path'] ?? ''));
            $uploadedRows[] = [
                'file_name' => $fileData['file_name'],
                'file_path' => 'uploads/myrep_boq_progress/' . $fileData['file_name'],
                'caption' => '',
                'photo_category' => 'HARIAN',
                'comply_label' => null,
            ];
        }

        return $uploadedRows;
    }

    private function uploadDailyActivityPhotos($clusterId, $activityCode, $inputName = 'activity_photos')
    {
        $clusterId = (int) $clusterId;
        $files = $_FILES[$inputName] ?? null;
        if (empty($files['name']) || !is_array($files['name'])) {
            return [];
        }

        $uploadDir = './uploads/myrep_boq_activity/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedRows = [];
        $totalFiles = count($files['name']);
        for ($i = 0; $i < $totalFiles; $i++) {
            if (empty($files['name'][$i])) {
                continue;
            }

            $_FILES['single_activity_photo'] = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];

            $extension = pathinfo((string) $files['name'][$i], PATHINFO_EXTENSION);
            $fileName = 'ACTIVITY_' . $clusterId . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $activityCode) . '_' . date('YmdHis') . '_' . ($i + 1) . '.' . $extension;
            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size' => 30720,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('single_activity_photo')) {
                $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
                return false;
            }

            $fileData = $this->upload->data();
            $this->optimizeUploadedPhoto((string) ($fileData['full_path'] ?? ''));
            $uploadedRows[] = [
                'file_name' => (string) $fileData['file_name'],
                'file_path' => 'uploads/myrep_boq_activity/' . $fileData['file_name'],
                'caption' => '',
            ];
        }

        return $uploadedRows;
    }

    private function uploadStandaloneComplyPhotos($clusterId, $baselineItemId, $itemContext, $label, array $photoRemarks = [], $inputName = 'comply_photos_single')
    {
        $clusterId = (int) $clusterId;
        $baselineItemId = (int) $baselineItemId;
        $itemName = trim((string) ($itemContext['item_name'] ?? 'Item'));
        $requiredPhotoCount = max((int) ($itemContext['comply_photo_per_label'] ?? 0), 1);
        $files = $_FILES[$inputName] ?? null;

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

        if (count($photoRemarks) !== count($validFileNames)) {
            $this->session->set_flashdata('error', 'Setiap foto comply wajib punya remark masing-masing. Jumlah remark harus sama dengan jumlah foto yang diupload.');
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
            $this->optimizeUploadedPhoto((string) ($fileData['full_path'] ?? ''));
            $uploadedRows[] = [
                'file_name' => $fileData['file_name'],
                'file_path' => 'uploads/myrep_boq_progress/' . $fileData['file_name'],
                'caption' => (string) ($photoRemarks[$uploadIndex] ?? ('Comply - ' . $label)),
                'photo_category' => 'COMPLY',
                'comply_label' => $label,
                'status_photo' => 'UPLOADED',
            ];
            $uploadIndex++;
        }

        return $uploadedRows;
    }

    private function optimizeUploadedPhoto($fullPath)
    {
        $fullPath = trim((string) $fullPath);
        if ($fullPath === '' || !is_file($fullPath)) {
            return;
        }

        $info = @getimagesize($fullPath);
        if ($info === false || empty($info[0]) || empty($info[1])) {
            return;
        }

        $extension = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return;
        }

        $maxSide = 1600;
        $width = (int) $info[0];
        $height = (int) $info[1];
        if ($width <= $maxSide && $height <= $maxSide) {
            return;
        }

        $this->load->library('image_lib');
        $this->image_lib->clear();
        $this->image_lib->initialize([
            'image_library' => 'gd2',
            'source_image' => $fullPath,
            'maintain_ratio' => true,
            'width' => $maxSide,
            'height' => $maxSide,
            'quality' => '82%',
        ]);
        @$this->image_lib->resize();
        $this->image_lib->clear();
    }

    private function resolveProgressPhotoFullPath($filePath)
    {
        $relativePath = trim(str_replace('\\', '/', (string) $filePath));
        $relativePath = ltrim($relativePath, '/');
        if ($relativePath === '' || strpos($relativePath, '..') !== false) {
            return '';
        }

        $allowedPrefixes = [
            'uploads/myrep_boq_progress/',
        ];
        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (strpos($relativePath, $prefix) === 0) {
                $isAllowed = true;
                break;
            }
        }
        if (!$isAllowed) {
            return '';
        }

        $fullPath = FCPATH . $relativePath;
        $realBase = realpath(FCPATH . 'uploads/myrep_boq_progress');
        $realFileDir = realpath(dirname($fullPath));
        if ($realBase === false || $realFileDir === false || strpos($realFileDir, $realBase) !== 0) {
            return '';
        }

        return $fullPath;
    }

    private function rotateImageFile($fullPath, $clockwiseDegrees)
    {
        $extension = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
        $createFunction = null;
        $saveFunction = null;

        if (in_array($extension, ['jpg', 'jpeg'], true)) {
            $createFunction = 'imagecreatefromjpeg';
            $saveFunction = static function ($image, $path) {
                return imagejpeg($image, $path, 88);
            };
        } elseif ($extension === 'png') {
            $createFunction = 'imagecreatefrompng';
            $saveFunction = static function ($image, $path) {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                return imagepng($image, $path, 6);
            };
        } elseif ($extension === 'webp' && function_exists('imagecreatefromwebp') && function_exists('imagewebp')) {
            $createFunction = 'imagecreatefromwebp';
            $saveFunction = static function ($image, $path) {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                return imagewebp($image, $path, 88);
            };
        }

        if ($createFunction === null || !function_exists($createFunction) || !function_exists('imagerotate')) {
            return false;
        }

        $source = @$createFunction($fullPath);
        if (!$source) {
            return false;
        }

        $transparent = imagecolorallocatealpha($source, 0, 0, 0, 127);
        $gdAngle = (360 - (int) $clockwiseDegrees) % 360;
        $rotated = imagerotate($source, $gdAngle, $transparent);
        imagedestroy($source);

        if (!$rotated) {
            return false;
        }

        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);
        $saved = (bool) $saveFunction($rotated, $fullPath);
        imagedestroy($rotated);

        return $saved;
    }

    private function replaceProgressPhotoFromUpload($fullPath, array $uploadedFile)
    {
        if (empty($uploadedFile['tmp_name']) || !is_uploaded_file($uploadedFile['tmp_name'])) {
            return false;
        }

        if ((int) ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        $info = @getimagesize($uploadedFile['tmp_name']);
        if ($info === false || empty($info['mime'])) {
            return false;
        }

        $extension = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
        $allowedMimeByExtension = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
        ];

        if (empty($allowedMimeByExtension[$extension]) || !in_array((string) $info['mime'], $allowedMimeByExtension[$extension], true)) {
            return false;
        }

        return move_uploaded_file($uploadedFile['tmp_name'], $fullPath);
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

    private function isAjaxRequest()
    {
        return $this->input->is_ajax_request()
            || strtolower((string) $this->input->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    private function jsonResponse($status, $message, array $payload = [], $httpStatus = 200)
    {
        $this->output
            ->set_status_header((int) $httpStatus)
            ->set_content_type('application/json')
            ->set_output(json_encode(array_merge([
                'status' => (bool) $status,
                'message' => (string) $message,
            ], $payload)));
    }

    private function buildComplyPhotoReviewPayload($photoId, $statusPhoto, $reviewRemark)
    {
        $statusPhoto = strtoupper(trim((string) $statusPhoto));
        return [
            'id_progress_photo' => (int) $photoId,
            'status_photo' => $statusPhoto,
            'badge_class' => $statusPhoto === 'APPROVED' ? 'success' : ($statusPhoto === 'REJECTED' ? 'danger' : 'warning'),
            'review_remark' => trim((string) $reviewRemark),
        ];
    }

    private function renderComplyPdfHeader($pdf, $cluster, $sectionTitle, $documentTitle = 'FOTO COMPLY', $infoTitle = 'Foto Comply Approved')
    {
        $tkmLogo = $this->resolvePdfLogoFile([
            'assets/dist/img/solid logo tkm landscape transparent.png',
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
        $pdf->MultiCell(20, 4, str_replace(' ', "\n", strtoupper((string) $documentTitle)), 0, 'C', false, 1);

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
        $pdf->Cell(60, 3, (string) $infoTitle, 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY($left + 26, $top + $headerHeight + 4.5);
        $pdf->Cell(120, 3, 'Cluster: ' . $clusterName, 0, 0, 'L');
    }

    private function renderComplyPdfPhotos($pdf, $photos, $defaultPrefix = 'Comply')
    {
        $startY = 42;
        $leftX = 8;
        $rightX = 106;
        $tileWidth = 90;
        $tileHeight = 56;
        $imageHeight = 45;
        $rowGap = 4;

        foreach (array_values($photos) as $index => $photo) {
            $column = $index % 2;
            $row = (int) floor($index / 2);
            $x = $column === 0 ? $leftX : $rightX;
            $y = $startY + ($row * ($tileHeight + $rowGap));

            $description = trim((string) ($photo['description'] ?? '')) !== ''
                ? (string) $photo['description']
                : (trim((string) ($photo['comply_label'] ?? '')) !== '' ? (string) $photo['comply_label'] : (string) ($photo['file_name'] ?? 'Foto'));
            $caption = trim((string) ($photo['caption'] ?? ''));
            $metaLine = trim((string) ($photo['meta_line'] ?? '')) !== ''
                ? (string) $photo['meta_line']
                : ($caption !== '' ? $caption : ((string) $defaultPrefix . ' - ' . $description));
            $metaLine = $this->appendPrintPhotoItemToRemark($metaLine, $photo);
            $imagePath = $this->resolvePdfImageFile((string) ($photo['file_path'] ?? ''));

            $pdf->Rect($x, $y, $tileWidth, $tileHeight);
            $pdf->Rect($x, $y, $tileWidth, $imageHeight);
            $pdf->Rect($x, $y + $imageHeight, $tileWidth, 5.5);
            $pdf->Rect($x, $y + $imageHeight + 5.5, $tileWidth, 5.5);

            if ($imagePath !== '') {
                try {
                    $pdf->Image($imagePath, $x + 1.5, $y + 1.5, $tileWidth - 3, $imageHeight - 3, '', '', '', false, 150, '', false, false, 1, false, false, false);
                } catch (\Throwable $e) {
                    $pdf->SetFont('helvetica', '', 7.5);
                    $pdf->SetXY($x + 4, $y + 20);
                    $pdf->Cell($tileWidth - 8, 5, 'Gagal memuat gambar', 0, 0, 'C');
                }
            } else {
                $pdf->SetFont('helvetica', '', 7.5);
                $pdf->SetXY($x + 4, $y + 20);
                $pdf->Cell($tileWidth - 8, 5, 'File foto tidak ditemukan', 0, 0, 'C');
            }

            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetXY($x + 1, $y + $imageHeight + 0.8);
            $pdf->Cell($tileWidth - 2, 4, 'Description: ' . $description, 0, 0, 'C');
            $pdf->SetFont('helvetica', '', 6.6);
            $pdf->SetXY($x + 1, $y + $imageHeight + 6.1);
            $pdf->Cell($tileWidth - 2, 4, $metaLine, 0, 0, 'C');
        }
    }

    private function renderProgressReportPdf($pdf, array $report)
    {
        $cluster = (array) ($report['cluster'] ?? []);
        $scope = (string) ($report['scope'] ?? 'CLUSTER');
        $itemTypes = (array) ($report['itemTypes'] ?? []);
        $planByType = (array) ($report['planByType'] ?? []);
        $historyRows = (array) ($report['historyRows'] ?? []);
        $finalAchieve = (array) ($report['finalAchieve'] ?? []);
        $clusterName = trim((string) ($cluster['cluster_name'] ?? '-'));
        $spv = trim((string) ($cluster['spv'] ?? '-'));
        $percent = (float) ($report['percent'] ?? 0);
        $tkmLogo = $this->resolvePdfLogoFile([
            'assets/dist/img/solid logo tkm landscape transparent.png',
            'assets/dist/img/logotkmsolid.png',
        ]);
        $webLogo = $this->resolvePdfLogoFile([
            'assets/dist/img/logoweb.png',
        ]);

        $html = '<style>
            table { border-collapse: collapse; }
            .letterhead td { border-bottom:2px solid #111827; padding:4px 6px 8px; vertical-align:middle; }
            .brand { font-size:18px; font-weight:bold; text-align:center; color:#0f172a; }
            .meta td { border:1px solid #111827; padding:4px 6px; font-size:8px; }
            .title { font-size:14px; font-weight:bold; text-align:center; }
            .subtitle { font-size:8px; color:#374151; text-align:center; }
            .summary td { border:1px solid #111827; padding:4px 6px; font-size:8px; }
            .tracker th { border:1px solid #111827; background-color:#1f2937; color:#ffffff; font-weight:bold; text-align:center; font-size:6.2px; padding:3px; }
            .tracker td { border:1px solid #111827; font-size:6.2px; padding:3px; text-align:center; }
            .tracker .left { text-align:left; }
            .tracker .total { background-color:#f3f4f6; font-weight:bold; }
            .section-title { font-size:10px; font-weight:bold; margin-top:8px; }
            .daily th { border:1px solid #111827; background-color:#1f2937; color:#ffffff; font-weight:bold; text-align:center; font-size:6.4px; padding:3px; }
            .daily td { border:1px solid #111827; font-size:6.4px; padding:3px; text-align:center; }
            .daily .left { text-align:left; }
        </style>';

        $leftLogo = $tkmLogo !== ''
            ? '<img src="' . html_escape($tkmLogo) . '" height="28">'
            : '';
        $rightLogo = $webLogo !== ''
            ? '<img src="' . html_escape($webLogo) . '" height="28">'
            : '';

        $html .= '<table class="letterhead" width="100%">
            <tr>
                <td width="25%">' . $leftLogo . '</td>
                <td width="50%" class="brand">DatabaseTKM</td>
                <td width="25%" align="right">' . $rightLogo . '</td>
            </tr>
        </table><br>';

        $html .= '<table class="meta" width="100%">
            <tr>
                <td width="22%"><strong>Progress Report</strong></td>
                <td width="28%">' . html_escape($scope) . '</td>
                <td width="18%"><strong>Tanggal Print</strong></td>
                <td width="32%">' . html_escape(date('d/m/Y H:i')) . '</td>
            </tr>
            <tr>
                <td><strong>Cluster</strong></td>
                <td>' . html_escape($clusterName) . '</td>
                <td><strong>Team</strong></td>
                <td>' . html_escape($spv !== '' ? $spv : '-') . '</td>
            </tr>
            <tr>
                <td><strong>Regional / Kota</strong></td>
                <td>' . html_escape((string) ($cluster['regional_name'] ?? '-') . ' / ' . (string) ($cluster['city_name'] ?? '-')) . '</td>
                <td><strong>OLT</strong></td>
                <td>' . html_escape((string) ($cluster['nama_olt'] ?? '-')) . '</td>
            </tr>
        </table>';

        $html .= '<br><div class="title">HISTORY PROGRESS ' . html_escape($scope) . '</div>';
        $html .= '<div class="subtitle">Dokumen bukti progress untuk penagihan invoice</div><br>';

        $html .= '<table class="summary" width="100%">
            <tr>
                <td width="25%"><strong>Total Plan</strong><br>' . $this->formatProgressReportNumber((float) ($report['totalPlan'] ?? 0)) . '</td>
                <td width="25%"><strong>Total Achievement</strong><br>' . $this->formatProgressReportNumber((float) ($report['totalAchieve'] ?? 0)) . '</td>
                <td width="25%"><strong>Selisih</strong><br>' . $this->formatProgressReportNumber((float) ($report['totalGap'] ?? 0)) . '</td>
                <td width="25%"><strong>Persentase</strong><br>' . $this->formatProgressReportPercent($percent) . '</td>
            </tr>
        </table><br>';

        if (empty($report['hasPlan'])) {
            $html .= '<table class="summary" width="100%"><tr><td>BOQ Tracker ' . html_escape($scope) . ' belum memiliki plan.</td></tr></table>';
        } else {
            $html .= '<table class="tracker" width="100%"><thead><tr>';
            $html .= '<th rowspan="2" width="4%">No</th><th rowspan="2" width="6%">HP DRM</th>';
            $dynamicWidth = count($itemTypes) > 0 ? (62 / max(count($itemTypes), 1)) : 10;
            foreach ($itemTypes as $itemType) {
                $html .= '<th colspan="2" width="' . (float) $dynamicWidth . '%">' . html_escape($itemType) . '</th>';
            }
            $html .= '<th rowspan="2" width="10%">Tanggal</th><th rowspan="2" width="18%">Keterangan</th></tr><tr>';
            foreach ($itemTypes as $itemType) {
                $html .= '<th>PLAN</th><th>ACHIEV</th>';
            }
            $html .= '</tr></thead><tbody>';

            foreach ($historyRows as $index => $row) {
                $html .= '<tr>';
                $html .= '<td>' . ((int) $index + 1) . '</td>';
                $html .= '<td>' . ($index === 0 ? $this->formatProgressReportNumber((float) ($cluster['homepass_drm'] ?? 0)) : '-') . '</td>';
                foreach ($itemTypes as $itemType) {
                    $html .= '<td>' . ($index === 0 ? $this->formatProgressReportNumber((float) ($planByType[$itemType] ?? 0)) : '-') . '</td>';
                    $html .= '<td>' . $this->formatProgressReportNumber((float) ($row['achieve'][$itemType] ?? 0), true) . '</td>';
                }
                $html .= '<td>' . html_escape((string) ($row['progress_date'] ?? '-')) . '</td>';
                $html .= '<td class="left">' . html_escape((string) ($row['remark'] ?? '-')) . '</td>';
                $html .= '</tr>';
            }

            $html .= '<tr class="total"><td colspan="2">Total</td>';
            foreach ($itemTypes as $itemType) {
                $html .= '<td>' . $this->formatProgressReportNumber((float) ($planByType[$itemType] ?? 0)) . '</td>';
                $html .= '<td>' . $this->formatProgressReportNumber((float) ($finalAchieve[$itemType] ?? 0), true) . '</td>';
            }
            $html .= '<td colspan="2"></td></tr>';

            $html .= '<tr class="total"><td colspan="2">Selisih</td>';
            foreach ($itemTypes as $itemType) {
                $html .= '<td colspan="2">' . $this->formatProgressReportNumber((float) (($planByType[$itemType] ?? 0) - ($finalAchieve[$itemType] ?? 0)), true) . '</td>';
            }
            $html .= '<td colspan="2"></td></tr>';
            $html .= '</tbody></table>';
        }

        $html .= $this->buildProgressReportDailyActivityHtml((array) ($report['dailyActivityGroups'] ?? []));

        $pdf->writeHTML($html, true, false, true, false, '');
    }

    private function buildProgressReportDailyActivityHtml(array $dailyActivityGroups)
    {
        $html = '<br><div class="section-title">Breakdown Daily Progress Aktivitas</div>';
        if (empty($dailyActivityGroups)) {
            return $html . '<table class="summary" width="100%"><tr><td>Belum ada daily progress aktivitas untuk scope ini.</td></tr></table>';
        }

        $html .= '<table class="daily" width="100%"><thead><tr>
            <th width="4%">No</th>
            <th width="10%">Tanggal</th>
            <th width="9%">Team/Orang</th>
            <th width="14%">Aktivitas</th>
            <th width="13%">Detail</th>
            <th width="6%">Qty</th>
            <th width="7%">Jenis</th>
            <th width="9%">Tipe</th>
            <th width="7%">Foto</th>
            <th width="11%">PIC</th>
            <th width="10%">Remark</th>
        </tr></thead><tbody>';

        $rowNo = 1;
        foreach ($dailyActivityGroups as $group) {
            $rows = array_values((array) ($group['rows'] ?? []));
            $rowspan = max(count($rows), 1);
            foreach ($rows as $index => $row) {
                $html .= '<tr>';
                $html .= '<td>' . $rowNo . '</td>';
                if ($index === 0) {
                    $html .= '<td rowspan="' . (int) $rowspan . '">' . html_escape((string) ($group['date'] ?? '-')) . '</td>';
                }
                $html .= '<td>' . (int) ($row['team_count'] ?? 0) . ' / ' . (int) ($row['worker_count'] ?? 0) . '</td>';
                $html .= '<td class="left">' . html_escape((string) ($row['activity_name'] ?? '-')) . '</td>';
                $html .= '<td class="left">' . html_escape((string) ($row['activity_detail'] ?? '-')) . '</td>';
                $html .= '<td>' . $this->formatProgressReportQty((float) ($row['qty_activity'] ?? 0)) . '</td>';
                $html .= '<td>' . html_escape((string) ($row['unit_activity'] ?? '')) . '</td>';
                $html .= '<td>' . html_escape((string) ($row['boq_type'] ?? '-')) . '</td>';
                $html .= '<td>' . (int) ($row['photo_count'] ?? 0) . '</td>';
                $html .= '<td class="left">' . html_escape((string) ($row['pic'] ?? '-')) . '</td>';
                $html .= '<td class="left">' . html_escape((string) ($row['remark_activity'] ?? '-')) . '</td>';
                $html .= '</tr>';
                $rowNo++;
            }
        }

        return $html . '</tbody></table>';
    }

    private function formatProgressReportNumber($value, $zeroAsDash = false)
    {
        $number = (float) $value;
        if ($zeroAsDash && abs($number) < 0.00001) {
            return '-';
        }

        return number_format($number, 0, ',', '.');
    }

    private function formatProgressReportPercent($value)
    {
        $number = (float) $value;
        if (abs($number) < 0.00001) {
            return '0%';
        }

        return rtrim(rtrim(number_format($number, 1, ',', '.'), '0'), ',') . '%';
    }

    private function formatProgressReportQty($value)
    {
        $number = (float) $value;
        return rtrim(rtrim(number_format($number, 2, ',', '.'), '0'), ',');
    }

    private function appendPrintPhotoItemToRemark($remark, array $photo)
    {
        $remark = trim((string) $remark);
        $itemName = trim((string) ($photo['item_name'] ?? ''));
        if ($remark === '' || $itemName === '') {
            return $remark;
        }

        if (stripos($remark, $itemName) !== false) {
            return $remark;
        }

        return $remark . ' - ' . $itemName;
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
            'C:/xampp/phpMyAdmin/vendor/tecnickcom/tcpdf/tcpdf.php',
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
            'C:/xampp/phpMyAdmin/vendor/tecnickcom/tcpdf',
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
