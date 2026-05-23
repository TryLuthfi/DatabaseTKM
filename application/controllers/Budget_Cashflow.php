<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Budget_Cashflow extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        enforce_budgeting_access();
        $this->load->model('MBudget_Cashflow');
        $this->load->library('upload');
    }

    public function index()
    {

        $selectedYear = (int) $this->input->get('year');
        $selectedMonth = (int) $this->input->get('month');
        $startDate = trim((string) $this->input->get('start_date'));
        $endDate = trim((string) $this->input->get('end_date'));
        if ($selectedYear <= 0) {
            $selectedYear = (int) date('Y');
        }

        $data['title'] = 'Budgeting - Cashflow TEC';
        $data['judul'] = 'Budgeting - Cashflow TEC';
        $data['selectedYear'] = $selectedYear;
        $data['selectedMonth'] = $selectedMonth;
        $data['startDate'] = $startDate;
        $data['endDate'] = $endDate;
        $data['headers'] = $this->MBudget_Cashflow->getHeaders($selectedYear, $selectedMonth, $startDate, $endDate);
        $data['items'] = $this->MBudget_Cashflow->getItems();
        $data['bowheers'] = $this->MBudget_Cashflow->getBowheers();
        $data['picUsers'] = $this->MBudget_Cashflow->getActivePicUsers();
        $data['reportFilterOptions'] = $this->MBudget_Cashflow->getReportFilterOptions([
            'year' => $selectedYear,
            'month' => $selectedMonth,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Budget_Cashflow/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function save()
    {
        $post = $this->input->post(NULL, true);
        $itemIds = $post['detail_item_id'] ?? [];
        $directions = $post['detail_direction'] ?? [];
        $qtys = $post['detail_qty'] ?? [];
        $unitPrices = $post['detail_unit_price'] ?? [];
        $nominals = $post['detail_nominal'] ?? [];
        $remarks = $post['detail_remarks'] ?? [];

        $details = [];
        $itemIdsForValidation = [];
        foreach ($itemIds as $index => $itemId) {
            $itemId = (int) $itemId;
            $qty = isset($qtys[$index]) ? (float) $qtys[$index] : 1;
            $unitPrice = isset($unitPrices[$index]) ? (float) $unitPrices[$index] : 0;
            $nominal = isset($nominals[$index]) ? (float) $nominals[$index] : 0;
            if ($itemId <= 0 || $nominal <= 0) {
                continue;
            }

            $direction = strtoupper(trim((string) ($directions[$index] ?? 'DEBIT')));
            if (!in_array($direction, ['DEBIT', 'KREDIT'], true)) {
                $this->session->set_flashdata('status', 'gagal_simpan');
                $this->session->set_flashdata('validation_errors', ['Direction detail harus DEBIT atau KREDIT.']);
                redirect('Budget_Cashflow');
            }

            if ($qty <= 0) {
                $this->session->set_flashdata('status', 'gagal_simpan');
                $this->session->set_flashdata('validation_errors', ['Qty detail harus lebih besar dari 0.']);
                redirect('Budget_Cashflow');
            }

            if ($unitPrice > 0) {
                $expectedNominal = round($qty * $unitPrice, 2);
                if (abs($expectedNominal - $nominal) > 0.01) {
                    $this->session->set_flashdata('status', 'gagal_simpan');
                    $this->session->set_flashdata('validation_errors', [
                        'Nominal detail harus konsisten dengan Qty x Harga Satuan.',
                    ]);
                    redirect('Budget_Cashflow');
                }
            }

            $details[] = [
                'id_budget_item' => $itemId,
                'direction' => $direction,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'nominal' => $nominal,
                'remarks_item' => trim((string) ($remarks[$index] ?? '')),
            ];
            $itemIdsForValidation[] = $itemId;
        }

        $headerId = (int) ($post['id_cashflow_header'] ?? 0);

        if (empty($post['nomor_tec']) || empty($post['tanggal_cashflow']) || empty($post['project_name']) || empty($details)) {
            $this->session->set_flashdata('status', 'gagal_simpan');
            redirect('Budget_Cashflow');
        }

        $picProject = trim((string) ($post['pic_project'] ?? ''));
        if ($picProject !== '' && !$this->MBudget_Cashflow->findActivePicUserByName($picProject)) {
            $this->session->set_flashdata('status', 'gagal_simpan');
            $this->session->set_flashdata('validation_errors', [
                'PIC Project harus dipilih dari Master PIC Budget.',
            ]);
            redirect('Budget_Cashflow');
        }

        if ($this->MBudget_Cashflow->isDuplicateHeader($post['nomor_tec'], $post['project_name'], $headerId)) {
            $this->session->set_flashdata('status', 'gagal_simpan');
            $this->session->set_flashdata('validation_errors', [
                'Kombinasi Nomor TEC dan Nama Project sudah ada.',
            ]);
            redirect('Budget_Cashflow');
        }

        $header = [
            'nomor_tec' => trim($post['nomor_tec']),
            'tanggal_cashflow' => $post['tanggal_cashflow'],
            'id_bowheer' => !empty($post['id_bowheer']) ? (int) $post['id_bowheer'] : null,
            'project_name' => trim($post['project_name']),
            'pic_project' => $picProject,
            'regional' => trim((string) ($post['regional'] ?? '')),
            'kota' => trim((string) ($post['kota'] ?? '')),
            'remarks' => trim((string) ($post['remarks'] ?? '')),
            'source_type' => 'MANUAL',
            'created_by' => (int) $this->session->userdata('id_user'),
        ];

        $budgetWarnings = $this->buildBudgetWarnings($header['tanggal_cashflow'], $details, $itemIdsForValidation, $headerId);

        if ($headerId > 0) {
            $existing = $this->MBudget_Cashflow->getHeaderById($headerId);
            if (!$existing) {
                $this->session->set_flashdata('status', 'gagal_simpan');
                redirect('Budget_Cashflow');
            }
            $success = $this->MBudget_Cashflow->updateManualCashflow($headerId, $header, $details);
            $this->session->set_flashdata('status', $success ? 'sukses_edit' : 'gagal_simpan');
        } else {
            $success = $this->MBudget_Cashflow->saveManualCashflow($header, $details);
            $this->session->set_flashdata('status', $success ? 'sukses_simpan' : 'gagal_simpan');
        }
        if (!empty($budgetWarnings)) {
            $this->session->set_flashdata('validation_warnings', $budgetWarnings);
        }
        redirect('Budget_Cashflow');
    }

    public function import()
    {
        if (empty($_FILES['import_file']['name'])) {
            $this->session->set_flashdata('status', 'gagal_import');
            redirect('Budget_Cashflow');
        }

        $config['upload_path'] = FCPATH . 'uploads/budgeting/';
        $config['allowed_types'] = 'csv|xlsx|xls';
        $config['encrypt_name'] = true;

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('import_file')) {
            $this->session->set_flashdata('status', 'gagal_import');
            redirect('Budget_Cashflow');
        }

        $uploadData = $this->upload->data();
        $filePath = $uploadData['full_path'];
        $fileName = $uploadData['file_name'];

        try {
            $rows = $this->readSpreadsheetRows($filePath);
            $result = $this->processImportRows($rows);
            $this->MBudget_Cashflow->logImport(
                $fileName,
                $result['total_rows'],
                $result['success_rows'],
                $result['failed_rows'],
                $result['notes'],
                (int) $this->session->userdata('id_user')
            );

            $this->session->set_flashdata('status', $result['failed_rows'] > 0 ? 'warning_import' : 'sukses_import');
            $this->session->set_flashdata('import_notes', $result['notes']);
        } catch (Exception $e) {
            $this->MBudget_Cashflow->logImport(
                $fileName,
                0,
                0,
                0,
                'Import gagal: ' . $e->getMessage(),
                (int) $this->session->userdata('id_user')
            );
            $this->session->set_flashdata('status', 'gagal_import');
            $this->session->set_flashdata('import_notes', $e->getMessage());
        }

        redirect('Budget_Cashflow');
    }

    public function detail($id)
    {
        $header = $this->MBudget_Cashflow->getHeaderById((int) $id);
        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'status' => (bool) $header,
                'header' => $header,
                'details' => $this->MBudget_Cashflow->getHeaderDetails((int) $id),
            ]));
    }

    public function editData($id)
    {
        $header = $this->MBudget_Cashflow->getHeaderById((int) $id);
        if (!$header) {
            $this->output
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Cashflow TEC tidak ditemukan.',
                ]));
            return;
        }

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'status' => true,
                'header' => $header,
                'details' => $this->MBudget_Cashflow->getHeaderDetails((int) $id),
            ]));
    }

    public function delete($id)
    {
        $success = $this->MBudget_Cashflow->deleteHeader((int) $id);
        $this->session->set_flashdata('status', $success ? 'sukses_hapus' : 'gagal_hapus');
        redirect('Budget_Cashflow');
    }

    public function reportFilterOptions()
    {
        $filters = [
            'year' => (int) $this->input->get('year'),
            'month' => (int) $this->input->get('month'),
            'start_date' => trim((string) $this->input->get('start_date')),
            'end_date' => trim((string) $this->input->get('end_date')),
            'project_name' => trim((string) $this->input->get('project_name')),
            'id_bowheer' => (int) $this->input->get('id_bowheer'),
            'regional' => trim((string) $this->input->get('regional')),
            'kota' => trim((string) $this->input->get('kota')),
            'pic_project' => trim((string) $this->input->get('pic_project')),
        ];

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'status' => true,
                'options' => $this->MBudget_Cashflow->getReportFilterOptions($filters),
            ]));
    }

    public function downloadReport()
    {
        $mode = trim((string) $this->input->get('mode'));
        $filters = [
            'year' => (int) $this->input->get('year'),
            'month' => (int) $this->input->get('month'),
            'start_date' => trim((string) $this->input->get('start_date')),
            'end_date' => trim((string) $this->input->get('end_date')),
            'project_name' => trim((string) $this->input->get('project_name')),
            'id_bowheer' => (int) $this->input->get('id_bowheer'),
            'regional' => trim((string) $this->input->get('regional')),
            'kota' => trim((string) $this->input->get('kota')),
            'pic_project' => trim((string) $this->input->get('pic_project')),
        ];

        $headerIds = [];
        if ($mode === 'current_table') {
            $headerIds = array_filter(array_map('intval', explode(',', (string) $this->input->get('header_ids'))));
        }

        $rows = $this->MBudget_Cashflow->getHeaderSummaries($filters, $headerIds);

        $filename = 'report_cashflow_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'No',
            'Nomor TEC',
            'Tanggal',
            'Bowheer',
            'Project',
            'PIC Project',
            'Regional',
            'Kota',
            'Item',
            'Debit',
            'Kredit',
        ], ';');

        $no = 1;
        foreach ($rows as $row) {
            fputcsv($output, [
                $no++,
                $row['nomor_tec'],
                $row['tanggal_cashflow'],
                $row['nama_bowheer'] ?? '',
                $row['project_name'] ?? '',
                $row['pic_project'] ?? '',
                $row['regional'] ?? '',
                $row['kota'] ?? '',
                (int) ($row['total_items'] ?? 0),
                (float) ($row['total_debit'] ?? 0),
                (float) ($row['total_kredit'] ?? 0),
            ], ';');
        }

        fclose($output);
        exit;
    }

    private function readSpreadsheetRows($filePath)
    {
        if (!class_exists('PHPExcel')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        }

        $excel = PHPExcel_IOFactory::load($filePath);
        return $excel->getActiveSheet()->toArray(null, true, true, true);
    }

    private function processImportRows(array $rows)
    {
        $headers = [];
        $result = [
            'total_rows' => 0,
            'success_rows' => 0,
            'failed_rows' => 0,
            'notes' => '',
        ];
        $errors = [];

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === 1) {
                continue;
            }

            $nomorTec = trim((string) ($row['A'] ?? ''));
            $tanggal = trim((string) ($row['B'] ?? ''));
            $idBowheer = trim((string) ($row['C'] ?? ''));
            $projectName = trim((string) ($row['D'] ?? ''));
            $picProject = trim((string) ($row['E'] ?? ''));
            $regional = trim((string) ($row['F'] ?? ''));
            $kota = trim((string) ($row['G'] ?? ''));
            $itemCode = trim((string) ($row['H'] ?? ''));
            $direction = strtoupper(trim((string) ($row['I'] ?? 'DEBIT')));
            $qty = (float) ($row['J'] ?? 1);
            $unitPrice = (float) ($row['K'] ?? 0);
            $nominal = (float) ($row['L'] ?? 0);
            $remarksHeader = trim((string) ($row['M'] ?? ''));
            $remarksItem = trim((string) ($row['N'] ?? ''));

            if ($nomorTec === '' && $projectName === '' && $itemCode === '') {
                continue;
            }

            $result['total_rows']++;

            $item = $this->MBudget_Cashflow->findItemByCode($itemCode);
            if (
                !$item ||
                $tanggal === '' ||
                $projectName === '' ||
                $nomorTec === '' ||
                ($picProject !== '' && !$this->MBudget_Cashflow->findActivePicUserByName($picProject)) ||
                $nominal <= 0 ||
                !in_array($direction, ['DEBIT', 'KREDIT'], true) ||
                $qty <= 0 ||
                ($unitPrice > 0 && abs(round($qty * $unitPrice, 2) - $nominal) > 0.01)
            ) {
                $result['failed_rows']++;
                $errors[] = 'Row ' . $rowIndex . ' gagal diproses.';
                continue;
            }

            $headerKey = implode('|', [$nomorTec, $tanggal, $projectName, $picProject, $regional, $kota, $idBowheer]);
            if (!isset($headers[$headerKey])) {
                $headers[$headerKey] = [
                    'header' => [
                        'nomor_tec' => $nomorTec,
                        'tanggal_cashflow' => date('Y-m-d', strtotime($tanggal)),
                        'id_bowheer' => $idBowheer !== '' ? (int) $idBowheer : null,
                        'project_name' => $projectName,
                        'pic_project' => $picProject,
                        'regional' => $regional,
                        'kota' => $kota,
                        'remarks' => $remarksHeader,
                        'source_type' => 'IMPORT',
                        'created_by' => (int) $this->session->userdata('id_user'),
                    ],
                    'details' => [],
                ];
            }

            $headers[$headerKey]['details'][] = [
                'id_budget_item' => (int) $item['id_budget_item'],
                'direction' => in_array($direction, ['DEBIT', 'KREDIT'], true) ? $direction : ($item['default_direction'] ?? 'DEBIT'),
                'qty' => $qty > 0 ? $qty : 1,
                'unit_price' => $unitPrice,
                'nominal' => $nominal,
                'remarks_item' => $remarksItem,
            ];

            $result['success_rows']++;
        }

        foreach ($headers as $group) {
            $this->MBudget_Cashflow->saveManualCashflow($group['header'], $group['details']);
        }

        $result['notes'] = empty($errors)
            ? 'Import berhasil diproses.'
            : implode(' ', array_slice($errors, 0, 10));

        return $result;
    }

    public function downloadCashflowTemplateCsv()
    {
        $filename = 'template_import_cashflow_tec.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'nomor_tec',
            'tanggal_cashflow',
            'id_bowheer',
            'project_name',
            'pic_project',
            'regional',
            'kota',
            'item_code',
            'direction',
            'qty',
            'unit_price',
            'nominal',
            'remarks_header',
            'remarks_item',
        ], ';');
        fputcsv($output, [
            'TEC-001',
            date('Y-m-d'),
            '11',
            'Project Alpha',
            'Budi',
            'REGIONAL 3',
            'Jakarta',
            '4100-99-010',
            'DEBIT',
            '1',
            '100000',
            '100000',
            'Header sample',
            'Item sample',
        ], ';');
        fclose($output);
        exit;
    }

    public function downloadReferenceBowheerCsv()
    {
        $filename = 'referensi_bowheer_budget.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['id_bowheer', 'nama_bowheer'], ';');
        foreach ($this->MBudget_Cashflow->getBowheers() as $row) {
            fputcsv($output, [
                $row['id_bowheer'] ?? '',
                $row['nama_bowheer'] ?? '',
            ], ';');
        }
        fclose($output);
        exit;
    }

    public function downloadReferencePicCsv()
    {
        $filename = 'referensi_pic_project_budget.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['nama_pic'], ';');
        foreach ($this->MBudget_Cashflow->getActivePicUsers() as $row) {
            fputcsv($output, [$row['value'] ?? ''], ';');
        }
        fclose($output);
        exit;
    }

    public function downloadReferenceItemsCsv()
    {
        $filename = 'referensi_master_item_budget.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['item_code', 'item_name', 'default_direction'], ';');
        foreach ($this->MBudget_Cashflow->getItems() as $row) {
            fputcsv($output, [
                $row['item_code'] ?? '',
                $row['item_name'] ?? '',
                $row['default_direction'] ?? '',
            ], ';');
        }
        fclose($output);
        exit;
    }

    public function downloadCashflowTemplateXlsx()
    {
        if (!class_exists('ZipArchive')) {
            $this->downloadCashflowTemplateCsv();
            return;
        }

        $spreadsheet = $this->createPHPExcelObject();
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle('Cashflow TEC');

        $headers = [
            'nomor_tec',
            'tanggal_cashflow',
            'id_bowheer',
            'project_name',
            'pic_project',
            'regional',
            'kota',
            'item_code',
            'direction',
            'qty',
            'unit_price',
            'nominal',
            'remarks_header',
            'remarks_item',
        ];

        $row = 1;
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index, $row, $header);
        }

        $samples = [
            ['TEC-001', date('Y-m-d'), '11', 'Project Alpha', 'Budi', 'JABODETABEK', 'Jakarta', '4100-99-010', 'DEBIT', 1, 100000, 100000, 'Header sample', 'Item sample'],
            ['TEC-001', date('Y-m-d'), '11', 'Project Alpha', 'Budi', 'JABODETABEK', 'Jakarta', '5100-99-010', 'KREDIT', 2, 25000, 50000, 'Header sample', 'Item sample 2'],
        ];

        $row = 2;
        foreach ($samples as $sample) {
            foreach ($sample as $index => $value) {
                $sheet->setCellValueByColumnAndRow($index, $row, $value);
            }
            $row++;
        }

        $this->outputPHPExcel($spreadsheet, 'template_import_cashflow_tec.xlsx', 'Excel2007');
    }

    public function downloadBudgetTemplateCsv()
    {
        $filename = 'template_import_budget_annual_monthly.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        $headers = ['budget_year', 'item_code', 'annual_budget', 'notes'];
        for ($month = 1; $month <= 12; $month++) {
            $headers[] = 'month_' . $month;
        }
        fputcsv($output, $headers, ';');

        $sample = [date('Y'), '4100-99-010', '1200000', 'Budget sample'];
        for ($month = 1; $month <= 12; $month++) {
            $sample[] = '100000';
        }
        fputcsv($output, $sample, ';');
        fclose($output);
        exit;
    }

    public function downloadBudgetTemplateXlsx()
    {
        if (!class_exists('ZipArchive')) {
            $this->downloadBudgetTemplateCsv();
            return;
        }

        $spreadsheet = $this->createPHPExcelObject();
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle('Budget Annual');

        $headers = ['budget_year', 'item_code', 'annual_budget', 'notes'];
        for ($month = 1; $month <= 12; $month++) {
            $headers[] = 'month_' . $month;
        }

        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index, 1, $header);
        }

        $sample = [date('Y'), '4100-99-010', 1200000, 'Budget sample'];
        for ($month = 1; $month <= 12; $month++) {
            $sample[] = 100000;
        }

        foreach ($sample as $index => $value) {
            $sheet->setCellValueByColumnAndRow($index, 2, $value);
        }

        $this->outputPHPExcel($spreadsheet, 'template_import_budget_annual_monthly.xlsx', 'Excel2007');
    }

    private function createPHPExcelObject()
    {
        if (!class_exists('PHPExcel')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        }

        return new PHPExcel();
    }

    private function outputPHPExcel($spreadsheet, $filename, $writerType)
    {
        if (!class_exists('PHPExcel_IOFactory')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel/IOFactory.php';
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($spreadsheet, $writerType);
        $writer->save('php://output');
        exit;
    }

    private function buildBudgetWarnings($tanggalCashflow, array $details, array $itemIds, $headerId = 0)
    {
        $year = (int) date('Y', strtotime($tanggalCashflow));
        $month = (int) date('n', strtotime($tanggalCashflow));
        $snapshot = $this->MBudget_Cashflow->getBudgetSnapshotByItems($year, $month, $itemIds);

        $detailTotals = [];
        foreach ($details as $detail) {
            $itemId = (int) $detail['id_budget_item'];
            $signedNominal = $detail['direction'] === 'DEBIT'
                ? (float) $detail['nominal']
                : -(float) $detail['nominal'];
            $detailTotals[$itemId] = ($detailTotals[$itemId] ?? 0) + $signedNominal;
        }

        $warnings = [];
        foreach ($detailTotals as $itemId => $detailTotal) {
            $current = $snapshot[$itemId] ?? null;
            if (!$current) {
                continue;
            }

            $projectedAnnual = (float) $current['real_annual'] + $detailTotal;
            $projectedMonthly = (float) $current['real_monthly'] + $detailTotal;

            if ((float) $current['annual_budget'] > 0 && $projectedAnnual > (float) $current['annual_budget']) {
                $warnings[] = 'Annual budget terlampaui untuk item ' . $current['item_code'] . ' - ' . $current['item_name'] . '.';
            }

            if ((float) $current['monthly_budget'] > 0 && $projectedMonthly > (float) $current['monthly_budget']) {
                $warnings[] = 'Budget bulanan terlampaui untuk item ' . $current['item_code'] . ' - ' . $current['item_name'] . '.';
            }
        }

        return array_values(array_unique($warnings));
    }
}
