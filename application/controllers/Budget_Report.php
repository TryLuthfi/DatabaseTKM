<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Budget_Report extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MBudget_Report');
    }

    public function index()
    {
        if (!$this->session->userdata('id_user')) {
            redirect('Auth');
        }

        $data = $this->buildDashboardData();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Budget_Report/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function exportExcel()
    {
        if (!$this->session->userdata('id_user')) {
            redirect('Auth');
        }

        $data = $this->buildDashboardData();
        $excel = $this->createPHPExcelObject();
        $this->populateAnnualSheet($excel->setActiveSheetIndex(0), $data);
        $excel->getActiveSheet()->setTitle('Annual');
        $this->populateMonthlyMatrixSheet($excel->createSheet(), $data);
        $this->populateSimpleSheet($excel->createSheet(), 'DebitKredit', ['Direction', 'Nominal'], $data['debitKreditSummary'], ['direction', 'total_nominal']);
        $this->populateSimpleSheet($excel->createSheet(), 'ItemDetails', ['Item Code', 'Item Name', 'Total TEC', 'Total Project', 'Realisasi'], $data['itemDetails'], ['item_code', 'item_name', 'total_tec', 'total_project', 'total_realisasi']);
        $this->populateSimpleSheet($excel->createSheet(), 'TEC', ['Nomor TEC', 'Tanggal', 'Bowheer', 'Project', 'PIC', 'Regional', 'Kota', 'Realisasi'], $data['tecDetails'], ['nomor_tec', 'tanggal_cashflow', 'nama_bowheer', 'project_name', 'pic_project', 'regional', 'kota', 'total_realisasi']);
        $this->populateSimpleSheet($excel->createSheet(), 'Project', ['Project', 'Total TEC', 'Total PIC', 'Realisasi'], $data['projectDetails'], ['project_name', 'total_tec', 'total_pic', 'total_realisasi']);
        $this->populateSimpleSheet($excel->createSheet(), 'PIC', ['PIC Project', 'Total Project', 'Total TEC', 'Realisasi'], $data['picDetails'], ['pic_project', 'total_project', 'total_tec', 'total_realisasi']);
        $this->populateSimpleSheet($excel->createSheet(), 'Area', ['Regional', 'Kota', 'Total Project', 'Total TEC', 'Realisasi'], $data['areaDetails'], ['regional', 'kota', 'total_project', 'total_tec', 'total_realisasi']);

        $this->outputExcel($excel, 'dashboard_budget_' . $data['selectedYear'] . '.xlsx');
    }

    public function exportCsv()
    {
        if (!$this->session->userdata('id_user')) {
            redirect('Auth');
        }

        $data = $this->buildDashboardData();
        $monthNames = $this->getMonthNames();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="dashboard_budget_' . $data['selectedYear'] . '.csv"');

        $output = fopen('php://output', 'w');
        $header = ['item_code', 'item_name'];
        foreach ($data['selectedMonths'] as $monthNo) {
            $header[] = $monthNames[$monthNo] . '_budget';
            $header[] = $monthNames[$monthNo] . '_realisasi';
            $header[] = $monthNames[$monthNo] . '_sisa';
        }
        fputcsv($output, $header, ';');

        foreach ($data['monthlyMatrix'] as $row) {
            $csvRow = [$row['item_code'], $row['item_name']];
            foreach ($data['selectedMonths'] as $monthNo) {
                $monthData = $row['months'][$monthNo] ?? ['budget' => 0, 'realisasi' => 0, 'sisa' => 0];
                $csvRow[] = $monthData['budget'];
                $csvRow[] = $monthData['realisasi'];
                $csvRow[] = $monthData['sisa'];
            }
            fputcsv($output, $csvRow, ';');
        }

        fclose($output);
        exit;
    }

    private function buildDashboardData()
    {
        $selectedYear = (int) $this->input->get('year');
        if ($selectedYear <= 0) {
            $selectedYear = (int) date('Y');
        }

        $currentMonth = (int) date('n');
        $startMonth = (int) $this->input->get('start_month');
        $endMonth = (int) $this->input->get('end_month');
        if ($startMonth <= 0 || $startMonth > 12) {
            $startMonth = $currentMonth;
        }
        if ($endMonth <= 0 || $endMonth > 12) {
            $endMonth = $currentMonth;
        }
        if ($startMonth > $endMonth) {
            [$startMonth, $endMonth] = [$endMonth, $startMonth];
        }
        $selectedMonths = range($startMonth, $endMonth);

        $data['title'] = 'Budgeting - Dashboard';
        $data['judul'] = 'Budgeting - Dashboard';
        $data['selectedYear'] = $selectedYear;
        $data['startMonth'] = $startMonth;
        $data['endMonth'] = $endMonth;
        $data['selectedMonths'] = $selectedMonths;
        $data['yearOptions'] = $this->MBudget_Report->getAvailableYears();
        if (!in_array($selectedYear, $data['yearOptions'], true)) {
            $data['yearOptions'][] = $selectedYear;
            rsort($data['yearOptions']);
        }
        $data['annualComparison'] = $this->MBudget_Report->getAnnualComparison($selectedYear);
        $data['monthlyMatrix'] = $this->MBudget_Report->getMonthlyMatrix($selectedYear, $selectedMonths);
        $data['debitKreditComparison'] = $this->MBudget_Report->getDebitKreditComparison($selectedYear, $selectedMonths);
        $data['debitKreditSummary'] = $this->MBudget_Report->getDebitKreditSummary($selectedYear, $selectedMonths);
        $data['itemDetails'] = $this->MBudget_Report->getItemDetails($selectedYear, $selectedMonths);
        $data['tecDetails'] = $this->MBudget_Report->getTecDetails($selectedYear, $selectedMonths);
        $data['projectDetails'] = $this->MBudget_Report->getProjectDetails($selectedYear, $selectedMonths);
        $data['picDetails'] = $this->MBudget_Report->getPicDetails($selectedYear, $selectedMonths);
        $data['areaDetails'] = $this->MBudget_Report->getAreaDetails($selectedYear, $selectedMonths);
        $data['summaryCards'] = $this->MBudget_Report->getSummaryCards($selectedYear, $selectedMonths);

        return $data;
    }

    public function drilldown()
    {
        if (!$this->session->userdata('id_user')) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Unauthorized',
                    'rows' => [],
                ]));
            return;
        }

        $year = (int) $this->input->get('year');
        if ($year <= 0) {
            $year = (int) date('Y');
        }

        $months = $this->input->get('months');
        if (!is_array($months) || empty($months)) {
            $months = range(1, 12);
        } else {
            $months = array_values(array_filter(array_map('intval', $months)));
        }

        $type = (string) $this->input->get('type');
        $filters = [
            'id_budget_item' => $this->input->get('id_budget_item'),
            'month_no' => $this->input->get('month_no'),
            'project_name' => $this->input->get('project_name'),
            'pic_project' => $this->input->get('pic_project'),
            'regional' => $this->input->get('regional'),
            'kota' => $this->input->get('kota'),
            'nomor_tec' => $this->input->get('nomor_tec'),
        ];

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'status' => true,
                'rows' => $this->MBudget_Report->getDrilldownRows($type, $year, $months, $filters),
            ]));
    }

    private function getMonthNames()
    {
        return [
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
    }

    private function createPHPExcelObject()
    {
        if (!class_exists('PHPExcel')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        }

        return new PHPExcel();
    }

    private function populateAnnualSheet($sheet, array $data)
    {
        $headers = ['No', 'Kode Item', 'Nama Item', 'Budget Tahunan', 'Realisasi', 'Sisa'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index, 1, $header);
        }

        $rowNo = 2;
        foreach ($data['annualComparison'] as $index => $row) {
            $sheet->setCellValueByColumnAndRow(0, $rowNo, $index + 1);
            $sheet->setCellValueByColumnAndRow(1, $rowNo, $row['item_code']);
            $sheet->setCellValueByColumnAndRow(2, $rowNo, $row['item_name']);
            $sheet->setCellValueByColumnAndRow(3, $rowNo, (float) $row['annual_budget']);
            $sheet->setCellValueByColumnAndRow(4, $rowNo, (float) $row['total_realisasi']);
            $sheet->setCellValueByColumnAndRow(5, $rowNo, (float) $row['sisa']);
            $rowNo++;
        }
    }

    private function populateMonthlyMatrixSheet($sheet, array $data)
    {
        $monthNames = $this->getMonthNames();
        $sheet->setTitle('MonthlyMatrix');

        $headers = ['Item Code', 'Item Name'];
        foreach ($data['selectedMonths'] as $monthNo) {
            $headers[] = $monthNames[$monthNo] . ' Budget';
            $headers[] = $monthNames[$monthNo] . ' Realisasi';
            $headers[] = $monthNames[$monthNo] . ' Sisa';
        }

        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index, 1, $header);
        }

        $rowNo = 2;
        foreach ($data['monthlyMatrix'] as $row) {
            $sheet->setCellValueByColumnAndRow(0, $rowNo, $row['item_code']);
            $sheet->setCellValueByColumnAndRow(1, $rowNo, $row['item_name']);
            $col = 2;
            foreach ($data['selectedMonths'] as $monthNo) {
                $monthData = $row['months'][$monthNo] ?? ['budget' => 0, 'realisasi' => 0, 'sisa' => 0];
                $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) $monthData['budget']);
                $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) $monthData['realisasi']);
                $sheet->setCellValueByColumnAndRow($col++, $rowNo, (float) $monthData['sisa']);
            }
            $rowNo++;
        }
    }

    private function populateSimpleSheet($sheet, $title, array $headers, array $rows, array $keys)
    {
        $sheet->setTitle(substr($title, 0, 31));
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index, 1, $header);
        }

        $rowNo = 2;
        foreach ($rows as $row) {
            foreach ($keys as $index => $key) {
                $sheet->setCellValueByColumnAndRow($index, $rowNo, $row[$key] ?? '');
            }
            $rowNo++;
        }
    }

    private function outputExcel($excel, $filename)
    {
        if (!class_exists('PHPExcel_IOFactory')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel/IOFactory.php';
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }
}
