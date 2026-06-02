<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class Checklist_Dokument_MyRep extends CI_Controller
{
    private $cityPicMappingCache = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('MChecklist_Dokument_MyRep');
        $this->load->model('MMonitoring_RFS_MyRep');
        $this->load->model('MMyRep_Cleanup');
        $this->load->library('upload');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('Checklist_Dokument_MyRep');
            $this->myrepAccess->enforceByMethod('Checklist_Dokument_MyRep', (string) $this->router->fetch_method());
        }
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedRegional = strtoupper(trim((string) $this->input->get('regional')));

        try {
            // Sync bridge bisa berat; jangan sampai memblokir render halaman monitoring.
            $this->MMonitoring_RFS_MyRep->syncMyrepCompatibilityBridge((int) date('Y'), (int) date('n'), $selectedCity);
        } catch (\Throwable $e) {
            log_message('error', 'Checklist index sync bridge failed: ' . $e->getMessage());
        }

        $data['title'] = 'Checklist Dokument';
        $data['atpSchemaReady'] = $this->MChecklist_Dokument_MyRep->supportsAtpColumns();
        $data['selectedCity'] = $selectedCity;
        $data['selectedRegional'] = $selectedRegional;
        $data['cityOptions'] = $this->MChecklist_Dokument_MyRep->getCityOptions();
        $data['regionalOptions'] = $this->MChecklist_Dokument_MyRep->getRegionalOptions();
        $data['clusterList'] = [];
        $data['renderClusterRows'] = false;
        $data['documentItemList'] = [];
        $data['dashboardSummary'] = $this->buildDashboardSummary([], []);
        $data['itemFilterOptions'] = [];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Checklist_Dokument_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function dashboardData()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'dashboardSummary' => $this->buildDashboardSummary([], []),
                    'itemFilterOptions' => [],
                ]));
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->post('selected_city')));
        $selectedRegional = strtoupper(trim((string) $this->input->post('selected_regional')));
        $cacheKey = 'checklist_doc_dashboard_' . md5($selectedCity . '|' . $selectedRegional);

        try {
            $cachedPayload = $this->getChecklistCache($cacheKey);
            if (!is_array($cachedPayload)) {
                $clusterList = $this->MChecklist_Dokument_MyRep->getFullRfsClusters($selectedCity, $selectedRegional);
                $documentItemList = $this->MChecklist_Dokument_MyRep->getClusterDocumentItemRows($selectedCity, $selectedRegional);
                $cachedPayload = [
                    'dashboardSummary' => $this->buildDashboardSummary($clusterList, $documentItemList),
                    'itemFilterOptions' => $this->buildItemFilterOptions($documentItemList),
                    'generatedAt' => date('Y-m-d H:i:s'),
                ];
                $this->saveChecklistCache($cacheKey, $cachedPayload, 300);
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => true,
                    'dashboardSummary' => isset($cachedPayload['dashboardSummary']) && is_array($cachedPayload['dashboardSummary'])
                        ? $cachedPayload['dashboardSummary']
                        : $this->buildDashboardSummary([], []),
                    'itemFilterOptions' => isset($cachedPayload['itemFilterOptions']) && is_array($cachedPayload['itemFilterOptions'])
                        ? $cachedPayload['itemFilterOptions']
                        : [],
                    'generatedAt' => (string) ($cachedPayload['generatedAt'] ?? ''),
                ]));
        } catch (\Throwable $e) {
            log_message('error', 'Checklist dashboardData failed: ' . $e->getMessage());
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'dashboardSummary' => $this->buildDashboardSummary([], []),
                    'itemFilterOptions' => [],
                ]));
        }
    }

    public function clusterTableData()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'draw' => (int) $this->input->post('draw'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]));
            return;
        }

        try {
            $selectedCity = strtoupper(trim((string) $this->input->post('selected_city')));
            $selectedRegional = strtoupper(trim((string) $this->input->post('selected_regional')));
            $searchPayload = $this->input->post('search');
            $searchValue = '';
            if (is_array($searchPayload) && isset($searchPayload['value'])) {
                $searchValue = strtoupper(trim((string) $searchPayload['value']));
            }

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
                $length = 5;
            }

            $page = $this->MChecklist_Dokument_MyRep->getFullRfsClusterPage(
                $selectedCity,
                $selectedRegional,
                ['search' => $searchValue],
                $start,
                $length,
                $order
            );

            $canHapus = isset($this->myrepAccess)
                ? $this->myrepAccess->hasPermission('Checklist_Dokument_MyRep', 'HAPUS')
                : true;

            $data = [];
            $no = $start + 1;
            $rows = isset($page['rows']) && is_array($page['rows']) ? $page['rows'] : [];
            foreach ($rows as $cluster) {
                $data[] = $this->buildClusterTableRow($cluster, $no++, $canHapus);
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'draw' => (int) $this->input->post('draw'),
                    'recordsTotal' => (int) ($page['recordsTotal'] ?? 0),
                    'recordsFiltered' => (int) ($page['recordsFiltered'] ?? 0),
                    'data' => $data,
                ]));
        } catch (\Throwable $e) {
            log_message('error', 'Checklist clusterTableData failed: ' . $e->getMessage());
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'draw' => (int) $this->input->post('draw'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]));
        }
    }

    public function itemTableData()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'draw' => (int) $this->input->post('draw'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]));
            return;
        }

        try {
            $selectedCity = strtoupper(trim((string) $this->input->post('selected_city')));
            $selectedRegional = strtoupper(trim((string) $this->input->post('selected_regional')));

            $searchPayload = $this->input->post('search');
            $searchValue = '';
            if (is_array($searchPayload) && isset($searchPayload['value'])) {
                $searchValue = strtoupper(trim((string) $searchPayload['value']));
            }

            $start = max(0, (int) $this->input->post('start'));
            $length = (int) $this->input->post('length');
            if ($length <= 0) {
                $length = 10;
            }

            $page = $this->MChecklist_Dokument_MyRep->getClusterDocumentItemPage(
                $selectedCity,
                $selectedRegional,
                [
                    'item_regional' => $this->input->post('item_regional'),
                    'item_city' => $this->input->post('item_city'),
                    'item_cluster' => $this->input->post('item_cluster'),
                    'item_scope' => $this->input->post('item_scope'),
                    'item_sow' => $this->input->post('item_sow'),
                    'item_doc' => $this->input->post('item_doc'),
                    'internal_status' => $this->input->post('internal_status'),
                    'astri_status' => $this->input->post('astri_status'),
                    'quick_type' => $this->input->post('quick_type'),
                    'quick_value' => $this->input->post('quick_value'),
                    'search' => $searchValue,
                ],
                $start,
                $length
            );

            $recordsTotal = (int) ($page['recordsTotal'] ?? 0);
            $recordsFiltered = (int) ($page['recordsFiltered'] ?? 0);
            $pageRows = isset($page['rows']) && is_array($page['rows']) ? $page['rows'] : [];

            $data = [];
            $no = $start + 1;
            foreach ($pageRows as $row) {
                $idCluster = (int) ($row['id_cluster'] ?? 0);
                $internalStatusLabel = $this->statusLabel((string) ($row['status_file'] ?? 'NOT UPLOADED'));
                $astriStatusLabel = $this->statusLabel((string) ($row['astri_status'] ?? 'NY'));
                $internalBadge = $this->statusBadge((string) ($row['status_file'] ?? 'NOT UPLOADED'));
                $astriBadge = $this->statusBadge((string) ($row['astri_status'] ?? 'NY'));

                $data[] = [
                    $no++,
                    htmlspecialchars((string) ($row['regional_name'] ?? '-'), ENT_QUOTES),
                    htmlspecialchars((string) ($row['city_name'] ?? '-'), ENT_QUOTES),
                    '<div class="flat-cluster-name"><a href="' . base_url('Checklist_Dokument_MyRep/detail/' . $idCluster) . '" class="cluster-name-link">'
                        . htmlspecialchars((string) ($row['cluster_name'] ?? '-'), ENT_QUOTES) . '</a></div>',
                    htmlspecialchars((string) ($row['scope_type'] ?? '-'), ENT_QUOTES),
                    htmlspecialchars((string) ($row['sow_type'] ?? '-'), ENT_QUOTES),
                    '<strong>' . htmlspecialchars((string) ($row['doc_name'] ?? '-'), ENT_QUOTES) . '</strong>',
                    htmlspecialchars((string) ($row['verification_by'] ?? '-'), ENT_QUOTES),
                    '<span class="badge badge-' . $internalBadge . '">' . htmlspecialchars($internalStatusLabel, ENT_QUOTES) . '</span>',
                    !empty($row['remark']) ? nl2br(htmlspecialchars((string) $row['remark'], ENT_QUOTES)) : '-',
                    '<span class="badge badge-' . $astriBadge . '">' . htmlspecialchars($astriStatusLabel, ENT_QUOTES) . '</span>',
                    !empty($row['astri_remark']) ? nl2br(htmlspecialchars((string) $row['astri_remark'], ENT_QUOTES)) : '-',
                    $this->formatDateDisplay($row['uploaded_at'] ?? null),
                    $this->formatDateDisplay($row['reviewed_at'] ?? null),
                    $this->formatDateDisplay($row['approved_at'] ?? null),
                    $this->formatDateDisplay($row['astri_submitted_date'] ?? null),
                    '<a href="' . base_url('Checklist_Dokument_MyRep/detail/' . $idCluster) . '" class="btn btn-primary btn-sm">Detail</a> '
                    . '<form method="post" action="' . base_url('Checklist_Dokument_MyRep/deleteCluster') . '" class="d-inline" onsubmit="return confirm(\'Hapus cluster ini dari ATP/RFS beserta seluruh flow MyRep sebelumnya?\');">'
                    . '<input type="hidden" name="cluster_id" value="' . $idCluster . '">'
                    . '<button type="submit" class="btn btn-danger btn-sm">Hapus</button>'
                    . '</form>',
                ];
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'draw' => (int) $this->input->post('draw'),
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $data,
                ]));
        } catch (\Throwable $e) {
            log_message('error', 'Checklist itemTableData failed: ' . $e->getMessage());
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'draw' => (int) $this->input->post('draw'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]));
        }
    }

    public function exportItemExcel()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('selected_city')));
        $selectedRegional = strtoupper(trim((string) $this->input->get('selected_regional')));
        $cacheKey = 'checklist_doc_index_' . md5($selectedCity . '|' . $selectedRegional);
        $cachedPayload = $this->getChecklistCache($cacheKey);
        if (!is_array($cachedPayload)) {
            $clusterList = $this->MChecklist_Dokument_MyRep->getFullRfsClusters($selectedCity, $selectedRegional);
            $documentItemList = $this->MChecklist_Dokument_MyRep->getClusterDocumentItemRows($selectedCity, $selectedRegional);
            $cachedPayload = [
                'clusterList' => $clusterList,
                'documentItemList' => $documentItemList,
                'dashboardSummary' => $this->buildDashboardSummary($clusterList, $documentItemList),
            ];
            $this->saveChecklistCache($cacheKey, $cachedPayload, 300);
        }

        $rows = isset($cachedPayload['documentItemList']) && is_array($cachedPayload['documentItemList'])
            ? $cachedPayload['documentItemList']
            : [];

        $itemRegional = strtoupper(trim((string) $this->input->get('item_regional')));
        $itemCity = strtoupper(trim((string) $this->input->get('item_city')));
        $itemCluster = strtoupper(trim((string) $this->input->get('item_cluster')));
        $itemScope = strtoupper(trim((string) $this->input->get('item_scope')));
        $itemSow = strtoupper(trim((string) $this->input->get('item_sow')));
        $itemDoc = strtoupper(trim((string) $this->input->get('item_doc')));
        $internalStatus = strtoupper(trim((string) $this->input->get('internal_status')));
        $astriStatus = strtoupper(trim((string) $this->input->get('astri_status')));
        $quickType = strtolower(trim((string) $this->input->get('quick_type')));
        $quickValue = strtoupper(trim((string) $this->input->get('quick_value')));
        $searchValue = strtoupper(trim((string) $this->input->get('search')));

        $exportRows = [];
        foreach ($rows as $row) {
            $rowRegional = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            $rowCity = strtoupper(trim((string) ($row['city_name'] ?? '')));
            $rowCluster = strtoupper(trim((string) ($row['cluster_name'] ?? '')));
            $rowScope = strtoupper(trim((string) ($row['scope_type'] ?? '')));
            $rowSow = strtoupper(trim((string) ($row['sow_type'] ?? '')));
            $rowInternal = $this->normalizeUiStatusLabel((string) ($row['status_file'] ?? 'NOT UPLOADED'));
            $rowAstri = $this->normalizeUiStatusLabel((string) ($row['astri_status'] ?? 'NY'));
            $rowDoc = strtoupper(trim((string) ($row['doc_name'] ?? '')));

            if ($itemRegional !== '' && $rowRegional !== $itemRegional) continue;
            if ($itemCity !== '' && $rowCity !== $itemCity) continue;
            if ($itemCluster !== '' && $rowCluster !== $itemCluster) continue;
            if ($itemScope !== '' && $rowScope !== $itemScope) continue;
            if ($itemSow !== '' && $rowSow !== $itemSow) continue;
            if ($itemDoc !== '' && $rowDoc !== $itemDoc) continue;
            if ($internalStatus !== '' && $rowInternal !== $internalStatus) continue;
            if ($astriStatus !== '' && $rowAstri !== $astriStatus) continue;

            if ($quickType === 'project-opname' && !($rowDoc === 'PROJECT OPNAME' && $rowAstri === $quickValue)) continue;
            if ($quickType === 'astri' && $quickValue === 'ON REVIEW' && $rowAstri !== 'ON REVIEW') continue;

            if ($searchValue !== '') {
                $haystack = strtoupper(implode(' ', [
                    (string) ($row['regional_name'] ?? ''),
                    (string) ($row['city_name'] ?? ''),
                    (string) ($row['cluster_name'] ?? ''),
                    (string) ($row['scope_type'] ?? ''),
                    (string) ($row['sow_type'] ?? ''),
                    (string) ($row['doc_name'] ?? ''),
                    (string) ($row['verification_by'] ?? ''),
                    $rowInternal,
                    (string) ($row['remark'] ?? ''),
                    $rowAstri,
                    (string) ($row['astri_remark'] ?? ''),
                ]));
                if (strpos($haystack, $searchValue) === false) continue;
            }
            $exportRows[] = $row;
        }

        $excel = $this->createPHPExcelObject();
        $this->populateChecklistItemDataSheet($excel->getActiveSheet(), $exportRows);

        $pivotSheet = $excel->createSheet();
        $this->populateChecklistItemPivotSheet($pivotSheet, $exportRows);

        $excel->setActiveSheetIndex(0);
        $this->outputChecklistItemWorkbook($excel, 'monitoring_item_dokumen_' . date('Y-m-d') . '.xls');
    }

    private function createPHPExcelObject()
    {
        if (!class_exists('PHPExcel')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        }

        return new PHPExcel();
    }

    private function outputChecklistItemWorkbook($excel, $filename)
    {
        if (!class_exists('PHPExcel_IOFactory')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel/IOFactory.php';
        }

        if (function_exists('ob_get_level')) {
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer->save('php://output');
        exit;
    }

    private function populateChecklistItemDataSheet($sheet, array $rows)
    {
        $sheet->setTitle('Data Item');
        $headers = [
            'No',
            'Regional',
            'Kota',
            'Cluster',
            'Scope',
            'SOW',
            'Dokumen',
            'Verification By',
            'Status Internal',
            'Remark Internal',
            'Status Astri',
            'Remark Astri',
            'Uploaded At',
            'Reviewed At',
            'Approved At',
            'Submit Astri',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index, 1, $header);
        }

        $rowNo = 2;
        $no = 1;
        foreach ($rows as $row) {
            $values = [
                $no++,
                (string) ($row['regional_name'] ?? '-'),
                (string) ($row['city_name'] ?? '-'),
                (string) ($row['cluster_name'] ?? '-'),
                (string) ($row['scope_type'] ?? '-'),
                (string) ($row['sow_type'] ?? '-'),
                (string) ($row['doc_name'] ?? '-'),
                (string) ($row['verification_by'] ?? '-'),
                $this->statusLabel((string) ($row['status_file'] ?? 'NOT UPLOADED')),
                (string) ($row['remark'] ?? '-'),
                $this->statusLabel((string) ($row['astri_status'] ?? 'NY')),
                (string) ($row['astri_remark'] ?? '-'),
                $this->formatDateDisplay($row['uploaded_at'] ?? null),
                $this->formatDateDisplay($row['reviewed_at'] ?? null),
                $this->formatDateDisplay($row['approved_at'] ?? null),
                $this->formatDateDisplay($row['astri_submitted_date'] ?? null),
            ];

            foreach ($values as $index => $value) {
                $sheet->setCellValueByColumnAndRow($index, $rowNo, $value);
            }
            $rowNo++;
        }

        $lastColumn = PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($this->checklistExcelHeaderStyle());
        $sheet->getStyle('A1:' . $lastColumn . max(1, $rowNo - 1))->applyFromArray($this->checklistExcelBorderStyle());
        $sheet->setAutoFilter('A1:' . $lastColumn . max(1, $rowNo - 1));
        $sheet->freezePane('A2');

        for ($col = 0; $col < count($headers); $col++) {
            $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }

    private function populateChecklistItemPivotSheet($sheet, array $rows)
    {
        $sheet->setTitle('Pivot Status');
        $pivot = $this->buildChecklistItemPivotData($rows);
        $statusesBySow = $pivot['statusesBySow'];

        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->mergeCells('D1:D2');
        $sheet->setCellValue('A1', 'Regional');
        $sheet->setCellValue('B1', 'Kota');
        $sheet->setCellValue('C1', 'Cluster');
        $sheet->setCellValue('D1', 'Scope');

        $col = 4;
        foreach ($statusesBySow as $sow => $statuses) {
            $startCol = $col;
            foreach ($statuses as $status) {
                $sheet->setCellValueByColumnAndRow($col, 2, $status);
                $col++;
            }
            $endCol = $col - 1;
            $sheet->mergeCellsByColumnAndRow($startCol, 1, $endCol, 1);
            $sheet->setCellValueByColumnAndRow($startCol, 1, $sow);
        }

        $grandTotalCol = $col;
        $sheet->mergeCellsByColumnAndRow($grandTotalCol, 1, $grandTotalCol, 2);
        $sheet->setCellValueByColumnAndRow($grandTotalCol, 1, 'Grand Total');

        $rowNo = 3;
        foreach ($pivot['rows'] as $pivotRow) {
            $sheet->setCellValueByColumnAndRow(0, $rowNo, $pivotRow['regional']);
            $sheet->setCellValueByColumnAndRow(1, $rowNo, $pivotRow['city']);
            $sheet->setCellValueByColumnAndRow(2, $rowNo, $pivotRow['cluster']);
            $sheet->setCellValueByColumnAndRow(3, $rowNo, $pivotRow['scope']);

            $rowTotal = 0;
            $col = 4;
            foreach ($statusesBySow as $sow => $statuses) {
                foreach ($statuses as $status) {
                    $value = (int) ($pivotRow['counts'][$sow][$status] ?? 0);
                    if ($value > 0) {
                        $sheet->setCellValueByColumnAndRow($col, $rowNo, $value);
                    }
                    $rowTotal += $value;
                    $col++;
                }
            }
            $sheet->setCellValueByColumnAndRow($grandTotalCol, $rowNo, $rowTotal);
            $rowNo++;
        }

        $sheet->mergeCellsByColumnAndRow(0, $rowNo, 3, $rowNo);
        $sheet->setCellValueByColumnAndRow(0, $rowNo, 'Grand Total');
        $col = 4;
        $grandTotal = 0;
        foreach ($statusesBySow as $sow => $statuses) {
            foreach ($statuses as $status) {
                $value = (int) ($pivot['totals'][$sow][$status] ?? 0);
                $sheet->setCellValueByColumnAndRow($col, $rowNo, $value);
                $grandTotal += $value;
                $col++;
            }
        }
        $sheet->setCellValueByColumnAndRow($grandTotalCol, $rowNo, $grandTotal);

        $lastColumn = PHPExcel_Cell::stringFromColumnIndex($grandTotalCol);
        $lastRow = max(2, $rowNo);
        $sheet->getStyle('A1:' . $lastColumn . '2')->applyFromArray($this->checklistExcelHeaderStyle());
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray($this->checklistExcelBorderStyle());
        $sheet->getStyle('A' . $lastRow . ':' . $lastColumn . $lastRow)->applyFromArray($this->checklistExcelTotalStyle());
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('E3:' . $lastColumn . $lastRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->freezePane('E3');

        for ($dimensionCol = 0; $dimensionCol <= $grandTotalCol; $dimensionCol++) {
            $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($dimensionCol))->setAutoSize(true);
        }
    }

    private function buildChecklistItemPivotData(array $rows)
    {
        $preferredSowOrder = ['CW ATP', 'FULL OPM', 'RFS'];
        $preferredStatusOrder = ['APPROVED', 'NOT UPLOADED', 'ON REVIEW', 'REJECTED'];
        $pivotRows = [];
        $statusesBySow = [];
        $totals = [];

        foreach ($rows as $row) {
            $regional = trim((string) ($row['regional_name'] ?? '-'));
            $city = trim((string) ($row['city_name'] ?? '-'));
            $cluster = trim((string) ($row['cluster_name'] ?? '-'));
            $scope = trim((string) ($row['scope_type'] ?? '-'));
            $sow = strtoupper(trim((string) ($row['sow_type'] ?? '-')));
            $sow = $sow !== '' ? $sow : '-';
            $status = $this->normalizeUiStatusLabel((string) ($row['status_file'] ?? 'NOT UPLOADED'));
            $key = implode("\t", [$regional, $city, $cluster, $scope]);

            if (!isset($pivotRows[$key])) {
                $pivotRows[$key] = [
                    'regional' => $regional !== '' ? $regional : '-',
                    'city' => $city !== '' ? $city : '-',
                    'cluster' => $cluster !== '' ? $cluster : '-',
                    'scope' => $scope !== '' ? $scope : '-',
                    'counts' => [],
                ];
            }

            if (!isset($statusesBySow[$sow])) {
                $statusesBySow[$sow] = [];
            }
            $statusesBySow[$sow][$status] = true;
            $pivotRows[$key]['counts'][$sow][$status] = (int) ($pivotRows[$key]['counts'][$sow][$status] ?? 0) + 1;
            $totals[$sow][$status] = (int) ($totals[$sow][$status] ?? 0) + 1;
        }

        if (empty($statusesBySow)) {
            $statusesBySow['-'] = ['NOT UPLOADED' => true];
        }

        uksort($statusesBySow, static function ($left, $right) use ($preferredSowOrder) {
            $leftPos = array_search($left, $preferredSowOrder, true);
            $rightPos = array_search($right, $preferredSowOrder, true);
            $leftPos = $leftPos === false ? 999 : $leftPos;
            $rightPos = $rightPos === false ? 999 : $rightPos;
            return $leftPos === $rightPos ? strcmp($left, $right) : ($leftPos - $rightPos);
        });

        foreach ($statusesBySow as $sow => $statusMap) {
            $statuses = array_keys($statusMap);
            usort($statuses, static function ($left, $right) use ($preferredStatusOrder) {
                $leftPos = array_search($left, $preferredStatusOrder, true);
                $rightPos = array_search($right, $preferredStatusOrder, true);
                $leftPos = $leftPos === false ? 999 : $leftPos;
                $rightPos = $rightPos === false ? 999 : $rightPos;
                return $leftPos === $rightPos ? strcmp($left, $right) : ($leftPos - $rightPos);
            });
            $statusesBySow[$sow] = $statuses;
        }

        return [
            'rows' => array_values($pivotRows),
            'statusesBySow' => $statusesBySow,
            'totals' => $totals,
        ];
    }

    private function checklistExcelHeaderStyle()
    {
        return [
            'font' => ['bold' => true],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9E2F3'],
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
        ];
    }

    private function checklistExcelTotalStyle()
    {
        return [
            'font' => ['bold' => true],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'E2E8F4'],
            ],
        ];
    }

    private function checklistExcelBorderStyle()
    {
        return [
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
    }

    private function buildClusterTableRow(array $cluster, $no, $canHapus)
    {
        $idCluster = (int) ($cluster['id_cluster'] ?? 0);
        $cwCard = $this->buildClusterDocCard('CW ATP', 'Realisasi', $cluster, 'doc_cw_atp_uploaded', 'doc_cw_atp_required', 'doc_cw_atp');
        $opmCard = $this->buildClusterDocCard('FULL OPM', 'Realisasi', $cluster, 'doc_full_opm_uploaded', 'doc_full_opm_required', 'doc_full_opm');
        $rfsCard = $this->buildClusterDocCard('FULL RFS', 'Realisasi', $cluster, 'doc_rfs_uploaded', 'doc_rfs_required', 'doc_rfs');
        $astriCwCard = $this->buildClusterDocCard('ASTRI CW ATP', 'Submit', $cluster, 'astri_doc_cw_atp_submitted', 'doc_cw_atp_required', 'astri_doc_cw_atp');
        $astriOpmCard = $this->buildClusterDocCard('ASTRI FULL OPM', 'Submit', $cluster, 'astri_doc_full_opm_submitted', 'doc_full_opm_required', 'astri_doc_full_opm');
        $astriRfsCard = $this->buildClusterDocCard('ASTRI FULL RFS', 'Submit', $cluster, 'astri_doc_rfs_submitted', 'doc_rfs_required', 'astri_doc_rfs');

        return [
            (int) $no,
            $this->html($cluster['regional_name'] ?? '-'),
            $this->buildClusterIdentityHtml($cluster),
            $this->buildClusterTimelineHtml('Plan ATP', $cluster['plan_atp_date'] ?? null, $cluster['aging_atp_days'] ?? null, 'Realisasi ATP', $cluster['actual_atp_date'] ?? null),
            $this->buildClusterTimelineHtml('Plan Dokument', $cluster['plan_submit_doc_date'] ?? null, $cluster['aging_doc_days'] ?? null, 'Realisasi Dokument', $cluster['actual_submit_doc_date'] ?? null),
            $cwCard,
            $opmCard,
            $rfsCard,
            $this->buildClusterAstriTimelineHtml($cluster),
            $astriCwCard,
            $astriOpmCard,
            $astriRfsCard,
            $this->buildClusterActionHtml($idCluster, $canHapus),
        ];
    }

    private function buildClusterIdentityHtml(array $cluster)
    {
        $idCluster = (int) ($cluster['id_cluster'] ?? 0);
        $homepass = number_format((float) ($cluster['homepass'] ?? 0), 0, ',', '.');

        return '<div class="cluster-identity">'
            . '<div class="cluster-name"><a href="' . base_url('Checklist_Dokument_MyRep/detail/' . $idCluster) . '" class="cluster-name-link">'
            . $this->html($cluster['cluster_name'] ?? '-') . '</a></div>'
            . '<div class="cluster-meta">'
            . '<span class="cluster-chip">' . $this->html($cluster['city_name'] ?? '-') . '</span>'
            . '<span class="cluster-chip">HP ' . $this->html($homepass) . '</span>'
            . '<span class="cluster-chip">RFS ' . $this->html($this->formatDateDisplay($cluster['tanggal_rfs'] ?? null)) . '</span>'
            . '</div></div>';
    }

    private function buildClusterTimelineHtml($planLabel, $planDate, $agingDays, $actualLabel, $actualDate)
    {
        $agingHtml = '<span class="badge badge-secondary">Aging -</span>';
        if ($agingDays !== null) {
            $agingHtml = '<span class="badge badge-' . $this->clusterAgingBadge($agingDays) . '">Aging ' . (int) $agingDays . ' hari</span>';
        }

        return '<div class="timeline-stack">'
            . '<div class="timeline-item">'
            . '<span class="timeline-label">' . $this->html($planLabel) . '</span>'
            . '<div class="timeline-value">' . $this->html($this->formatDateDisplay($planDate)) . '</div>'
            . $agingHtml
            . '</div>'
            . '<div class="timeline-item">'
            . '<span class="timeline-label">' . $this->html($actualLabel) . '</span>'
            . '<div class="timeline-value">' . $this->html($this->formatDateDisplay($actualDate)) . '</div>'
            . '</div>'
            . '</div>';
    }

    private function buildClusterAstriTimelineHtml(array $cluster)
    {
        $agingDays = null;
        if (!empty($cluster['submit_astri_date'])) {
            $start = new DateTime((string) $cluster['submit_astri_date']);
            $end = new DateTime(!empty($cluster['approved_astri_date']) ? (string) $cluster['approved_astri_date'] : date('Y-m-d'));
            $invert = $start > $end ? -1 : 1;
            $diff = $start->diff($end);
            $agingDays = $diff->days * $invert;
        }

        return '<div class="sla-separator-left"><div class="timeline-stack">'
            . '<div class="timeline-item">'
            . '<span class="timeline-label">Submit Astri</span>'
            . '<div class="timeline-value">' . $this->html($this->formatDateDisplay($cluster['submit_astri_date'] ?? null)) . '</div>'
            . '</div>'
            . '<div class="timeline-item">'
            . '<span class="timeline-label">Approved Astri</span>'
            . '<div class="timeline-value">' . $this->html($this->formatDateDisplay($cluster['approved_astri_date'] ?? null)) . '</div>'
            . ($agingDays === null
                ? '<span class="badge badge-secondary">Aging -</span>'
                : '<span class="badge badge-' . $this->clusterAgingBadge($agingDays) . '">Aging ' . (int) $agingDays . ' hari</span>')
            . '</div>'
            . '</div></div>';
    }

    private function buildClusterDocCard($title, $verb, array $cluster, $doneKey, $requiredKey, $prefix)
    {
        $done = (int) ($cluster[$doneKey] ?? 0);
        $required = (int) ($cluster[$requiredKey] ?? 0);
        $percent = $this->clusterProgressPercent($done, $required);
        $theme = $this->clusterProgressTheme($done, $required);

        return '<div class="doc-card ' . $theme['box'] . '">'
            . '<div class="doc-card-head"><div>'
            . '<div class="doc-card-title">' . $this->html($title) . '</div>'
            . '<div class="doc-card-subtitle">' . $this->html($verb) . ' ' . $done . '/' . $required . '</div>'
            . '</div><div class="doc-card-progress ' . $theme['tone'] . '">' . $percent . '%</div></div>'
            . '<div class="doc-progress-track"><div class="doc-progress-bar ' . $theme['bar'] . '" style="width: ' . $percent . '%;"></div></div>'
            . '<div class="doc-status-grid">'
            . $this->buildClusterDocStatusItem('NY', (int) ($cluster[$prefix . '_ny'] ?? 0))
            . $this->buildClusterDocStatusItem('On Review', (int) ($cluster[$prefix . '_on_review'] ?? 0))
            . $this->buildClusterDocStatusItem('Reject', (int) ($cluster[$prefix . '_rejected'] ?? 0))
            . $this->buildClusterDocStatusItem('Approved', (int) ($cluster[$prefix . '_approved'] ?? 0))
            . '</div></div>';
    }

    private function buildClusterDocStatusItem($label, $value)
    {
        return '<div class="doc-status-item">'
            . '<span class="doc-status-label">' . $this->html($label) . '</span>'
            . '<span class="doc-status-value">' . (int) $value . '</span>'
            . '</div>';
    }

    private function buildClusterActionHtml($idCluster, $canHapus)
    {
        $html = '<div class="action-stack">'
            . '<a href="' . base_url('Checklist_Dokument_MyRep/detail/' . (int) $idCluster) . '" class="btn btn-primary btn-sm">Detail</a>';

        if ($canHapus) {
            $html .= '<form method="post" action="' . base_url('Checklist_Dokument_MyRep/deleteCluster') . '" class="d-inline" onsubmit="return confirm(\'Hapus cluster ini dari ATP/RFS beserta seluruh flow MyRep sebelumnya?\');">'
                . '<input type="hidden" name="cluster_id" value="' . (int) $idCluster . '">'
                . '<button type="submit" class="btn btn-danger btn-sm">Hapus</button>'
                . '</form>';
        }

        return $html . '</div>';
    }

    private function clusterProgressPercent($uploaded, $required)
    {
        $required = (int) $required;
        $uploaded = (int) $uploaded;
        if ($required <= 0) {
            return 0;
        }

        return min(100, (int) round(($uploaded / $required) * 100));
    }

    private function clusterProgressTheme($uploaded, $required)
    {
        $required = (int) $required;
        $uploaded = (int) $uploaded;
        if ($required <= 0 || $uploaded <= 0) {
            return ['box' => 'bg-light', 'bar' => 'bg-secondary', 'tone' => 'text-muted'];
        }
        if ($uploaded >= $required) {
            return ['box' => 'bg-success-light', 'bar' => 'bg-success-strong', 'tone' => 'text-success-dark'];
        }

        return ['box' => 'bg-warning-light', 'bar' => 'bg-warning', 'tone' => 'text-warning-dark'];
    }

    private function clusterAgingBadge($aging)
    {
        if ($aging === null) {
            return 'secondary';
        }
        if ((int) $aging <= 0) {
            return 'success';
        }
        if ((int) $aging <= 3) {
            return 'warning';
        }

        return 'danger';
    }

    private function html($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES);
    }

    private function formatDateDisplay($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }
        return date('d/m/Y', strtotime((string) $date));
    }

    private function statusLabel($status)
    {
        $status = strtoupper(trim((string) $status));
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }
        if ($status === 'NY' || $status === '') {
            return 'NOT UPLOADED';
        }
        return $status;
    }

    private function normalizeUiStatusLabel($status)
    {
        return strtoupper(trim($this->statusLabel($status)));
    }

    private function statusBadge($status)
    {
        $status = strtoupper(trim((string) $status));
        switch ($status) {
            case 'APPROVED':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'UPLOADED':
            case 'WAITING WASPANG':
            case 'WAITING PLANNING':
            case 'WAITING TL':
            case 'WAITING LOGISTIK':
                return 'warning';
            case 'NOT UPLOADED':
            case 'NY':
                return 'secondary';
            default:
                return 'info';
        }
    }

    private function getChecklistCache($cacheKey)
    {
        $path = APPPATH . 'cache/' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $cacheKey) . '.json';
        if (!is_file($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || empty($decoded['expires_at']) || !isset($decoded['payload'])) {
            return null;
        }

        if ((int) $decoded['expires_at'] < time()) {
            @unlink($path);
            return null;
        }

        return is_array($decoded['payload']) ? $decoded['payload'] : null;
    }

    private function saveChecklistCache($cacheKey, array $payload, $ttlSeconds = 300)
    {
        $path = APPPATH . 'cache/' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $cacheKey) . '.json';
        $data = [
            'expires_at' => time() + max(60, (int) $ttlSeconds),
            'payload' => $payload,
        ];
        @file_put_contents($path, json_encode($data));
    }

    private function buildDashboardSummary(array $clusterList, array $documentItemList)
    {
        $summary = [
            'totalCluster' => count($clusterList),
            'clusterDoneRfsBelumAtp' => 0,
            'clusterDoneAtpBelumDokument' => 0,
            'clusterNyAstri' => 0,
            'internalStatusSummary' => [
                'NY' => 0,
                'ON REVIEW' => 0,
                'REJECTED' => 0,
                'APPROVED' => 0,
            ],
            'astriStatusSummary' => [
                'NY' => 0,
                'ON REVIEW' => 0,
                'REJECTED' => 0,
                'APPROVED' => 0,
            ],
            'projectOpnameFlowSummary' => [
                'WAITING WASPANG' => 0,
                'WAITING PLANNING' => 0,
                'WAITING TL' => 0,
                'WAITING LOGISTIK' => 0,
            ],
        ];

        foreach ($clusterList as $cluster) {
            if (!empty($cluster['tanggal_rfs']) && empty($cluster['actual_atp_date'])) {
                $summary['clusterDoneRfsBelumAtp']++;
            }
            if (!empty($cluster['actual_atp_date']) && empty($cluster['actual_submit_doc_date'])) {
                $summary['clusterDoneAtpBelumDokument']++;
            }
            if (empty($cluster['approved_astri_date'])) {
                $summary['clusterNyAstri']++;
            }
        }

        foreach ($documentItemList as $item) {
            $internalStatus = strtoupper(trim((string) ($item['status_file'] ?? 'NOT UPLOADED')));
            if ($internalStatus === '' || $internalStatus === 'NOT UPLOADED' || $internalStatus === 'NY') {
                $summary['internalStatusSummary']['NY']++;
            } elseif ($internalStatus === 'UPLOADED' || in_array($internalStatus, ['WAITING WASPANG', 'WAITING PLANNING', 'WAITING TL', 'WAITING LOGISTIK'], true)) {
                $summary['internalStatusSummary']['ON REVIEW']++;
            } elseif (isset($summary['internalStatusSummary'][$internalStatus])) {
                $summary['internalStatusSummary'][$internalStatus]++;
            }

            $astriStatus = strtoupper(trim((string) ($item['astri_status'] ?? 'NY')));
            if ($astriStatus === '' || $astriStatus === 'NOT UPLOADED' || $astriStatus === 'NY') {
                $summary['astriStatusSummary']['NY']++;
            } elseif ($astriStatus === 'UPLOADED' || $astriStatus === 'ON REVIEW') {
                $summary['astriStatusSummary']['ON REVIEW']++;
            } elseif (isset($summary['astriStatusSummary'][$astriStatus])) {
                $summary['astriStatusSummary'][$astriStatus]++;
            }

            if (
                strtoupper(trim((string) ($item['scope_type'] ?? ''))) === 'CLUSTER'
                && strtoupper(trim((string) ($item['sow_type'] ?? ''))) === 'RFS'
                && strtoupper(trim((string) ($item['doc_name'] ?? ''))) === 'PROJECT OPNAME'
                && isset($summary['projectOpnameFlowSummary'][$astriStatus])
            ) {
                $summary['projectOpnameFlowSummary'][$astriStatus]++;
            }
        }

        return $summary;
    }

    private function buildItemFilterOptions(array $documentItemList)
    {
        $options = [];
        $seen = [];

        foreach ($documentItemList as $item) {
            $row = [
                'regional' => strtoupper(trim((string) ($item['regional_name'] ?? ''))),
                'city' => strtoupper(trim((string) ($item['city_name'] ?? ''))),
                'cluster' => strtoupper(trim((string) ($item['cluster_name'] ?? ''))),
                'scope' => strtoupper(trim((string) ($item['scope_type'] ?? ''))),
                'sow' => strtoupper(trim((string) ($item['sow_type'] ?? ''))),
                'doc' => strtoupper(trim((string) ($item['doc_name'] ?? ''))),
            ];

            $key = implode('|', $row);
            if ($key === '|||||' || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $options[] = $row;
        }

        usort($options, static function ($a, $b) {
            return strcmp(
                implode('|', [$a['regional'], $a['city'], $a['cluster'], $a['scope'], $a['sow'], $a['doc']]),
                implode('|', [$b['regional'], $b['city'], $b['cluster'], $b['scope'], $b['sow'], $b['doc']])
            );
        });

        return $options;
    }

    public function detail($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $this->MMonitoring_RFS_MyRep->syncMyrepCompatibilityBridge((int) date('Y'), (int) date('n'));

        $cluster = $this->MChecklist_Dokument_MyRep->getClusterDetail($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data cluster tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $this->MChecklist_Dokument_MyRep->ensureClusterPackages($clusterId, $cluster['tanggal_rfs'] ?? null);
        $cluster = $this->MChecklist_Dokument_MyRep->getClusterDetail($clusterId);

        $data['title'] = 'Checklist Dokument Detail';
        $data['cluster'] = $cluster;
        $data['scopeTabs'] = $this->MChecklist_Dokument_MyRep->getClusterScopeTabs($clusterId, false);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Checklist_Dokument_MyRep/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function documentHistoryData($fileId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Session habis. Silakan login ulang.',
                    'history' => [],
                ]));
            return;
        }

        $fileId = (int) $fileId;
        if ($fileId <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Dokumen tidak valid.',
                    'history' => [],
                ]));
            return;
        }

        $file = $this->MChecklist_Dokument_MyRep->getFileById($fileId);
        if (empty($file)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Dokumen tidak ditemukan.',
                    'history' => [],
                ]));
            return;
        }

        $historyByFileId = $this->MChecklist_Dokument_MyRep->getFileLogsByFileIds([$fileId]);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'history' => isset($historyByFileId[$fileId]) && is_array($historyByFileId[$fileId])
                    ? $historyByFileId[$fileId]
                    : [],
            ]));
    }

    public function mainfeeder()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedRegional = strtoupper(trim((string) $this->input->get('regional')));

        $data['title'] = 'Checklist Dokument Mainfeeder';
        $data['selectedCity'] = $selectedCity;
        $data['selectedRegional'] = $selectedRegional;
        $data['cityOptions'] = $this->MChecklist_Dokument_MyRep->getCityOptions();
        $data['regionalOptions'] = $this->MChecklist_Dokument_MyRep->getRegionalOptions();
        $data['targetOptions'] = $this->MChecklist_Dokument_MyRep->getTargetOptions($selectedCity, $selectedRegional);
        $data['mainfeederList'] = $this->MChecklist_Dokument_MyRep->getMainfeederList($selectedCity, $selectedRegional);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Checklist_Dokument_MyRep/mainfeeder_index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveMainfeeder()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idTarget = (int) $this->input->post('id_target');
        $mainfeederName = trim((string) $this->input->post('mainfeeder_name'));
        $lengthMeter = (float) $this->input->post('length_meter');
        $atpDate = $this->normalizeDateInput($this->input->post('atp_date'));

        if ($idTarget <= 0 || $mainfeederName === '' || !$atpDate) {
            $this->session->set_flashdata('error', 'Data mainfeeder wajib diisi lengkap.');
            redirect('Checklist_Dokument_MyRep/mainfeeder');
            return;
        }

        $this->MChecklist_Dokument_MyRep->saveMainfeeder([
            'id_target' => $idTarget,
            'mainfeeder_name' => $mainfeederName,
            'length_meter' => $lengthMeter,
            'atp_date' => $atpDate,
            'created_by' => (int) $this->session->userdata('id_user'),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata('success', 'Mainfeeder berhasil ditambahkan.');
        redirect('Checklist_Dokument_MyRep/mainfeeder');
    }

    public function detailMainfeeder($mainfeederId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $mainfeederId = (int) $mainfeederId;
        if ($mainfeederId <= 0) {
            redirect('Checklist_Dokument_MyRep/mainfeeder');
            return;
        }

        $mainfeeder = $this->MChecklist_Dokument_MyRep->getMainfeederDetail($mainfeederId);
        if (empty($mainfeeder)) {
            $this->session->set_flashdata('error', 'Data mainfeeder tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep/mainfeeder');
            return;
        }

        $this->MChecklist_Dokument_MyRep->ensureMainfeederPackages($mainfeederId, $mainfeeder['atp_date'] ?? null);
        $mainfeeder = $this->MChecklist_Dokument_MyRep->getMainfeederDetail($mainfeederId);

        $data['title'] = 'Detail Mainfeeder';
        $data['mainfeeder'] = $mainfeeder;
        $data['groupRows'] = $this->MChecklist_Dokument_MyRep->getMainfeederGroupRows($mainfeederId);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Checklist_Dokument_MyRep/mainfeeder_detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function uploadMainfeederDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(false, 'Session habis. Silakan login ulang.');
                return;
            }
            redirect('Auth');
            return;
        }

        $mainfeederId = (int) $this->input->post('mainfeeder_id');
        $packageId = (int) $this->input->post('id_doc_package_mainfeeder');
        $itemId = (int) $this->input->post('id_doc_item_mainfeeder');
        $docName = trim((string) $this->input->post('doc_name'));
        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;

        if ($mainfeederId <= 0 || $packageId <= 0 || $itemId <= 0) {
            $this->handleUploadError('Data upload mainfeeder belum lengkap.', 'Checklist_Dokument_MyRep/mainfeeder');
            return;
        }

        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->handleUploadError('File wajib dipilih jika dokumen dibutuhkan.', 'Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
            return;
        }

        $uploadDir = './uploads/checklist_myrep_mainfeeder/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = '';
        $filePath = '';
        if (!$isNoDocumentRequired) {
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName);
            $fileName = 'MF_' . $mainfeederId . '_' . $packageId . '_' . $itemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;
            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
                'max_size' => 102400,
                'file_name' => $fileName,
                'overwrite' => true,
            ];
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $this->handleUploadError(strip_tags($this->upload->display_errors()), 'Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
                return;
            }
            $fileData = $this->upload->data();
            $fileName = $fileData['file_name'];
            $filePath = 'uploads/checklist_myrep_mainfeeder/' . $fileData['file_name'];
        }

        $this->MChecklist_Dokument_MyRep->saveMainfeederFileUpload([
            'id_doc_package_mainfeeder' => $packageId,
            'id_doc_item_mainfeeder' => $itemId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
        ]);

        $this->notifyMainfeederDocumentSubmittedToHo($mainfeederId, [
            'package_id' => $packageId,
            'item_id' => $itemId,
            'doc_name' => $docName,
            'file_name' => $fileName,
            'remark' => trim((string) $this->input->post('remark')),
            'is_document_not_required' => $isNoDocumentRequired,
        ]);

        $this->handleUploadSuccess('Dokumen mainfeeder berhasil diupload.', 'Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
    }

    public function approveMainfeederDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }
        $mainfeederId = (int) $this->input->post('mainfeeder_id');
        $fileId = (int) $this->input->post('id_doc_file_mainfeeder');
        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen.');
            redirect('Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
            return;
        }
        $this->MChecklist_Dokument_MyRep->updateMainfeederFileStatus($fileId, [
            'status_file' => 'APPROVED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);
        $this->session->set_flashdata('success', 'Dokumen berhasil di-approve.');
        redirect('Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
    }

    public function rejectMainfeederDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }
        $mainfeederId = (int) $this->input->post('mainfeeder_id');
        $fileId = (int) $this->input->post('id_doc_file_mainfeeder');
        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen.');
            redirect('Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
            return;
        }
        $this->MChecklist_Dokument_MyRep->updateMainfeederFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);
        $this->session->set_flashdata('success', 'Dokumen berhasil di-reject.');
        redirect('Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
    }

    public function saveMainfeederAstriStatus()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $mainfeederId = (int) $this->input->post('mainfeeder_id');
        $fileId = (int) $this->input->post('id_doc_file_mainfeeder');
        $astriStatus = trim((string) $this->input->post('astri_status'));
        $astriSubmittedDate = $this->normalizeDateInput($this->input->post('astri_submitted_date'));

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses update status ASTRI.');
            redirect('Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
            return;
        }

        $file = $this->MChecklist_Dokument_MyRep->getMainfeederFileById($fileId);
        if (empty($file)) {
            $this->session->set_flashdata('error', 'Dokumen tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
            return;
        }

        if ($file['status_file'] !== 'APPROVED' && $astriStatus !== 'NY') {
            $this->session->set_flashdata('error', 'Dokumen internal harus APPROVED sebelum di-submit ke ASTRI.');
            redirect('Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
            return;
        }

        $this->MChecklist_Dokument_MyRep->updateMainfeederAstriStatus($fileId, [
            'astri_submitted_date' => $astriStatus === 'NY' ? null : $astriSubmittedDate,
            'astri_status' => $astriStatus,
            'astri_remark' => trim((string) $this->input->post('astri_remark')),
        ]);

        $this->session->set_flashdata('success', 'Status ASTRI berhasil diperbarui.');
        redirect('Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId);
    }

    public function previewMainfeederDocument($fileId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }
        $file = $this->MChecklist_Dokument_MyRep->getMainfeederFileById((int) $fileId);
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

    public function downloadMainfeederDocument($fileId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $file = $this->MChecklist_Dokument_MyRep->getMainfeederFileById((int) $fileId);
        $this->downloadStoredChecklistFile($file);
    }

    public function saveTimeline()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Data timeline tidak valid.');
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $payload = [
            'actual_atp_date' => $this->normalizeDateInput($this->input->post('actual_atp_date')),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ];

        $this->MChecklist_Dokument_MyRep->updateClusterTimeline($clusterId, $payload);
        $this->session->set_flashdata('success', 'Timeline berhasil diperbarui.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
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
        $packageId = (int) $this->input->post('id_doc_package');
        $itemId = (int) $this->input->post('id_doc_item');
        $docName = trim((string) $this->input->post('doc_name'));
        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;

        if ($clusterId <= 0 || $packageId <= 0 || $itemId <= 0) {
            $this->handleUploadError('Data upload dokumen belum lengkap.', 'Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->handleUploadError('File wajib dipilih jika dokumen dibutuhkan.', 'Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $uploadDir = './uploads/checklist_myrep/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = '';
        $filePath = '';

        if (!$isNoDocumentRequired) {
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName);
            $fileName = 'DOC_' . $clusterId . '_' . $packageId . '_' . $itemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => '*',
                'max_size' => 102400,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $this->upload->initialize($config);

            if (!$this->upload->do_upload('file')) {
                $this->handleUploadError(strip_tags($this->upload->display_errors()), 'Checklist_Dokument_MyRep/detail/' . $clusterId);
                return;
            }

            $fileData = $this->upload->data();
            $fileName = $fileData['file_name'];
            $filePath = 'uploads/checklist_myrep/' . $fileData['file_name'];
        }

        $payload = [
            'id_doc_package' => $packageId,
            'id_doc_item' => $itemId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
        ];

        $this->MChecklist_Dokument_MyRep->saveFileUpload($payload);
        $this->notifyClusterDocumentSubmittedToHo($clusterId, [
            'package_id' => $packageId,
            'item_id' => $itemId,
            'doc_name' => $docName,
            'file_name' => $fileName,
            'remark' => $payload['remark'],
            'is_document_not_required' => $isNoDocumentRequired,
        ]);
        $this->handleUploadSuccess(
            $isNoDocumentRequired ? 'Dokumen ditandai tidak dibutuhkan dan dikirim ke review.' : 'Dokumen berhasil diupload.',
            'Checklist_Dokument_MyRep/detail/' . $clusterId
        );
    }

    public function bulkUploadDocuments()
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
        $packageId = (int) $this->input->post('id_doc_package');
        $itemIds = $this->input->post('id_doc_item');
        $docNames = $this->input->post('doc_name');

        if ($clusterId <= 0 || $packageId <= 0 || !is_array($itemIds)) {
            $this->handleUploadError('Data bulk upload tidak valid.', 'Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $uploadDir = './uploads/checklist_myrep/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $successCount = 0;
        $uploadedDocNames = [];
        $uploadedFileNames = [];

        foreach ($itemIds as $index => $itemId) {
            $itemId = (int) $itemId;
            $docName = trim((string) ($docNames[$index] ?? ''));
            $inputName = 'bulk_file_' . $itemId;

            if ($itemId <= 0 || empty($_FILES[$inputName]['name'])) {
                continue;
            }

            $extension = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
            $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName);
            $fileName = 'DOC_' . $clusterId . '_' . $packageId . '_' . $itemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => '*',
                'max_size' => 102400,
                'file_name' => $fileName,
                'overwrite' => true,
            ];

            $_FILES['single_bulk_file'] = $_FILES[$inputName];
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('single_bulk_file')) {
                continue;
            }

            $fileData = $this->upload->data();
            $payload = [
                'id_doc_package' => $packageId,
                'id_doc_item' => $itemId,
                'file_name' => $fileData['file_name'],
                'file_path' => 'uploads/checklist_myrep/' . $fileData['file_name'],
                'status_file' => 'UPLOADED',
                'remark' => '',
                'uploaded_by' => (int) $this->session->userdata('id_user'),
            ];

            $this->MChecklist_Dokument_MyRep->saveFileUpload($payload);
            $uploadedDocNames[] = $docName !== '' ? $docName : ('Dokumen #' . $itemId);
            $uploadedFileNames[] = (string) $fileData['file_name'];
            $successCount++;
        }

        if ($successCount > 0) {
            $this->notifyClusterDocumentSubmittedToHo($clusterId, [
                'package_id' => $packageId,
                'item_id' => 0,
                'doc_name' => implode(', ', $uploadedDocNames),
                'file_name' => $successCount . ' file (' . implode(', ', $uploadedFileNames) . ')',
                'remark' => $successCount > 1
                    ? ($successCount . ' dokumen dikirim dalam satu kali submit dan menunggu review HO.')
                    : ('Dokumen ' . (isset($uploadedDocNames[0]) ? $uploadedDocNames[0] : '') . ' menunggu review HO.'),
                'is_document_not_required' => false,
                'notification_title' => $successCount > 1 ? 'FULL CLUSTER DOCUMENT' : 'NEW DOCUMENT',
            ]);
            $this->handleUploadSuccess($successCount . ' dokumen berhasil diupload.', 'Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $this->handleUploadError('Tidak ada file yang berhasil diupload.', 'Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function approveDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $clusterId = (int) $this->input->post('cluster_id');

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        if ($fileId <= 0 || $clusterId <= 0) {
            $this->session->set_flashdata('error', 'Dokumen tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $this->MChecklist_Dokument_MyRep->updateFileStatus($fileId, [
            'status_file' => 'APPROVED',
            'remark' => trim((string) $this->input->post('remark')),
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata('success', 'Dokumen berhasil di-approve.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function rejectDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $remark = trim((string) $this->input->post('remark'));
        if ($fileId <= 0) {
            $this->session->set_flashdata('error', 'Dokumen tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $this->MChecklist_Dokument_MyRep->updateFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => $remark,
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata('success', 'Dokumen berhasil di-reject.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function saveAstriStatus()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $fileId = (int) $this->input->post('id_doc_file');
        $astriStatus = trim((string) $this->input->post('astri_status'));
        $astriSubmittedDate = $this->normalizeDateInput($this->input->post('astri_submitted_date'));
        $astriRemark = trim((string) $this->input->post('astri_remark'));

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses update status ASTRI.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        if ($clusterId <= 0 || $fileId <= 0) {
            $this->session->set_flashdata('error', 'Data ASTRI tidak valid.');
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $allowedStatuses = [
            'NY',
            'ON REVIEW',
            'WAITING WASPANG',
            'WAITING PLANNING',
            'WAITING TL',
            'WAITING LOGISTIK',
            'REJECTED',
            'APPROVED'
        ];
        if (!in_array($astriStatus, $allowedStatuses, true)) {
            $this->session->set_flashdata('error', 'Status ASTRI tidak dikenali.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $file = $this->MChecklist_Dokument_MyRep->getFileById($fileId);
        if (empty($file)) {
            $this->session->set_flashdata('error', 'Dokumen tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $specialAstriStatuses = ['WAITING WASPANG', 'WAITING PLANNING', 'WAITING TL', 'WAITING LOGISTIK'];
        if (!(int) ($file['is_special_project_opname'] ?? 0) && in_array($astriStatus, $specialAstriStatuses, true)) {
            $this->session->set_flashdata('error', 'Status ASTRI khusus ini hanya berlaku untuk Project Opname.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        if ((int) ($file['is_special_project_opname'] ?? 0) && empty($file['cluster_actual_atp_date']) && $astriStatus !== 'NY') {
            $this->session->set_flashdata('error', 'Project Opname hanya bisa masuk flow approval ASTRI setelah ATP terisi.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        if ($file['status_file'] !== 'APPROVED' && $astriStatus !== 'NY') {
            $this->session->set_flashdata('error', 'Dokumen internal harus APPROVED sebelum di-submit ke ASTRI.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        if ($astriStatus !== 'NY' && empty($astriSubmittedDate)) {
            $this->session->set_flashdata('error', 'Tanggal submit ASTRI wajib diisi untuk status selain NY.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $this->MChecklist_Dokument_MyRep->updateAstriStatus($fileId, [
            'astri_submitted_date' => $astriStatus === 'NY' ? null : $astriSubmittedDate,
            'astri_status' => $astriStatus,
            'astri_remark' => $astriRemark,
        ]);

        $this->session->set_flashdata('success', 'Status ASTRI berhasil diperbarui.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function previewDocument($fileId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $file = $this->MChecklist_Dokument_MyRep->getFileById((int) $fileId);
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
        $mimeType = isset($mimeMap[$extension]) ? $mimeMap[$extension] : 'application/octet-stream';

        header('Content-Type: ' . $mimeType);
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

        $file = $this->MChecklist_Dokument_MyRep->getFileById((int) $fileId);
        $this->downloadStoredChecklistFile($file);
    }

    private function downloadStoredChecklistFile(array $file)
    {
        if (empty($file) || empty($file['file_path'])) {
            show_404();
            return;
        }

        $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file['file_path']);
        if (!is_file($fullPath)) {
            show_404();
            return;
        }

        $downloadName = trim((string) ($file['file_name'] ?? ''));
        if ($downloadName === '') {
            $downloadName = basename($fullPath);
        }

        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    public function previewKesepakatanPdf($clusterId = 0)
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

        $cluster = $this->MChecklist_Dokument_MyRep->getClusterDetail($clusterId);
        if (empty($cluster) || empty($cluster['id_myrep_cluster'])) {
            show_404();
            return;
        }

        $sourceDocs = $this->MChecklist_Dokument_MyRep->getKesepakatanSourceDocuments((int) $cluster['id_myrep_cluster']);
        if (empty($sourceDocs) || count($sourceDocs) < 4) {
            $this->session->set_flashdata('error', 'Dokumen sumber Berita Acara Kesepakatan belum lengkap di Batch Approval/Post Donasi.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $autoload = $this->resolveVendorAutoloadPath();
        if ($autoload === '') {
            show_error('Autoload vendor tidak ditemukan untuk proses merge PDF.', 500);
            return;
        }
        require_once $autoload;

        if (!class_exists('\setasign\Fpdi\Tcpdf\Fpdi')) {
            show_error('Library FPDI tidak ditemukan. Pastikan dependency Composer terpasang.', 500);
            return;
        }

        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('DatabaseTKM');
        $pdf->SetAuthor('DatabaseTKM');
        $pdf->SetTitle('Berita Acara Kesepakatan - ' . (string) ($cluster['cluster_name'] ?? 'Cluster'));
        $pdf->SetSubject('Merge Dokumen Batch Approval');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);

        foreach ($sourceDocs as $doc) {
            $filePath = (string) ($doc['file_path'] ?? '');
            $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
            if (!is_file($fullPath)) {
                continue;
            }

            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                try {
                    $pageCount = $pdf->setSourceFile($fullPath);
                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $tplId = $pdf->importPage($pageNo);
                        $size = $pdf->getTemplateSize($tplId);
                        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                        $pdf->AddPage($orientation);
                        $pdf->useTemplate($tplId, 0, 0, null, null, true);
                    }
                } catch (\Throwable $e) {
                    $pdf->AddPage('P');
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(0, 6, 'Gagal merge PDF sumber', 0, 1, 'L');
                    $pdf->SetFont('helvetica', '', 9);
                    $pdf->MultiCell(0, 5, (string) ($doc['doc_name'] ?? '-') . ' - ' . basename($fullPath), 0, 'L', false, 1);
                }
                continue;
            }

            if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                $pdf->AddPage('P');
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 6, (string) ($doc['doc_name'] ?? '-'), 0, 1, 'L');
                try {
                    $pdf->Image($fullPath, 10, 20, 190, 260, '', '', '', false, 150, '', true, false, 0, false, false, false);
                } catch (\Throwable $e) {
                    $pdf->SetFont('helvetica', '', 9);
                    $pdf->MultiCell(0, 5, 'Gagal memuat image: ' . basename($fullPath), 0, 'L', false, 1);
                }
                continue;
            }

            $pdf->AddPage('P');
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, (string) ($doc['doc_name'] ?? '-'), 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 5, 'File non-PDF/Image tidak bisa di-embed: ' . basename($fullPath), 0, 'L', false, 1);
        }

        $fileName = 'BA_KESEPAKATAN_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) ($cluster['cluster_name'] ?? ('CLUSTER_' . $clusterId))) . '.pdf';
        $pdf->Output($fileName, 'I');
    }

    public function downloadDocumentFormat($itemId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $item = $this->MChecklist_Dokument_MyRep->getDocumentItemFormatById((int) $itemId);
        if (empty($item) || empty($item['format_file_path'])) {
            show_404();
            return;
        }

        $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $item['format_file_path']);
        if (!is_file($fullPath)) {
            show_404();
            return;
        }

        $downloadName = !empty($item['format_file_name']) ? $item['format_file_name'] : basename($fullPath);
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
        header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
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
            $this->session->set_flashdata('error', 'Cluster ATP/RFS tidak valid.');
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $deleted = $this->MMyRep_Cleanup->deleteWholeClusterByRfsCluster($clusterId);
        $this->session->set_flashdata($deleted ? 'success' : 'error', $deleted ? 'Cluster MyRep beserta ATP, RFS, dan seluruh tahap sebelumnya berhasil dihapus bersih.' : 'Gagal menghapus cluster MyRep dari flow ATP/RFS.');
        redirect('Checklist_Dokument_MyRep');
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

    private function handleUploadError($message, $redirectPath)
    {
        if ($this->isAjaxRequest()) {
            $this->jsonResponse(false, $message, base_url($redirectPath));
            return;
        }

        $this->session->set_flashdata('error', $message);
        redirect($redirectPath);
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

    private function normalizeDateInput($date)
    {
        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        return $date;
    }

    private function resolveTcpdfPath()
    {
        $paths = [
            'D:/XAMPP/phpMyAdmin/vendor/tecnickcom/tcpdf/tcpdf.php',
            dirname(FCPATH) . '/phpMyAdmin/vendor/tecnickcom/tcpdf/tcpdf.php',
            FCPATH . '../phpMyAdmin/vendor/tecnickcom/tcpdf/tcpdf.php',
            'D:/XAMPP/htdocs/DatabaseTKM/application/third_party/tcpdf/tcpdf.php',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return '';
    }

    private function resolveVendorAutoloadPath()
    {
        $paths = [
            FCPATH . 'vendor/autoload.php',
            dirname(FCPATH) . '/DatabaseTKM/vendor/autoload.php',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return '';
    }

    private function notifyClusterDocumentSubmittedToHo($clusterId, array $document)
    {
        $cluster = $this->MChecklist_Dokument_MyRep->getClusterDetail((int) $clusterId);
        if (empty($cluster)) {
            return;
        }

        $reviewer = $this->resolveNotificationReviewer(
            'CLUSTER',
            (int) ($document['item_id'] ?? 0),
            (string) ($cluster['ho_pic_name'] ?? ''),
            (string) ($cluster['ho_pic_telegram_user_id'] ?? ''),
            (array) $cluster
        );

        $message = $this->buildChecklistTelegramMessage('CLUSTER', [
            'title' => (string) ($cluster['cluster_name'] ?? '-'),
            'site' => (string) ($cluster['city_name'] ?? '-'),
            'project' => 'Checklist Dokument MyRep',
            'module_label' => 'Checklist Dokument',
            'category' => $this->resolveClusterDocumentCategory((int) ($document['package_id'] ?? 0)),
            'detail_url' => base_url('Checklist_Dokument_MyRep/detail/' . (int) $clusterId),
            'pic_name' => (string) ($reviewer['name'] ?? ''),
            'pic_telegram_user_id' => (string) ($reviewer['telegram_user_id'] ?? ''),
            'pic_mentions' => (string) ($reviewer['mention_html'] ?? ''),
            'notification_title' => (string) ($document['notification_title'] ?? 'NEW DOCUMENT'),
        ], $document);

        $this->sendChecklistTelegramNotification($message);
    }

    private function notifyMainfeederDocumentSubmittedToHo($mainfeederId, array $document)
    {
        $mainfeeder = $this->MChecklist_Dokument_MyRep->getMainfeederDetail((int) $mainfeederId);
        if (empty($mainfeeder)) {
            return;
        }

        $reviewer = $this->resolveNotificationReviewer(
            'MAINFEEDER',
            (int) ($document['item_id'] ?? 0),
            (string) ($mainfeeder['ho_pic_name'] ?? ''),
            (string) ($mainfeeder['ho_pic_telegram_user_id'] ?? ''),
            (array) $mainfeeder
        );

        $message = $this->buildChecklistTelegramMessage('MAINFEEDER', [
            'title' => (string) ($mainfeeder['mainfeeder_name'] ?? '-'),
            'site' => (string) ($mainfeeder['city_name'] ?? '-'),
            'project' => 'Checklist Dokument MyRep Mainfeeder',
            'module_label' => 'Checklist Dokument Mainfeeder',
            'category' => $this->resolveMainfeederDocumentCategory((int) ($document['package_id'] ?? 0)),
            'detail_url' => base_url('Checklist_Dokument_MyRep/detailMainfeeder/' . (int) $mainfeederId),
            'pic_name' => (string) ($reviewer['name'] ?? ''),
            'pic_telegram_user_id' => (string) ($reviewer['telegram_user_id'] ?? ''),
            'pic_mentions' => (string) ($reviewer['mention_html'] ?? ''),
            'notification_title' => (string) ($document['notification_title'] ?? 'NEW DOCUMENT'),
        ], $document);

        $this->sendChecklistTelegramNotification($message);
    }

    private function buildChecklistTelegramMessage($type, array $entity, array $document)
    {
        $uploadedBy = trim((string) $this->session->userdata('nama_user'));
        $docName = trim((string) ($document['doc_name'] ?? '-'));
        $fileName = trim((string) ($document['file_name'] ?? ''));
        $remark = trim((string) ($document['remark'] ?? ''));
        $isNoDocumentRequired = !empty($document['is_document_not_required']);
        $fileLabel = $isNoDocumentRequired ? '[Tanpa Dokumen - Not Required]' : ($fileName !== '' ? $fileName : '-');
        $clusterLabel = strtoupper($type) === 'MAINFEEDER' ? 'Mainfeeder ' . trim((string) ($entity['title'] ?? '-')) : trim((string) ($entity['title'] ?? '-'));
        $siteLabel = trim((string) ($entity['site'] ?? '-'));
        $projectLabel = trim((string) ($entity['project'] ?? '-'));
        $categoryLabel = trim((string) ($entity['category'] ?? '-'));
        $detailUrl = trim((string) ($entity['detail_url'] ?? ''));
        $picName = trim((string) ($entity['pic_name'] ?? ''));
        $picMention = trim((string) ($entity['pic_mentions'] ?? ''));
        if ($picMention === '') {
            $picMention = $this->buildTelegramPicMention($picName, (string) ($entity['pic_telegram_user_id'] ?? ''));
        }
        $notificationTitle = trim((string) ($entity['notification_title'] ?? 'NEW DOCUMENT'));
        $moduleLabel = trim((string) ($entity['module_label'] ?? 'Checklist Dokument'));
        $displayDocLabel = $this->shouldUseModuleOnlyLabel($notificationTitle) ? $moduleLabel : $docName;
        $senderLabel = $uploadedBy !== '' ? $this->escapeTelegramText($uploadedBy) : 'System';

        $lines = [
            '✅ <b>' . $this->escapeTelegramText($notificationTitle) . '</b>',
            '',
            '📄 ' . $this->escapeTelegramText($displayDocLabel),
            '🏗 ' . $this->escapeTelegramText($categoryLabel) . ' | ' . $this->escapeTelegramText($siteLabel) . ' | ' . $this->escapeTelegramText($clusterLabel),
            '',
            '👤 ' . $senderLabel . ' -> HO (' . $picMention . ')',
            '🕒 ' . $this->escapeTelegramText($this->formatTelegramDate(date('Y-m-d H:i:s'))),
        ];

        if ($detailUrl !== '') {
            $lines[] = '';
            $lines[] = $this->escapeTelegramText($detailUrl);
        }

        return implode("\n", $lines);
    }

    private function shouldUseModuleOnlyLabel($notificationTitle)
    {
        $notificationTitle = strtoupper(trim((string) $notificationTitle));
        return in_array($notificationTitle, ['FULL CLUSTER DOCUMENT', 'CLAIM RFS'], true);
    }

    private function sendChecklistTelegramNotification($message)
    {
        $config = $this->getChecklistTelegramConfig();
        if (empty($config['enabled']) || empty($config['bot_token']) || empty($config['chat_id'])) {
            return;
        }

        $endpoint = 'https://api.telegram.org/bot' . $config['bot_token'] . '/sendMessage';
        $payload = http_build_query([
            'chat_id' => $config['chat_id'],
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => 'true',
        ]);

        $isSent = false;

        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            $isSent = $response !== false && $httpCode >= 200 && $httpCode < 300;
            if (!$isSent) {
                log_message('error', 'Checklist MyRep Telegram notification failed via cURL. HTTP: ' . $httpCode . ' Error: ' . $curlError);
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $payload,
                    'timeout' => 10,
                ],
            ]);
            $response = @file_get_contents($endpoint, false, $context);
            $isSent = $response !== false;
            if (!$isSent) {
                log_message('error', 'Checklist MyRep Telegram notification failed via file_get_contents.');
            }
        }
    }

    private function getChecklistTelegramConfig()
    {
        static $config = null;
        if ($config !== null) {
            return $config;
        }

        $envPath = APPPATH . '../.env';
        $env = is_file($envPath) ? parse_ini_file($envPath) : [];

        $config = [
            'enabled' => $this->normalizeBooleanEnv($env['TELEGRAM_CHECKLIST_MYREP_ENABLED'] ?? true),
            'bot_token' => trim((string) ($env['TELEGRAM_BOT_TOKEN'] ?? '')),
            'chat_id' => trim((string) ($env['TELEGRAM_CHAT_ID_CHECKLIST_MYREP'] ?? ($env['TELEGRAM_CHAT_ID'] ?? ''))),
        ];

        return $config;
    }

    private function normalizeBooleanEnv($value)
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private function resolveClusterDocumentCategory($packageId)
    {
        if ($packageId <= 0) {
            return 'RFS';
        }

        $row = $this->db
            ->select('g.sow_type, g.group_label')
            ->from('tb_rfs_myrep_doc_package p')
            ->join('md_rfs_myrep_doc_group g', 'g.id_doc_group = p.id_doc_group', 'inner')
            ->where('p.id_doc_package', (int) $packageId)
            ->get()
            ->row_array();

        return trim((string) ($row['sow_type'] ?? $row['group_label'] ?? 'RFS'));
    }

    private function resolveMainfeederDocumentCategory($packageId)
    {
        if ($packageId <= 0) {
            return 'MAINFEEDER';
        }

        $row = $this->db
            ->select('g.sow_type, g.group_label')
            ->from('tb_rfs_myrep_mainfeeder_doc_package p')
            ->join('md_rfs_myrep_mainfeeder_doc_group g', 'g.id_doc_group_mainfeeder = p.id_doc_group_mainfeeder', 'inner')
            ->where('p.id_doc_package_mainfeeder', (int) $packageId)
            ->get()
            ->row_array();

        return trim((string) ($row['sow_type'] ?? $row['group_label'] ?? 'MAINFEEDER'));
    }

    private function resolveNotificationReviewer($type, $itemId, $fallbackName, $fallbackTelegramUserId, array $locationContext = [])
    {
        $reviewer = [
            'name' => $fallbackName !== '' ? $fallbackName : 'PIC HO',
            'telegram_user_id' => $fallbackTelegramUserId,
            'mention_html' => '',
        ];

        if ($itemId <= 0) {
            return $reviewer;
        }

        $table = strtoupper(trim((string) $type)) === 'MAINFEEDER'
            ? 'md_rfs_myrep_mainfeeder_doc_item'
            : 'md_rfs_myrep_doc_item';
        $idField = strtoupper(trim((string) $type)) === 'MAINFEEDER'
            ? 'id_doc_item_mainfeeder'
            : 'id_doc_item';

        $row = $this->db
            ->select('verification_team')
            ->from($table)
            ->where($idField, (int) $itemId)
            ->get()
            ->row_array();

        $verificationTeam = $this->normalizeVerificationTeam((string) ($row['verification_team'] ?? ''));
        if ($verificationTeam === '') {
            return $reviewer;
        }

        $mappedReviewer = $this->resolveMappedReviewerByVerificationTeam($verificationTeam, $locationContext);
        if (!empty($mappedReviewer)) {
            return $mappedReviewer;
        }

        if ($verificationTeam !== 'SITAC') {
            return $reviewer;
        }

        return $this->resolveSitacReviewer($reviewer);
    }

    private function normalizeVerificationTeam($verificationTeam)
    {
        $verificationTeam = strtoupper(str_replace('_', ' ', trim((string) $verificationTeam)));
        return preg_replace('/\s+/', ' ', $verificationTeam);
    }

    private function resolveMappedReviewerByVerificationTeam($verificationTeam, array $locationContext = [])
    {
        $roleColumnMap = [
            'RPM' => 'rpm_area',
            'SM' => 'sm_area',
            'SPV' => 'spv_area',
            'SND' => 'snd_area',
            'ADMIN' => 'admin_area',
            'SND HO' => 'snd_ho',
            'RFS HO' => 'rfs_ho',
            'SITAC HO' => 'sitac_ho',
            'DC HO' => 'dc_ho',
        ];

        if (!isset($roleColumnMap[$verificationTeam])) {
            return [];
        }

        $cityPicMapping = $this->getNotificationCityPicMapping($locationContext);
        $nikList = myrep_pic_nik_list($cityPicMapping[$roleColumnMap[$verificationTeam]] ?? '');
        if (empty($nikList)) {
            return [];
        }

        $users = [];
        foreach ($nikList as $nik) {
            $mappedUser = $this->getNotificationUserByNik($nik);
            if (!empty($mappedUser['name'])) {
                $users[] = $mappedUser;
                continue;
            }

            $users[] = [
                'name' => (string) $nik,
                'telegram_user_id' => '',
            ];
        }

        return $this->combineNotificationReviewers($users);
    }

    private function resolveSitacReviewer(array $fallbackReviewer)
    {
        $sitacUser = $this->db
            ->select('nama_karyawan AS nama_user, telegram_user_id')
            ->from('tb_master_user_new')
            ->where('id', 22)
            ->get()
            ->row_array();

        if (!empty($sitacUser['nama_user'])) {
            $fallbackReviewer['name'] = (string) $sitacUser['nama_user'];
        }
        if (!empty($sitacUser['telegram_user_id'])) {
            $fallbackReviewer['telegram_user_id'] = (string) $sitacUser['telegram_user_id'];
        }
        $fallbackReviewer['mention_html'] = $this->buildTelegramPicMention(
            (string) ($fallbackReviewer['name'] ?? 'PIC HO'),
            (string) ($fallbackReviewer['telegram_user_id'] ?? '')
        );

        return $fallbackReviewer;
    }

    private function getNotificationCityPicMapping(array $locationContext = [])
    {
        if (!$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return [];
        }

        $city = strtoupper(trim((string) ($locationContext['city_name'] ?? '')));
        $province = strtoupper(trim((string) ($locationContext['province_name'] ?? '')));
        $regional = strtoupper(trim((string) ($locationContext['regional_name'] ?? '')));
        if ($city === '') {
            return [];
        }

        $cacheKey = $regional . '|' . $province . '|' . $city;
        if (isset($this->cityPicMappingCache[$cacheKey])) {
            return $this->cityPicMappingCache[$cacheKey];
        }

        $this->db->from('tb_myrep_pic_mapping_city');
        $this->db->where('UPPER(city_name)', $city);
        if ($province !== '') {
            $this->db->where('UPPER(province_name)', $province);
        }
        if ($regional !== '') {
            $this->db->where('UPPER(regional_name)', $regional);
        }

        $row = $this->db->limit(1)->get()->row_array();
        if (empty($row)) {
            $row = $this->db
                ->from('tb_myrep_pic_mapping_city')
                ->where('UPPER(city_name)', $city)
                ->limit(1)
                ->get()
                ->row_array();
        }

        $this->cityPicMappingCache[$cacheKey] = !empty($row) ? $row : [];
        return $this->cityPicMappingCache[$cacheKey];
    }

    private function getNotificationUserByNik($nik)
    {
        $nik = trim((string) $nik);
        if ($nik === '') {
            return [];
        }

        $row = $this->db
            ->select('nama_karyawan AS nama_user, telegram_user_id')
            ->from('tb_master_user_new')
            ->where('nik', $nik)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        return [
            'name' => (string) ($row['nama_user'] ?? ''),
            'telegram_user_id' => (string) ($row['telegram_user_id'] ?? ''),
        ];
    }

    private function combineNotificationReviewers(array $users)
    {
        $names = [];
        $mentions = [];
        $firstTelegramUserId = '';

        foreach ($users as $user) {
            $name = trim((string) ($user['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $telegramUserId = trim((string) ($user['telegram_user_id'] ?? ''));
            $names[] = $name;
            $mentions[] = $this->buildTelegramPicMention($name, $telegramUserId);
            if ($firstTelegramUserId === '' && $telegramUserId !== '') {
                $firstTelegramUserId = $telegramUserId;
            }
        }

        if (empty($names)) {
            return [];
        }

        return [
            'name' => implode(', ', $names),
            'telegram_user_id' => $firstTelegramUserId,
            'mention_html' => implode(', ', $mentions),
        ];
    }

    private function buildTelegramPicMention($picName, $telegramUserId)
    {
        $safeName = $this->escapeTelegramText($picName !== '' ? $picName : 'PIC HO');
        $telegramUserId = trim((string) $telegramUserId);

        if ($telegramUserId === '') {
            return $safeName;
        }

        return '<a href="tg://user?id=' . rawurlencode($telegramUserId) . '">' . $safeName . '</a>';
    }

    private function formatTelegramDate($dateTime)
    {
        $timestamp = strtotime((string) $dateTime);
        if ($timestamp === false) {
            return (string) $dateTime;
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $monthIndex = (int) date('n', $timestamp);
        return date('d', $timestamp) . ' ' . ($months[$monthIndex] ?? date('m', $timestamp)) . ' ' . date('Y H:i', $timestamp) . ' WIB';
    }

    private function escapeTelegramText($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

