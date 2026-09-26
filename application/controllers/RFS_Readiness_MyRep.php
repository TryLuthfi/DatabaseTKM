<?php
defined('BASEPATH') or exit('No direct script access allowed');

class RFS_Readiness_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MRFS_Readiness_MyRep');
        $this->load->model('MBatch_Approval_MyRep');
        $this->load->library('upload');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('RFS_Readiness_MyRep');
            $this->myrepAccess->enforceByMethod('RFS_Readiness_MyRep', (string) $this->router->fetch_method(), [
                'createPeriod' => 'TAMBAH',
                'generateCandidates' => 'TAMBAH',
                'saveBaseline' => 'EDIT',
                'saveItemBaseline' => 'EDIT',
                'lockPeriod' => 'EDIT',
                'unlockPeriod' => 'EDIT',
                'submitChangeRequest' => 'EDIT',
                'reviewChangeRequest' => 'APPROVAL',
                'syncActualRfs' => 'EDIT',
                'closePeriod' => 'APPROVAL',
                'exportWeeklySummary' => 'VIEW',
            ]);
        }
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        $isReady = $this->MRFS_Readiness_MyRep->tablesReady();
        $selectedPeriodId = (int) $this->input->get('period_id');
        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedRegional = strtoupper(trim((string) $this->input->get('regional')));
        $selectedScope = strtolower(trim((string) $this->input->get('scope')));
        if (!in_array($selectedScope, ['city', 'regional'], true)) {
            $selectedScope = '';
        }
        if ($selectedScope !== 'city') {
            $selectedCity = '';
        }
        if ($selectedScope !== 'regional') {
            $selectedRegional = '';
        }
        $isSummaryMode = $selectedScope === '';
        $selectedPriority = strtoupper(trim((string) $this->input->get('priority')));
        $selectedFinalStatus = strtoupper(trim((string) $this->input->get('final_status')));

        $periodOptions = $isReady ? $this->MRFS_Readiness_MyRep->getPeriodOptions() : [];
        if ($isReady && $selectedPeriodId <= 0) {
            if (!empty($periodOptions)) {
                $selectedPeriodId = (int) $periodOptions[0]['id_period'];
            } elseif ($this->isRfsHo()) {
                $period = $this->MRFS_Readiness_MyRep->getOrCreateCurrentPeriod($userId);
                $selectedPeriodId = (int) ($period['id_period'] ?? 0);
                $periodOptions = $this->MRFS_Readiness_MyRep->getPeriodOptions();
            }
        }

        $period = $selectedPeriodId > 0 ? $this->MRFS_Readiness_MyRep->getPeriodById($selectedPeriodId) : [];
        if (!empty($period)) {
            $this->MRFS_Readiness_MyRep->syncActualRfsForPeriod($selectedPeriodId, $userId);
        }

        $items = $selectedPeriodId > 0
            ? $this->MRFS_Readiness_MyRep->getItems($selectedPeriodId, $selectedCity, $selectedPriority, $selectedFinalStatus, $selectedRegional)
            : [];
        $candidatePage = ($selectedPeriodId > 0 && !empty($period) && $this->isRfsHo())
            ? $this->MRFS_Readiness_MyRep->getCandidateClustersPage($selectedPeriodId, $selectedCity, 0, 1, '', [], $selectedRegional)
            : ['recordsFiltered' => 0];
        $cityOptions = $selectedPeriodId > 0 ? $this->MRFS_Readiness_MyRep->getCityOptions($selectedPeriodId) : [];

        $data = [
            'title' => 'RFS Readiness MyRep',
            'isReady' => $isReady,
            'periodOptions' => $periodOptions,
            'period' => $period,
            'selectedPeriodId' => $selectedPeriodId,
            'selectedCity' => $selectedCity,
            'selectedRegional' => $selectedRegional,
            'selectedScope' => $selectedScope,
            'isSummaryMode' => $isSummaryMode,
            'selectedPriority' => $selectedPriority,
            'selectedFinalStatus' => $selectedFinalStatus,
            'cityOptions' => array_values($cityOptions),
            'items' => $items,
            'candidateCount' => (int) ($candidatePage['recordsFiltered'] ?? 0),
            'summary' => $selectedPeriodId > 0 ? $this->MRFS_Readiness_MyRep->getPeriodSummary($selectedPeriodId, $selectedCity, $selectedRegional, $selectedPriority, $selectedFinalStatus) : [],
            'checklistStatus' => $selectedPeriodId > 0 ? $this->MRFS_Readiness_MyRep->getChecklistStatusSummary($selectedPeriodId) : ['fix' => 0, 'belum' => 0, 'total' => 0],
            'weeklySummary' => $selectedPeriodId > 0 ? $this->MRFS_Readiness_MyRep->getWeeklyTargetRealization($selectedPeriodId, $selectedCity, $selectedRegional) : ['weeks' => []],
            'citySummaries' => $selectedPeriodId > 0 ? $this->MRFS_Readiness_MyRep->getAreaSummaries($selectedPeriodId, 'city') : [],
            'regionalSummaries' => $selectedPeriodId > 0 ? $this->MRFS_Readiness_MyRep->getAreaSummaries($selectedPeriodId, 'regional') : [],
            'cityWeeklySummaries' => $selectedPeriodId > 0 ? $this->MRFS_Readiness_MyRep->getAreaWeeklySummaries($selectedPeriodId, 'city') : [],
            'regionalWeeklySummaries' => $selectedPeriodId > 0 ? $this->MRFS_Readiness_MyRep->getAreaWeeklySummaries($selectedPeriodId, 'regional') : [],
            'pendingRequests' => $selectedPeriodId > 0 ? $this->MRFS_Readiness_MyRep->getPendingRequests($selectedPeriodId) : [],
            'notifications' => $this->MRFS_Readiness_MyRep->getUserNotifications($userId, 12),
            'aspectFields' => $this->MRFS_Readiness_MyRep->getAspectFields(),
            'canHoManage' => $this->isRfsHo(),
            'canSubmitChange' => $this->hasRole('SPV_AREA'),
            'roleKeys' => $this->getRoleKeys(),
        ];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('RFS_Readiness_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function exportWeeklySummary()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }
        if (!$this->MRFS_Readiness_MyRep->tablesReady()) {
            show_error('Tabel RFS Readiness belum tersedia.', 500);
            return;
        }

        $periodId = (int) $this->input->get('period_id');
        $period = $periodId > 0 ? $this->MRFS_Readiness_MyRep->getPeriodById($periodId) : [];
        if (empty($period)) {
            show_404();
            return;
        }

        $periodWeekly = $this->MRFS_Readiness_MyRep->getWeeklyTargetRealization($periodId);
        $periodLabel = sprintf('%04d-%02d', (int) $period['year_num'], (int) $period['month_num']);
        $this->loadPHPExcel();
        $excel = new PHPExcel();
        $excel->getProperties()
            ->setCreator('Database TKM')
            ->setTitle('RFS Readiness MyRep ' . $periodLabel)
            ->setSubject('Summary Target dan Realisasi RFS');

        $this->populateReadinessSummarySheet(
            $excel->setActiveSheetIndex(0),
            'Summary Kota',
            'city',
            $this->MRFS_Readiness_MyRep->getAreaSummaries($periodId, 'city'),
            $this->MRFS_Readiness_MyRep->getAreaWeeklySummaries($periodId, 'city'),
            $periodWeekly,
            $periodLabel
        );

        $regionalSheet = $excel->createSheet();
        $this->populateReadinessSummarySheet(
            $regionalSheet,
            'Summary Regional',
            'regional',
            $this->MRFS_Readiness_MyRep->getAreaSummaries($periodId, 'regional'),
            $this->MRFS_Readiness_MyRep->getAreaWeeklySummaries($periodId, 'regional'),
            $periodWeekly,
            $periodLabel
        );

        $detailSheet = $excel->createSheet();
        $this->populateReadinessDetailSheet(
            $detailSheet,
            $this->enrichTargetPeriodRows($this->enrichBatchApprovalDisplayRows($this->MRFS_Readiness_MyRep->getItems($periodId)), $period),
            $periodLabel
        );

        $excel->setActiveSheetIndex(0);
        $this->outputPHPExcel($excel, 'rfs_readiness_summary_' . $periodLabel . '.xls');
    }

    private function populateReadinessSummarySheet($sheet, $title, $scope, array $statusRows, array $weeklyRows, array $periodWeekly, $periodLabel)
    {
        $sheet->setTitle(substr($title, 0, 31));
        $statusMap = [];
        foreach ($statusRows as $row) {
            $label = strtoupper(trim((string) ($row['label'] ?? '')));
            if ($label !== '') {
                $statusMap[$label] = $row;
            }
        }

        $weekMap = [];
        foreach ((array) ($periodWeekly['weeks'] ?? []) as $weekRow) {
            $week = (string) ($weekRow['week'] ?? '');
            if ($week !== '') {
                $weekMap[$week] = true;
            }
        }
        foreach ($weeklyRows as $row) {
            foreach (array_keys((array) ($row['weeks'] ?? [])) as $week) {
                if ($week !== '') {
                    $weekMap[$week] = true;
                }
            }
        }
        $weeks = array_keys($weekMap);
        usort($weeks, static function ($left, $right) {
            return (int) substr((string) $left, 1) <=> (int) substr((string) $right, 1);
        });

        $dimensionColumns = $scope === 'city'
            ? ['No', 'Provinsi', 'Regional', 'Kota']
            : ['No', 'Regional'];
        $baseCount = count($dimensionColumns);
        $lastColumnIndex = $baseCount + 1 + 2 + 4 + 6 + 4 + count($weeks) + 1 + count($weeks) + 1 + 3 - 1;
        $lastColumn = PHPExcel_Cell::stringFromColumnIndex($lastColumnIndex);

        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->setCellValue('A1', 'RFS READINESS MYREP - ' . strtoupper($title));
        $sheet->mergeCells('A2:' . $lastColumn . '2');
        $sheet->setCellValue('A2', 'Periode ' . $periodLabel . ' | Summary status readiness, target RFS, dan realisasi RFS');

        foreach ($dimensionColumns as $index => $header) {
            $sheet->mergeCellsByColumnAndRow($index, 4, $index, 6);
            $sheet->setCellValueByColumnAndRow($index, 4, $header);
        }

        $col = $baseCount;
        $sheet->mergeCellsByColumnAndRow($col, 4, $col, 6);
        $sheet->setCellValueByColumnAndRow($col++, 4, 'Homepass DRM');

        $sheet->mergeCellsByColumnAndRow($col, 4, $col + 1, 5);
        $sheet->setCellValueByColumnAndRow($col, 4, 'Target RFS');
        $sheet->setCellValueByColumnAndRow($col++, 6, 'Prioritas 1');
        $sheet->setCellValueByColumnAndRow($col++, 6, 'Prioritas 2');

        $sheet->mergeCellsByColumnAndRow($col, 4, $col + 3, 4);
        $sheet->setCellValueByColumnAndRow($col, 4, 'MOREP');
        foreach (['Material', 'OLT'] as $aspect) {
            $sheet->mergeCellsByColumnAndRow($col, 5, $col + 1, 5);
            $sheet->setCellValueByColumnAndRow($col, 5, $aspect);
            $sheet->setCellValueByColumnAndRow($col++, 6, 'Ready');
            $sheet->setCellValueByColumnAndRow($col++, 6, 'Not Ready');
        }

        $sheet->mergeCellsByColumnAndRow($col, 4, $col + 5, 4);
        $sheet->setCellValueByColumnAndRow($col, 4, 'TKM');
        foreach (['Tenaga Kerja', 'Operasional', 'Accessories'] as $aspect) {
            $sheet->mergeCellsByColumnAndRow($col, 5, $col + 1, 5);
            $sheet->setCellValueByColumnAndRow($col, 5, $aspect);
            $sheet->setCellValueByColumnAndRow($col++, 6, 'Ready');
            $sheet->setCellValueByColumnAndRow($col++, 6, 'Not Ready');
        }

        foreach (['CONFIRMED', 'BELUM', 'RFS', 'WAITING CHANGE REQUEST'] as $header) {
            $sheet->mergeCellsByColumnAndRow($col, 4, $col, 6);
            $sheet->setCellValueByColumnAndRow($col++, 4, $header);
        }

        $targetStart = $col;
        $targetEnd = $col + count($weeks);
        $sheet->mergeCellsByColumnAndRow($targetStart, 4, $targetEnd, 5);
        $sheet->setCellValueByColumnAndRow($targetStart, 4, 'Target RFS Mingguan');
        foreach ($weeks as $week) {
            $sheet->setCellValueByColumnAndRow($col++, 6, $week);
        }
        $sheet->setCellValueByColumnAndRow($col++, 6, 'Total');

        $actualStart = $col;
        $actualEnd = $col + count($weeks);
        $sheet->mergeCellsByColumnAndRow($actualStart, 4, $actualEnd, 5);
        $sheet->setCellValueByColumnAndRow($actualStart, 4, 'Realisasi RFS Mingguan');
        foreach ($weeks as $week) {
            $sheet->setCellValueByColumnAndRow($col++, 6, $week);
        }
        $sheet->setCellValueByColumnAndRow($col++, 6, 'Total');

        foreach (['Remaining', 'Progress %', 'Total Cluster'] as $header) {
            $sheet->mergeCellsByColumnAndRow($col, 4, $col, 6);
            $sheet->setCellValueByColumnAndRow($col++, 4, $header);
        }

        $rowNo = 7;
        foreach ($weeklyRows as $index => $weeklyRow) {
            $label = strtoupper(trim((string) ($weeklyRow['label'] ?? '')));
            $statusRow = $statusMap[$label] ?? [];
            $col = 0;
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (int) $index + 1);
            if ($scope === 'city') {
                $sheet->setCellValueByColumnAndRow($col++, $rowNo, (string) ((($weeklyRow['province_name'] ?? '') !== '') ? $weeklyRow['province_name'] : ($statusRow['province_name'] ?? '')));
                $sheet->setCellValueByColumnAndRow($col++, $rowNo, (string) ((($weeklyRow['regional_name'] ?? '') !== '') ? $weeklyRow['regional_name'] : ($statusRow['regional_name'] ?? '')));
                $sheet->setCellValueByColumnAndRow($col++, $rowNo, $label);
            } else {
                $sheet->setCellValueByColumnAndRow($col++, $rowNo, $label);
            }
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($statusRow['total_hp'] ?? 0));
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($statusRow['priority_1_hp'] ?? 0));
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($statusRow['priority_2_hp'] ?? 0));

            foreach (['material', 'olt', 'tenaga_kerja', 'operasional', 'accessories'] as $aspectKey) {
                $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($statusRow['aspects'][$aspectKey]['ready_hp'] ?? 0));
                $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($statusRow['aspects'][$aspectKey]['not_ready_hp'] ?? 0));
            }

            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($statusRow['fix_count'] ?? 0));
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($statusRow['belum_count'] ?? 0));
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($statusRow['rfs_count'] ?? 0));
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($statusRow['waiting_cr'] ?? 0));

            foreach ($weeks as $week) {
                $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($weeklyRow['weeks'][$week]['target_hp'] ?? 0));
            }
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($weeklyRow['target_total'] ?? 0));
            foreach ($weeks as $week) {
                $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($weeklyRow['weeks'][$week]['actual_hp'] ?? 0));
            }
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($weeklyRow['actual_total'] ?? 0));
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($weeklyRow['remaining_total'] ?? 0));
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($weeklyRow['progress_percent'] ?? 0));
            $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) ($statusRow['total_cluster'] ?? 0));
            $rowNo++;
        }

        $lastDataRow = max(7, $rowNo - 1);
        $sheet->freezePaneByColumnAndRow($baseCount, 7);
        $sheet->getStyle('A1:' . $lastColumn . '2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A4:' . $lastColumn . '6')->getFont()->setBold(true);
        $sheet->getStyle('A4:' . $lastColumn . $lastDataRow)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet->getStyle('A4:' . $lastColumn . '6')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle('A7:' . $lastColumn . $lastDataRow)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $this->setExcelFill($sheet, 'A1:' . $lastColumn . '1', '0F3F93', 'FFFFFF');
        $this->setExcelFill($sheet, 'A2:' . $lastColumn . '2', 'DBEAFE', '0F3F93');
        $this->setExcelFill($sheet, 'A4:' . $lastColumn . '6', 'EAF1F8', '1F2A44');
        $this->setExcelFill($sheet, PHPExcel_Cell::stringFromColumnIndex($baseCount + 3) . '4:' . PHPExcel_Cell::stringFromColumnIndex($baseCount + 6) . '6', '8B5CF6', 'FFFFFF');
        $this->setExcelFill($sheet, PHPExcel_Cell::stringFromColumnIndex($baseCount + 7) . '4:' . PHPExcel_Cell::stringFromColumnIndex($baseCount + 12) . '6', 'F59E0B', '111827');
        $this->setExcelFill($sheet, PHPExcel_Cell::stringFromColumnIndex($targetStart) . '4:' . PHPExcel_Cell::stringFromColumnIndex($targetEnd) . '6', 'DBEAFE', '1457B8');
        $this->setExcelFill($sheet, PHPExcel_Cell::stringFromColumnIndex($actualStart) . '4:' . PHPExcel_Cell::stringFromColumnIndex($actualEnd) . '6', 'DCFCE7', '11723D');
        for ($readyCol = $baseCount + 3; $readyCol <= $baseCount + 12; $readyCol += 2) {
            $this->setExcelFill($sheet, PHPExcel_Cell::stringFromColumnIndex($readyCol) . '6', 'DCFCE7', '11723D');
            $this->setExcelFill($sheet, PHPExcel_Cell::stringFromColumnIndex($readyCol + 1) . '6', 'FEE2E2', 'B91C1C');
        }

        for ($i = 0; $i <= $lastColumnIndex; $i++) {
            $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
        }
        if ($lastDataRow >= 7) {
            $numberStart = PHPExcel_Cell::stringFromColumnIndex($baseCount);
            $sheet->getStyle($numberStart . '7:' . $lastColumn . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0');
            $progressCol = PHPExcel_Cell::stringFromColumnIndex($lastColumnIndex - 1);
            $sheet->getStyle($progressCol . '7:' . $progressCol . $lastDataRow)->getNumberFormat()->setFormatCode('0.0');
        }
    }

    private function setExcelFill($sheet, $range, $backgroundColor, $fontColor = '000000')
    {
        $sheet->getStyle($range)->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB($backgroundColor);
        $sheet->getStyle($range)->getFont()->getColor()->setRGB($fontColor);
    }

    private function batchStagingLabel($status)
    {
        $status = strtoupper(trim((string) $status));
        $labels = [
            'DRAFT' => 'Draft',
            'WAITING INPUT' => 'NY Batch Approval',
            'WAITING HO' => 'Menunggu Nomor Batch Approval',
            'WAITING MYREP' => 'Menunggu Review EMR',
            'WAITING FINANCE' => 'Menunggu Finance',
            'WAITING_BATCH_APPROVAL' => 'Menunggu Nomor Batch Approval',
            'BATCH_APPROVED' => 'Batch Approval Disetujui',
            'HOLD' => 'Ditahan',
            'WAITING_PRE_ZEYN_DOC' => 'NY Dokumen Tahap 1',
            'PRE_ZEYN_DOC_ON_REVIEW' => 'On Review Dokumen Tahap 1',
            'PRE_ZEYN_DOC_APPROVED' => 'Approved Dokumen Tahap 1',
            'PRE_ZEYN_FINANCE_ON_REVIEW' => 'On Review Finance Dokumen Tahap 1',
            'PRE_ZEYN_FINANCE_APPROVED' => 'ON PROSES PENGAJUAN SAKU',
            'WAITING_SAKU_FINANCE_APPROVAL' => 'Menunggu Approval Saku Finance',
            'WAITING_FINANCE_RELEASE' => 'Menunggu Pembayaran Finance',
            'RELEASED' => 'Donasi Dibayarkan',
            'WAITING_POST_ZEYN_DOC' => 'NY Dokumen Tahap 2',
            'POST_ZEYN_DOC_ON_REVIEW' => 'On Review Dokumen Tahap 2',
            'POST_ZEYN_DOC_APPROVED' => 'Approved Dokumen Tahap 2',
            'POST_ZEYN_FINANCE_ON_REVIEW' => 'On Review Finance Dokumen Tahap 2',
            'WAITING_ASTRI_SUBMISSION' => 'Menunggu Submit Astri',
            'ASTRI_ON_REVIEW' => 'On Review Astri',
            'NEED_REVISE_ASTRI' => 'NEED REVISI ASTRI',
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

    private function batchStagingBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'APPROVED':
            case 'RELEASED':
            case 'RAB DONE':
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
            case 'WAITING HO':
            case 'WAITING MYREP':
            case 'WAITING FINANCE':
                return 'info';
            case 'HOLD':
            case 'WAITING DOC':
            case 'WAITING_PRE_ZEYN_DOC':
            case 'PRE_ZEYN_DOC_ON_REVIEW':
            case 'PRE_ZEYN_FINANCE_ON_REVIEW':
            case 'WAITING_SAKU_FINANCE_APPROVAL':
            case 'WAITING_FINANCE_RELEASE':
            case 'WAITING_POST_ZEYN_DOC':
            case 'POST_ZEYN_DOC_ON_REVIEW':
            case 'POST_ZEYN_FINANCE_ON_REVIEW':
            case 'ASTRI_ON_REVIEW':
            case 'ON REVIEW':
                return 'warning';
            case 'REJECTED':
            case 'NEED_REVISE':
            case 'NEED_REVISE_ASTRI':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    private function populateReadinessDetailSheet($sheet, array $items, $periodLabel)
    {
        $headers = [
            'No',
            'Regional',
            'Provinsi',
            'Kota',
            'Batch Approval',
            'Cluster Code',
            'Cluster',
            'OLT',
            'HP DRM',
            'DRM Date',
            'Baseline Date',
            'Baseline Week',
            'Target Date',
            'Target Week',
            'Target Period Status',
            'Material',
            'OLT Readiness',
            'Tenaga Kerja',
            'Operasional',
            'Accessories',
            'Status Progress Cable',
            'Status Progress FAT',
            'Status Progress Tiang',
            'Status Progress',
            'Priority',
            'Checklist',
            'Final',
            'Actual RFS Date',
            'Pending Change Request',
            'Source',
            'Remark',
        ];

        $lastColumnIndex = count($headers) - 1;
        $lastColumn = PHPExcel_Cell::stringFromColumnIndex($lastColumnIndex);
        $sheet->setTitle('Detail Cluster');
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->setCellValue('A1', 'RFS READINESS MYREP - DETAIL CLUSTER');
        $sheet->mergeCells('A2:' . $lastColumn . '2');
        $sheet->setCellValue('A2', 'Periode ' . $periodLabel . ' | List cluster DRM done belum RFS beserta readiness dan target');

        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index, 4, $header);
        }

        $rowNo = 5;
        foreach ($items as $index => $item) {
            $targetDate = (string) ($item['current_planned_rfs_date'] ?? '');
            $actualDate = (string) ($item['actual_rfs_date'] ?? '');
            $baselineDate = (string) ($item['baseline_planned_rfs_date'] ?? '');
            $checklistStatus = empty($item['checklist_completed_at']) ? 'BELUM' : 'CONFIRMED';
            $pendingCr = empty($item['pending_request_status']) ? '-' : (string) $item['pending_request_status'];
            $values = [
                (int) $index + 1,
                (string) ($item['regional_name'] ?? ''),
                (string) ($item['province_name'] ?? ''),
                (string) ($item['city_name'] ?? ''),
                (string) ($item['batch_approval_label'] ?? 'NY'),
                (string) ($item['cluster_code'] ?? ''),
                (string) ($item['cluster_name'] ?? ''),
                (string) ($item['nama_olt'] ?? ''),
                (float) ($item['homepass_drm_snapshot'] ?? 0),
                (string) ($item['drm_date'] ?? ''),
                $baselineDate,
                $item['baseline_week'] !== null && $item['baseline_week'] !== '' ? 'W' . (int) $item['baseline_week'] : '',
                $targetDate,
                $item['current_week'] !== null && $item['current_week'] !== '' ? 'W' . (int) $item['current_week'] : '',
                (string) ($item['target_period_label'] ?? 'IN PERIOD'),
                (string) ($item['material_status'] ?? ''),
                (string) ($item['olt_status'] ?? ''),
                (string) ($item['tenaga_kerja_status'] ?? ''),
                (string) ($item['operasional_status'] ?? ''),
                (string) ($item['accessories_status'] ?? ''),
                (string) ($item['boq_cable_status'] ?? 'NY BOQ'),
                (string) ($item['boq_fat_status'] ?? 'NY BOQ'),
                (string) ($item['boq_tiang_status'] ?? 'NY BOQ'),
                (string) ($item['boq_progress_status'] ?? 'NY BOQ'),
                (string) ($item['priority_level'] ?? ''),
                $checklistStatus,
                (string) ($item['final_status'] ?? ''),
                $actualDate,
                $pendingCr,
                (string) ($item['source_type'] ?? ''),
                (string) ($item['remark'] ?? ''),
            ];

            foreach ($values as $col => $value) {
                $sheet->setCellValueByColumnAndRow($col, $rowNo, $value);
            }
            if (!empty($item['batch_approval_url'])) {
                $sheet->getCellByColumnAndRow(4, $rowNo)->getHyperlink()->setUrl((string) $item['batch_approval_url']);
                $sheet->getStyleByColumnAndRow(4, $rowNo)->getFont()->getColor()->setRGB('1457B8');
                $sheet->getStyleByColumnAndRow(4, $rowNo)->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
            }
            $rowNo++;
        }

        $lastDataRow = max(5, $rowNo - 1);
        $sheet->freezePane('A5');
        $sheet->getStyle('A1:' . $lastColumn . '2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A4:' . $lastColumn . '4')->getFont()->setBold(true);
        $sheet->getStyle('A4:' . $lastColumn . $lastDataRow)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet->getStyle('A4:' . $lastColumn . '4')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle('A5:' . $lastColumn . $lastDataRow)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('G5:G' . $lastDataRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('AA5:AA' . $lastDataRow)->getAlignment()->setWrapText(true);

        $this->setExcelFill($sheet, 'A1:' . $lastColumn . '1', '0F3F93', 'FFFFFF');
        $this->setExcelFill($sheet, 'A2:' . $lastColumn . '2', 'DBEAFE', '0F3F93');
        $this->setExcelFill($sheet, 'A4:' . $lastColumn . '4', 'EAF1F8', '1F2A44');
        $this->setExcelFill($sheet, 'K4:O4', 'DBEAFE', '1457B8');
        $this->setExcelFill($sheet, 'P4:Q4', '8B5CF6', 'FFFFFF');
        $this->setExcelFill($sheet, 'R4:T4', 'F59E0B', '111827');
        $this->setExcelFill($sheet, 'X4:X4', 'DCFCE7', '11723D');

        for ($i = 0; $i <= $lastColumnIndex; $i++) {
            $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
        }
        if ($lastDataRow >= 5) {
            $sheet->getStyle('I5:I' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0');
        }
    }

    public function createPeriod()
    {
        $this->requireRfsHo();
        $year = (int) $this->input->post('year_num');
        $month = (int) $this->input->post('month_num');
        $meetingDate = $this->normalizeDate($this->input->post('meeting_date'));
        $remark = trim((string) $this->input->post('remark'));
        $periodId = $this->MRFS_Readiness_MyRep->createPeriod($year, $month, $meetingDate, $remark, $this->userId());
        $this->session->set_flashdata($periodId > 0 ? 'success' : 'error', $periodId > 0 ? 'Period readiness berhasil dibuat.' : 'Period readiness gagal dibuat.');
        redirect('RFS_Readiness_MyRep?period_id=' . (int) $periodId);
    }

    public function generateCandidates()
    {
        $this->requireRfsHo();
        $periodId = (int) $this->input->post('period_id');
        $period = $this->MRFS_Readiness_MyRep->getPeriodById($periodId);
        $sourceType = !empty($period) && $period['status_period'] === 'LOCKED' ? 'LATE_ADDITION' : 'NEW';
        $selectedOnly = (string) $this->input->post('selected_only') === '1';
        $clusterIds = array_values(array_unique(array_filter(array_map('intval', (array) $this->input->post('cluster_ids')))));
        if ($selectedOnly && empty($clusterIds)) {
            $message = 'Pilih minimal 1 cluster kandidat untuk generate terpilih.';
            if ($this->isAjaxRequest()) {
                $this->outputJson(['status' => false, 'message' => $message, 'count' => 0]);
                return;
            }
            $this->session->set_flashdata('error', $message);
            redirect($this->periodUrl($periodId));
            return;
        }
        $count = $this->MRFS_Readiness_MyRep->generateCandidates(
            $periodId,
            $this->userId(),
            $sourceType,
            strtoupper(trim((string) $this->input->post('city'))),
            strtoupper(trim((string) $this->input->post('regional'))),
            $selectedOnly ? $clusterIds : []
        );
        if ($this->isAjaxRequest()) {
            $this->outputJson(['status' => true, 'message' => $count . ' kandidat cluster berhasil ditambahkan.', 'count' => $count]);
            return;
        }
        $this->session->set_flashdata('success', $count . ' kandidat cluster berhasil ditambahkan.');
        redirect($this->periodUrl($periodId));
    }

    public function saveBaseline()
    {
        $this->requireRfsHo();
        $periodId = (int) $this->input->post('period_id');
        $rows = [];
        foreach ((array) $this->input->post('items') as $itemId => $row) {
            $rows[(int) $itemId] = is_array($row) ? $row : [];
        }
        $count = $this->MRFS_Readiness_MyRep->saveHoBaseline($periodId, $rows, $this->userId());
        $this->session->set_flashdata('success', $count . ' checklist readiness berhasil disimpan.');
        redirect($this->periodUrl($periodId));
    }

    public function saveItemBaseline()
    {
        $this->requireRfsHo();
        $periodId = (int) $this->input->post('period_id');
        $itemId = (int) $this->input->post('item_id');
        $plannedDate = $this->normalizeDate($this->input->post('planned_rfs_date'));
        if ($plannedDate === null) {
            $message = 'Planned Date wajib diisi. Week akan otomatis dihitung dari tanggal.';
            if ($this->isAjaxRequest()) {
                $this->outputJson(['status' => false, 'message' => $message]);
                return;
            }
            $this->session->set_flashdata('error', $message);
            redirect($this->periodUrl($periodId));
            return;
        }
        $existingItem = $this->MRFS_Readiness_MyRep->getItemById($itemId);
        $newWeek = (int) date('W', strtotime($plannedDate));
        $targetChanged = !empty($existingItem)
            && !empty($existingItem['current_planned_rfs_date'])
            && (
                (string) $existingItem['current_planned_rfs_date'] !== (string) $plannedDate
                || (int) ($existingItem['current_week'] ?? 0) !== $newWeek
            );
        if ($targetChanged && trim((string) $this->input->post('remark')) === '') {
            $message = 'Remark wajib diisi jika target date/week berubah.';
            if ($this->isAjaxRequest()) {
                $this->outputJson(['status' => false, 'message' => $message]);
                return;
            }
            $this->session->set_flashdata('error', $message);
            redirect($this->periodUrl($periodId));
            return;
        }
        $payload = [
            'planned_rfs_date' => $plannedDate,
            'material_status' => $this->input->post('material_status'),
            'olt_status' => $this->input->post('olt_status'),
            'tenaga_kerja_status' => $this->input->post('tenaga_kerja_status'),
            'operasional_status' => $this->input->post('operasional_status'),
            'accessories_status' => $this->input->post('accessories_status'),
            'checklist_is_fixed' => $this->input->post('checklist_is_fixed'),
            'remark' => $this->input->post('remark'),
        ];
        $count = $this->MRFS_Readiness_MyRep->saveHoBaseline($periodId, [$itemId => $payload], $this->userId());
        $savedMessage = (string) $this->input->post('checklist_is_fixed') === '1'
            ? 'Checklist cluster berhasil disimpan sebagai CONFIRMED.'
            : 'Checklist cluster berhasil disimpan sebagai draft/BELUM.';
        if ($this->isAjaxRequest()) {
            $this->outputJson([
                'status' => $count > 0,
                'message' => $count > 0 ? $savedMessage : 'Checklist cluster gagal disimpan atau period sudah terkunci.',
            ]);
            return;
        }
        $this->session->set_flashdata($count > 0 ? 'success' : 'error', $count > 0 ? $savedMessage : 'Checklist cluster gagal disimpan atau period sudah terkunci.');
        redirect($this->periodUrl($periodId));
    }

    public function itemTableData()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->outputJson(['draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
            return;
        }

        $periodId = (int) $this->input->get('period_id');
        $city = strtoupper(trim((string) $this->input->get('city')));
        $regional = strtoupper(trim((string) $this->input->get('regional')));
        $priority = strtoupper(trim((string) $this->input->get('priority')));
        $finalStatus = strtoupper(trim((string) $this->input->get('final_status')));
        $draw = (int) $this->input->get('draw');
        $start = (int) $this->input->get('start');
        $length = (int) $this->input->get('length');
        $searchInput = (array) $this->input->get('search');
        $orderRows = (array) $this->input->get('order');
        $search = (string) ($searchInput['value'] ?? '');
        $orderInput = (array) ($orderRows[0] ?? []);

        $page = $periodId > 0
            ? $this->MRFS_Readiness_MyRep->getItemsPage($periodId, $city, $priority, $finalStatus, $start, $length, $search, $orderInput, $regional)
            : ['recordsTotal' => 0, 'recordsFiltered' => 0, 'rows' => []];

        $period = $periodId > 0 ? $this->MRFS_Readiness_MyRep->getPeriodById($periodId) : [];
        $periodStatus = strtoupper((string) ($period['status_period'] ?? ''));
        $canHoManage = $this->isRfsHo();
        $canSubmitChange = $this->hasRole('SPV_AREA');

        $rows = [];
        foreach ($this->enrichBatchApprovalDisplayRows((array) ($page['rows'] ?? [])) as $row) {
            $row = $this->enrichTargetPeriodState($row, $period);
            $row['can_edit_baseline'] = $canHoManage
                && $periodStatus !== 'CLOSED'
                && ($periodStatus === 'DRAFT' || (strtoupper((string) ($row['source_type'] ?? '')) === 'LATE_ADDITION' && empty($row['baseline_week'])));
            $row['can_submit_change'] = $canSubmitChange && $periodStatus === 'LOCKED' && $periodStatus !== 'CLOSED';
            $rows[] = $row;
        }

        $this->outputJson([
            'draw' => $draw,
            'recordsTotal' => (int) ($page['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($page['recordsFiltered'] ?? 0),
            'data' => $rows,
        ]);
    }

    private function enrichBatchApprovalDisplayRows(array $rows)
    {
        $batchCache = [];
        foreach ($rows as &$row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $batchId = (int) ($row['readiness_batch_id'] ?? 0);
            $displayStatus = strtoupper(trim((string) ($row['batch_approval_status'] ?? '')));

            if ($clusterId > 0 && $batchId > 0) {
                if (!array_key_exists($clusterId, $batchCache)) {
                    $batchCache[$clusterId] = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
                }
                $batchRow = (array) ($batchCache[$clusterId] ?? []);
                $displayStatus = strtoupper(trim((string) ($batchRow['display_staging_status'] ?? $batchRow['staging_status'] ?? $displayStatus)));
            }

            if ($displayStatus === '') {
                $displayStatus = $batchId > 0 ? 'DRAFT' : 'WAITING INPUT';
            }

            $row['batch_approval_display_status'] = $displayStatus;
            $row['batch_approval_label'] = $this->batchStagingLabel($displayStatus);
            $row['batch_approval_badge_class'] = $this->batchStagingBadgeClass($displayStatus);
            $row['batch_approval_url'] = ($clusterId > 0 && $batchId > 0)
                ? base_url('Batch_Approval_MyRep/detail/' . $clusterId)
                : '';
        }
        unset($row);

        return $rows;
    }

    private function enrichTargetPeriodRows(array $rows, array $period)
    {
        foreach ($rows as &$row) {
            $row = $this->enrichTargetPeriodState($row, $period);
        }
        unset($row);
        return $rows;
    }

    private function enrichTargetPeriodState(array $row, array $period)
    {
        $label = 'IN PERIOD';
        $state = 'IN_PERIOD';
        $plannedDate = trim((string) ($row['current_planned_rfs_date'] ?? ''));
        if ($plannedDate !== '' && !empty($period)) {
            $targetYear = (int) date('Y', strtotime($plannedDate));
            $targetMonth = (int) date('n', strtotime($plannedDate));
            $periodYear = (int) ($period['year_num'] ?? 0);
            $periodMonth = (int) ($period['month_num'] ?? 0);
            if ($targetYear > $periodYear || ($targetYear === $periodYear && $targetMonth > $periodMonth)) {
                $state = 'SHIFTED_OUT';
                $label = 'SHIFTED OUT';
            } elseif ($targetYear < $periodYear || ($targetYear === $periodYear && $targetMonth < $periodMonth)) {
                $state = 'PAST_TARGET';
                $label = 'PAST TARGET';
            }
        }
        if (strtoupper((string) ($row['source_type'] ?? '')) === 'SHIFTED_IN') {
            $state = 'SHIFTED_IN';
            $label = 'SHIFTED IN';
        }
        $baselineWeek = (int) ($row['baseline_week'] ?? 0);
        $currentWeek = (int) ($row['current_week'] ?? 0);
        $row['target_period_state'] = $state;
        $row['target_period_label'] = $label;
        $row['target_slipped'] = $baselineWeek > 0 && $currentWeek > $baselineWeek ? 1 : 0;
        return $row;
    }

    public function candidateTableData()
    {
        if (empty($this->session->userdata('id_user')) || !$this->isRfsHo()) {
            $this->outputJson(['draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
            return;
        }

        $periodId = (int) $this->input->get('period_id');
        $city = strtoupper(trim((string) $this->input->get('city')));
        $regional = strtoupper(trim((string) $this->input->get('regional')));
        $draw = (int) $this->input->get('draw');
        $start = (int) $this->input->get('start');
        $length = (int) $this->input->get('length');
        $searchInput = (array) $this->input->get('search');
        $orderRows = (array) $this->input->get('order');
        $search = (string) ($searchInput['value'] ?? '');
        $orderInput = (array) ($orderRows[0] ?? []);

        $page = $periodId > 0
            ? $this->MRFS_Readiness_MyRep->getCandidateClustersPage($periodId, $city, $start, $length, $search, $orderInput, $regional)
            : ['recordsTotal' => 0, 'recordsFiltered' => 0, 'rows' => []];

        $this->outputJson([
            'draw' => $draw,
            'recordsTotal' => (int) ($page['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($page['recordsFiltered'] ?? 0),
            'data' => (array) ($page['rows'] ?? []),
        ]);
    }

    public function itemHistoryData()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->outputJson(['status' => false, 'message' => 'Session tidak aktif.', 'data' => []]);
            return;
        }
        $itemId = (int) $this->input->get('item_id');
        $item = $this->MRFS_Readiness_MyRep->getItemById($itemId);
        if (empty($item)) {
            $this->outputJson(['status' => false, 'message' => 'Item tidak ditemukan.', 'data' => []]);
            return;
        }
        $this->outputJson([
            'status' => true,
            'item' => [
                'cluster_name' => (string) ($item['cluster_name'] ?? '-'),
                'city_name' => (string) ($item['city_name'] ?? '-'),
                'regional_name' => (string) ($item['regional_name'] ?? '-'),
            ],
            'data' => $this->MRFS_Readiness_MyRep->getItemHistory($itemId),
        ]);
    }

    public function lockPeriod()
    {
        $this->requireRfsHo();
        $periodId = (int) $this->input->post('period_id');
        $ok = $this->MRFS_Readiness_MyRep->lockPeriod($periodId, $this->userId());
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Period berhasil di-lock.' : 'Period gagal di-lock.');
        redirect($this->periodUrl($periodId));
    }

    public function unlockPeriod()
    {
        $this->requireRfsHo();
        $periodId = (int) $this->input->post('period_id');
        $remark = trim((string) $this->input->post('unlock_remark'));
        $result = $this->MRFS_Readiness_MyRep->unlockPeriod($periodId, $remark, $this->userId());
        $this->session->set_flashdata(!empty($result['status']) ? 'success' : 'error', (string) ($result['message'] ?? 'Unlock period gagal.'));
        redirect($this->periodUrl($periodId));
    }

    public function submitChangeRequest()
    {
        if (!$this->hasRole('SPV_AREA')) {
            render_no_access('Hanya SPV Area yang dapat membuat change request readiness.');
        }
        $itemId = (int) $this->input->post('item_id');
        $item = $this->MRFS_Readiness_MyRep->getItemById($itemId);
        if (empty($item) || !$this->MRFS_Readiness_MyRep->userHasCityRole($this->userId(), (string) $item['city_name'], 'spv_area')) {
            render_no_access('Anda bukan SPV Area untuk kota cluster ini.');
        }

        $plannedDate = $this->normalizeDate($this->input->post('planned_rfs_date'));
        if ($plannedDate === null) {
            $message = 'Planned Date wajib diisi. Week akan otomatis dihitung dari tanggal.';
            if ($this->isAjaxRequest()) {
                $this->outputJson(['status' => false, 'message' => $message]);
                return;
            }
            $this->session->set_flashdata('error', $message);
            redirect($this->periodUrl((int) $item['id_period']));
            return;
        }
        $payload = [
            'planned_rfs_date' => $plannedDate,
            'material_status' => $this->input->post('material_status'),
            'olt_status' => $this->input->post('olt_status'),
            'tenaga_kerja_status' => $this->input->post('tenaga_kerja_status'),
            'operasional_status' => $this->input->post('operasional_status'),
            'accessories_status' => $this->input->post('accessories_status'),
            'reason_category' => $this->input->post('reason_category'),
            'remark' => $this->input->post('remark'),
        ];
        $item = $this->MRFS_Readiness_MyRep->getItemById($itemId);
        $newWeek = $plannedDate ? (int) date('W', strtotime($plannedDate)) : 0;
        $targetChanged = !empty($item)
            && ((int) ($item['current_week'] ?? 0) !== $newWeek || (string) ($item['current_planned_rfs_date'] ?? '') !== (string) $plannedDate);
        if ($targetChanged && trim((string) $payload['reason_category']) === '') {
            $message = 'Reason Category wajib diisi jika target date/week berubah.';
            if ($this->isAjaxRequest()) {
                $this->outputJson(['status' => false, 'message' => $message]);
                return;
            }
            $this->session->set_flashdata('error', $message);
            redirect($this->periodUrl((int) ($item['id_period'] ?? 0)));
            return;
        }
        $result = $this->MRFS_Readiness_MyRep->createChangeRequest($itemId, $payload, $_FILES['evidence_pdf'] ?? [], $this->userId());
        if (!empty($result['status']) && !empty($result['id_change_request']) && !empty($_FILES['evidence_pdf']['name'])) {
            $upload = $this->storeEvidence((int) $result['id_change_request']);
            if (!$upload['status']) {
                $this->db->where('id_change_request', (int) $result['id_change_request'])->delete('tb_myrep_rfs_readiness_history');
                $this->db->where('id_change_request', (int) $result['id_change_request'])->delete('tb_myrep_rfs_readiness_change_request');
                if ($this->isAjaxRequest()) {
                    $this->outputJson(['status' => false, 'message' => $upload['message']]);
                    return;
                }
                $this->session->set_flashdata('error', $upload['message']);
                redirect($this->periodUrl((int) $item['id_period']));
                return;
            }
            $this->MRFS_Readiness_MyRep->saveEvidence((int) $result['id_change_request'], $upload, $this->userId());
            $this->MRFS_Readiness_MyRep->notifyRoleForRequest(
                (int) $result['id_change_request'],
                'sm_area',
                'Change Request RFS Readiness menunggu approval SM: ' . (string) ($item['cluster_name'] ?? 'Cluster')
            );
        }
        if ($this->isAjaxRequest()) {
            $this->outputJson([
                'status' => !empty($result['status']),
                'message' => (string) ($result['message'] ?? 'Change request gagal diproses.'),
            ]);
            return;
        }
        $this->session->set_flashdata(!empty($result['status']) ? 'success' : 'error', (string) ($result['message'] ?? 'Change request gagal diproses.'));
        redirect($this->periodUrl((int) $item['id_period']));
    }

    public function reviewChangeRequest()
    {
        $requestId = (int) $this->input->post('id_change_request');
        $request = $this->MRFS_Readiness_MyRep->getChangeRequestById($requestId);
        if (empty($request)) {
            $this->session->set_flashdata('error', 'Change request tidak ditemukan.');
            redirect('RFS_Readiness_MyRep');
            return;
        }
        if (!$this->canReviewRequest($request)) {
            render_no_access('Anda tidak memiliki approval untuk request ini.');
        }

        $decision = strtoupper(trim((string) $this->input->post('decision')));
        $note = trim((string) $this->input->post('approval_note'));
        $oldStatus = (string) ($request['status_request'] ?? '');
        $ok = $this->MRFS_Readiness_MyRep->reviewChangeRequest($requestId, $decision === 'REJECT' ? 'REJECT' : 'APPROVE', $note, $this->userId());
        if ($ok) {
            if ($decision === 'REJECT') {
                $this->MRFS_Readiness_MyRep->notifyRequester($requestId, 'Change Request RFS Readiness ditolak: ' . (string) ($request['cluster_name'] ?? 'Cluster'));
            } elseif ($oldStatus === 'WAITING_SM') {
                $this->MRFS_Readiness_MyRep->notifyRoleForRequest($requestId, 'rpm_area', 'Change Request RFS Readiness menunggu approval RPM: ' . (string) ($request['cluster_name'] ?? 'Cluster'));
            } elseif ($oldStatus === 'WAITING_RPM') {
                $this->MRFS_Readiness_MyRep->notifyRoleForRequest($requestId, 'rfs_ho', 'Change Request RFS Readiness menunggu approval RFS HO: ' . (string) ($request['cluster_name'] ?? 'Cluster'));
            } elseif ($oldStatus === 'WAITING_RFS_HO') {
                $this->MRFS_Readiness_MyRep->notifyRequester($requestId, 'Change Request RFS Readiness sudah final approved: ' . (string) ($request['cluster_name'] ?? 'Cluster'));
            }
        }
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Review change request berhasil disimpan.' : 'Review change request gagal.');
        redirect($this->periodUrl((int) $request['id_period']));
    }

    public function markNotificationRead()
    {
        $notificationId = (int) $this->input->post('id_notification');
        $this->MRFS_Readiness_MyRep->markNotificationRead($notificationId, $this->userId());
        $periodId = (int) $this->input->post('period_id');
        redirect($periodId > 0 ? $this->periodUrl($periodId) : 'RFS_Readiness_MyRep');
    }

    public function syncActualRfs()
    {
        $this->requireRfsHo();
        $periodId = (int) $this->input->post('period_id');
        $count = $this->MRFS_Readiness_MyRep->syncActualRfsForPeriod($periodId, $this->userId());
        $this->session->set_flashdata('success', $count . ' item disinkronkan dari Monitoring RFS.');
        redirect($this->periodUrl($periodId));
    }

    public function closePeriod()
    {
        $this->requireRfsHo();
        $periodId = (int) $this->input->post('period_id');
        $finalStatuses = (array) $this->input->post('final_status');
        $nextYear = (int) $this->input->post('next_year_num');
        $nextMonth = (int) $this->input->post('next_month_num');
        $result = $this->MRFS_Readiness_MyRep->closePeriod($periodId, $finalStatuses, $nextYear, $nextMonth, $this->userId());
        $this->session->set_flashdata(!empty($result['closed']) ? 'success' : 'error', !empty($result['closed']) ? 'Period berhasil ditutup. Carry over dibuat: ' . (int) $result['carry_over'] : 'Period gagal ditutup.');
        redirect($this->periodUrl($periodId));
    }

    public function previewEvidence($evidenceId)
    {
        $row = (array) $this->db
            ->from('tb_myrep_rfs_readiness_evidence')
            ->where('id_evidence', (int) $evidenceId)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($row) || empty($row['file_path'])) {
            show_404();
            return;
        }
        $path = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $row['file_path']);
        if (!is_file($path)) {
            show_404();
            return;
        }
        $this->output
            ->set_content_type('application/pdf')
            ->set_output(file_get_contents($path));
    }

    private function storeEvidence($requestId)
    {
        $uploadDir = './uploads/myrep_rfs_readiness/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }
        $this->upload->initialize([
            'upload_path' => $uploadDir,
            'allowed_types' => 'pdf',
            'max_size' => 10240,
            'encrypt_name' => true,
            'file_ext_tolower' => true,
        ]);
        if (!$this->upload->do_upload('evidence_pdf')) {
            return ['status' => false, 'message' => strip_tags($this->upload->display_errors('', ''))];
        }
        $data = $this->upload->data();
        return [
            'status' => true,
            'file_name' => 'rfs-readiness-cr-' . (int) $requestId . '-' . $data['orig_name'],
            'file_path' => 'uploads/myrep_rfs_readiness/' . $data['file_name'],
        ];
    }

    private function canReviewRequest(array $request)
    {
        $status = (string) ($request['status_request'] ?? '');
        $city = (string) ($request['city_name'] ?? '');
        if ($status === 'WAITING_SM') {
            return $this->MRFS_Readiness_MyRep->userHasCityRole($this->userId(), $city, 'sm_area');
        }
        if ($status === 'WAITING_RPM') {
            return $this->MRFS_Readiness_MyRep->userHasCityRole($this->userId(), $city, 'rpm_area');
        }
        if ($status === 'WAITING_RFS_HO') {
            return $this->MRFS_Readiness_MyRep->userHasCityRole($this->userId(), $city, 'rfs_ho') || $this->isRfsHo();
        }
        return false;
    }

    private function isRfsHo()
    {
        return (string) $this->session->userdata('nama_level') === 'Super Admin' || $this->hasRole('RFS_HO');
    }

    private function hasRole($roleKey)
    {
        return in_array(strtoupper((string) $roleKey), $this->getRoleKeys(), true);
    }

    private function getRoleKeys()
    {
        if (!isset($this->myrepAccess) || !method_exists($this->myrepAccess, 'getCurrentRoleKeys')) {
            return [];
        }
        return array_map('strtoupper', (array) $this->myrepAccess->getCurrentRoleKeys());
    }

    private function requireRfsHo()
    {
        if (!$this->isRfsHo()) {
            render_no_access('Hanya RFS HO yang dapat menjalankan aksi ini.');
        }
    }

    private function userId()
    {
        return (int) $this->session->userdata('id_user');
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

    private function loadPHPExcel()
    {
        if (!class_exists('PHPExcel')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        }
        if (!class_exists('PHPExcel_IOFactory')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel/IOFactory.php';
        }
    }

    private function outputPHPExcel($excel, $filename)
    {
        error_reporting(error_reporting() & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
        @ini_set('display_errors', '0');
        if (function_exists('ob_get_level')) {
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer->save('php://output');
        exit;
    }

    private function periodUrl($periodId)
    {
        $params = ['period_id' => (int) $periodId];
        $scope = strtolower(trim((string) ($this->input->post('scope') ?: $this->input->get('scope'))));
        if ($scope === 'city') {
            $city = strtoupper(trim((string) ($this->input->post('city') ?: $this->input->get('city'))));
            if ($city !== '') {
                $params['scope'] = 'city';
                $params['city'] = $city;
            }
        } elseif ($scope === 'regional') {
            $regional = strtoupper(trim((string) ($this->input->post('regional') ?: $this->input->get('regional'))));
            if ($regional !== '') {
                $params['scope'] = 'regional';
                $params['regional'] = $regional;
            }
        }

        return 'RFS_Readiness_MyRep?' . http_build_query($params);
    }

    private function outputJson(array $payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function isAjaxRequest()
    {
        return $this->input->is_ajax_request() || strtolower((string) $this->input->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }
}
