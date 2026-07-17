<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PO_Monitor extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        enforce_database_all_po_access();
        $this->load->model('MPO_Monitor');
        $this->MPO_Monitor->ensureStandaloneSchema();
    }

    public function index()
    {
        $this->MPO_Monitor->rebuildMyRepSyncClaimsSince('2026-07-01', (int) $this->session->userdata('id_user'));

        $selectedBowheer = $this->input->get('bowheer');
        $selectedSla = $this->input->get('sla');
        $fromMonth = $this->input->get('from_month') ?: date('Y-m');
        $toMonth = $this->input->get('to_month') ?: date('Y-m');

        if (!is_array($selectedBowheer)) {
            $selectedBowheer = empty($selectedBowheer) ? [] : [$selectedBowheer];
        }

        if (!is_array($selectedSla)) {
            $selectedSla = empty($selectedSla) ? [] : [$selectedSla];
        }

        $filteredPoList = [];
        $bowheerSummary = $this->MPO_Monitor->getPOSummaryByBowheer();
        $bowheerTermBreakdown = $this->MPO_Monitor->getBowheerTermBreakdown();

        $data['title'] = 'PO Monitoring';
        $data['poList'] = $filteredPoList;
        $data['bowheerSummary'] = $bowheerSummary;
        $data['bowheerTermBreakdown'] = $bowheerTermBreakdown;
        $data['batchInvoiceRows'] = $this->MPO_Monitor->getBatchInvoiceTerminRows();
        $data['dashboardSummary'] = $this->MPO_Monitor->getDashboardSummary();
        $data['dashboardInitialTotals'] = $this->MPO_Monitor->getDashboardInitialTotals();
        $data['targetWeekSummary'] = $this->MPO_Monitor->getTargetWeekSummary();
        $data['projectWeekSummary'] = $this->MPO_Monitor->getProjectWeekSummary();
        $data['carryOverSummary'] = $this->MPO_Monitor->getCarryOverSummary();
        $data['comparisonMatrix'] = $this->MPO_Monitor->getComparisonMatrix($fromMonth, $toMonth, 'month', false);
        $data['comparisonWeekMatrix'] = $this->MPO_Monitor->getComparisonMatrix($fromMonth, $toMonth, 'week', false);
        $data['selectedBowheer'] = $selectedBowheer;
        $data['selectedSla'] = $selectedSla;
        $data['isLocalAccess'] = $this->isLocalAccess();
        $data['canManagePoImport'] = $this->canManagePoImport();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('PO_Monitor/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    private function resolveSlaStatus($terms)
    {
        $sla = 'AMAN';

        foreach ($terms as $term) {
            $termRemain = (float) $term['value'] - (float) $term['invoiced_amount'];
            if ($termRemain <= 0) {
                continue;
            }

            $dueDate = !empty($term['target_week_end']) ? $term['target_week_end'] : $term['due_date'];

            if (!empty($dueDate) && strtotime($dueDate) < strtotime(date('Y-m-d'))) {
                return 'OVERDUE';
            }

            if (!empty($dueDate) && strtotime($dueDate) <= strtotime('+7 days')) {
                $sla = 'WARNING';
            }
        }

        return $sla;
    }

    public function detail($id_po = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $po = $this->MPO_Monitor->getPOById((int) $id_po);
        if (!$po) {
            show_404();
            return;
        }

        $data['title'] = 'PO Detail: ' . $po['po_number'];
        $data['po'] = $po;
        $data['terms'] = $this->MPO_Monitor->getPOTerms((int) $id_po);
        $data['allocationMap'] = $this->MPO_Monitor->getPOAllocations((int) $id_po);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('PO_Monitor/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    private function normalizeAmount($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = trim((string) $value);
        $normalized = preg_replace('/\s+/', '', $normalized);
        $normalized = preg_replace('/[^\d,.\-]/', '', $normalized);

        $lastDot = strrpos($normalized, '.');
        $lastComma = strrpos($normalized, ',');

        if ($lastDot !== false && $lastComma !== false) {
            $lastSeparator = max($lastDot, $lastComma);
            $decimalDigits = strlen($normalized) - $lastSeparator - 1;
            if ($decimalDigits > 0 && $decimalDigits <= 2) {
                if ($lastDot > $lastComma) {
                    $normalized = str_replace(',', '', $normalized);
                } else {
                    $normalized = str_replace('.', '', $normalized);
                    $normalized = str_replace(',', '.', $normalized);
                }
            } else {
                $normalized = str_replace([',', '.'], '', $normalized);
            }
        } elseif ($lastComma !== false) {
            $parts = explode(',', $normalized);
            $lastPart = end($parts);
            if (count($parts) > 2 || strlen($lastPart) === 3) {
                $normalized = str_replace(',', '', $normalized);
            } else {
                $normalized = str_replace(',', '.', $normalized);
            }
        } else {
            $parts = explode('.', $normalized);
            $lastPart = end($parts);
            if (count($parts) > 2 || strlen($lastPart) === 3) {
                $normalized = str_replace('.', '', $normalized);
            }
        }

        return (float) preg_replace('/[^\d.\-]/', '', $normalized);
    }

    public function create()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $data['title'] = 'Tambah PO';
        // term masters
        $data['masters'] = $this->db->order_by('id_master')->get('tb_term_master')->result_array();
        $data['bowheers'] = $this->db
            ->select('id_bowheer, bowheer AS nama_bowheer, pic')
            ->order_by('no_urut', 'ASC')
            ->order_by('bowheer', 'ASC')
            ->get('tb_bowheer_po')
            ->result_array();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('PO_Monitor/create', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function store()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $po_number = trim((string) $this->input->post('po_number'));
        $po_date_raw = $this->input->post('po_date');
        $po_date = $po_date_raw ? date('Y-m-d', strtotime($po_date_raw)) : null;
        $id_bowheer = (int) $this->input->post('id_bowheer');
        $total_value = $this->normalizeAmount($this->input->post('total_value'));
        $master_id = (int) $this->input->post('master_id');

        if ($po_number === '' || $total_value <= 0) {
            $this->session->set_flashdata('status', false);
            $this->session->set_flashdata('error_log', 'PO number and total value are required');
            redirect('PO_Monitor/create');
            return;
        }

        $sourceHash = hash('sha256', implode('|', [$po_number, $id_bowheer, $po_date, number_format($total_value, 2, '.', '')]));

        // insert PO
        $this->db->insert('tb_po', [
            'po_number' => $po_number,
            'po_date' => $po_date,
            'id_bowheer' => $id_bowheer > 0 ? $id_bowheer : null,
            'total_value' => $total_value,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('id_user'),
            'notes' => $this->input->post('notes'),
            'source_hash' => $sourceHash
        ]);

        $id_po = $this->db->insert_id();

        if (!$id_po) {
            $this->session->set_flashdata('status', false);
            $this->session->set_flashdata('error_log', 'Failed to create PO');
            redirect('PO_Monitor/create');
            return;
        }

        // create initial amendment (amend_no = 1)
        $this->db->insert('tb_po_amend', [
            'id_po' => $id_po,
            'amend_no' => 1,
            'release_value' => $total_value,
            'release_date' => $po_date,
            'notes' => 'Initial release'
        ]);

        $id_amend = $this->db->insert_id();

        // create terms based on selected master, fallback single-term 100%
        $splits = [];
        if ($master_id > 0) {
            $splits = $this->db->order_by('term_index')->get_where('tb_term_master_split', ['id_master' => $master_id])->result_array();
        }

        if (empty($splits)) {
            $splits = [[ 'term_index' => 1, 'percent' => 100.00 ]];
        }

        $remaining = (float) $total_value;
        $count = count($splits);
        $i = 0;
        foreach ($splits as $s) {
            $i++;
            // last term gets remainder to avoid rounding issues
            if ($i === $count) {
                $value = $remaining;
            } else {
                $value = round($total_value * (floatval($s['percent']) / 100), 2);
                $remaining -= $value;
            }

            $this->db->insert('tb_po_term', [
                'id_po' => $id_po,
                'id_amend' => $id_amend,
                'term_index' => (int) $s['term_index'],
                'percent' => (float) $s['percent'],
                'value' => $value,
                'plan_amount' => $value,
                'target_status' => 'OPEN',
                'due_date' => $this->input->post('due_date_' . $s['term_index']) ?: null,
                'sla_days' => $this->input->post('sla_days_' . $s['term_index']) ?: null
            ]);
        }

        $this->session->set_flashdata('status', true);
        $this->session->set_flashdata('error_log', 'PO created successfully');
        redirect('PO_Monitor');
    }

    public function purge_all()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->canManagePoImport()) {
            show_error('Fitur hapus semua data PO hanya tersedia untuk user khusus.', 403);
            return;
        }

        if (strtoupper((string) $this->input->method(true)) !== 'POST') {
            show_404();
            return;
        }

        $confirm = trim((string) $this->input->post('confirm_delete'));
        if ($confirm !== 'HAPUS PO') {
            $this->session->set_flashdata('status', false);
            $this->session->set_flashdata('error_log', 'Konfirmasi hapus data tidak valid.');
            redirect('PO_Monitor');
            return;
        }

        $result = $this->MPO_Monitor->purgeStandaloneData();
        $this->session->set_flashdata('status', !empty($result['status']));
        $this->session->set_flashdata('error_log', $result['message']);
        redirect('PO_Monitor');
    }

    private function isLocalAccess()
    {
        $remoteAddr = (string) $this->input->server('REMOTE_ADDR');
        $serverAddr = (string) $this->input->server('SERVER_ADDR');
        $host = strtolower((string) $this->input->server('HTTP_HOST'));

        return in_array($remoteAddr, ['127.0.0.1', '::1'], true)
            || ($serverAddr !== '' && $remoteAddr === $serverAddr)
            || strpos($host, 'localhost') !== false
            || strpos($host, '127.0.0.1') !== false;
    }

    private function canManagePoImport()
    {
        return $this->getCurrentUserNik() === '9999';
    }

    private function getCurrentUserNik()
    {
        $sessionNik = trim((string) $this->session->userdata('nik'));
        if ($sessionNik !== '') {
            return $sessionNik;
        }

        $userId = (int) $this->session->userdata('id_user');
        if ($userId <= 0 || !$this->db->table_exists('tb_master_user_new')) {
            return '';
        }

        $row = $this->db
            ->select('nik')
            ->from('tb_master_user_new')
            ->where('id', $userId)
            ->get()
            ->row_array();

        return trim((string) ($row['nik'] ?? ''));
    }

    public function batch_add_po()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (strtoupper((string) $this->input->method(true)) !== 'POST') {
            show_404();
            return;
        }

        $fields = [
            'bowheer',
            'status_po',
            'po_number',
            'no_po_sub',
            'regional',
            'kota_po',
            'detail_po',
            'remarks',
            'type_project',
            'po_date',
            'po_value',
            'po_final_value',
            'po_term'
        ];

        $posted = [];
        $maxRows = 0;
        foreach ($fields as $field) {
            $value = $this->input->post($field);
            $posted[$field] = is_array($value) ? $value : [];
            $maxRows = max($maxRows, count($posted[$field]));
        }

        $rows = [];
        for ($i = 0; $i < $maxRows; $i++) {
            $row = [];
            $hasValue = false;
            foreach ($fields as $field) {
                $row[$field] = isset($posted[$field][$i]) ? trim((string) $posted[$field][$i]) : '';
                if ($row[$field] !== '') {
                    $hasValue = true;
                }
            }
            if ($hasValue) {
                $rows[] = $row;
            }
        }

        $result = $this->MPO_Monitor->createBatchPo($rows, $this->session->userdata('id_user'));
        $summary = (array) ($result['summary'] ?? []);
        $this->session->set_flashdata('status', !empty($result['status']));

        $message = 'Batch tambah PO selesai. Insert: ' . (int) ($summary['inserted'] ?? 0)
            . ', skip: ' . (int) ($summary['skipped'] ?? 0)
            . ', terms: ' . (int) ($summary['terms'] ?? 0)
            . ', allocations: ' . (int) ($summary['allocations'] ?? 0) . '.';
        if (!empty($summary['errors'])) {
            $message .= ' Catatan: ' . implode('; ', array_slice((array) $summary['errors'], 0, 5));
        }

        $this->session->set_flashdata('error_log', $message);
        redirect('PO_Monitor');
    }

    public function import_csv()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->canManagePoImport()) {
            show_error('Fitur import PO hanya tersedia untuk user khusus.', 403);
            return;
        }

        $filePath = null;
        $sourceFile = null;

        $serverPath = trim((string) $this->input->post('server_path'));
        if (!empty($_FILES['file_csv']['name'])) {
            $uploadDir = FCPATH . 'uploads/po_monitor_import';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $config = [
                'upload_path' => $uploadDir,
                'allowed_types' => 'csv|txt',
                'max_size' => 10240,
                'file_name' => 'po_monitor_' . date('Ymd_His')
            ];

            $this->load->library('upload');
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('file_csv')) {
                $this->session->set_flashdata('status', false);
                $this->session->set_flashdata('error_log', strip_tags($this->upload->display_errors('', '')));
                redirect('PO_Monitor');
                return;
            }

            $uploadData = $this->upload->data();
            $filePath = $uploadData['full_path'];
            $sourceFile = $uploadData['client_name'];
        } elseif ($serverPath !== '' && $this->isLocalAccess()) {
            $filePath = $this->resolveReadableCsvPath($serverPath);
            $sourceFile = basename($filePath ?: $serverPath);
        } elseif ($serverPath !== '') {
            $this->session->set_flashdata('status', false);
            $this->session->set_flashdata('error_log', 'Server path hanya bisa dipakai dari akses lokal. Untuk production, upload file CSV langsung.');
            redirect('PO_Monitor');
            return;
        }

        if (empty($filePath)) {
            $this->session->set_flashdata('status', false);
            $this->session->set_flashdata('error_log', 'Pilih file CSV atau isi server path.');
            redirect('PO_Monitor');
            return;
        }

        $result = $this->MPO_Monitor->importCsv($filePath, $sourceFile, $this->session->userdata('id_user'));
        $this->session->set_flashdata('status', !empty($result['status']));
        if (!empty($result['status'])) {
            $summary = $result['summary'];
            $this->session->set_flashdata('error_log', 'Import selesai. Insert: ' . $summary['inserted'] . ', update: ' . $summary['updated'] . ', NY PO pipeline: ' . ($summary['pipeline'] ?? 0) . ', terms: ' . $summary['terms'] . ', allocations: ' . ($summary['allocations'] ?? 0) . ', claims: ' . $summary['claims']);
        } else {
            $this->session->set_flashdata('error_log', $result['message']);
        }

        redirect('PO_Monitor');
    }

    public function download_import_report()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->canManagePoImport()) {
            show_error('Fitur download report PO hanya tersedia untuk user khusus.', 403);
            return;
        }

        $headers = $this->MPO_Monitor->getImportReportHeaders();
        $rows = $this->MPO_Monitor->getImportReportRows();
        $generatedAt = date('Y-m-d H:i:s');
        $fileName = 'report-database-po-monitor-' . date('Ymd-His') . '.xls';

        $html = '<html><head><meta charset="utf-8"><style>';
        $html .= 'body{font-family:Arial,sans-serif;}';
        $html .= 'table{border-collapse:collapse;}';
        $html .= 'th,td{border:1px solid #999;padding:5px 7px;font-size:10pt;mso-number-format:\@;vertical-align:top;}';
        $html .= 'th{background:#d9e2f3;font-weight:bold;text-align:center;white-space:nowrap;}';
        $html .= '.meta td{border:0;font-size:9pt;color:#666;padding:0 0 8px 0;}';
        $html .= '</style></head><body><table>';
        $html .= '<tr class="meta"><td colspan="' . count($headers) . '">Report Database PO Monitor - ' . htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8') . '</td></tr>';
        $html .= '<tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $html .= '</tr>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($headers as $header) {
                $html .= '<td>' . htmlspecialchars((string) ($row[$header] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        $this->output
            ->set_content_type('application/vnd.ms-excel')
            ->set_header('Content-Disposition: attachment; filename="' . $fileName . '"')
            ->set_header('Cache-Control: max-age=0')
            ->set_output($html);
    }

    private function resolveReadableCsvPath($path)
    {
        $path = trim((string) $path, " \t\n\r\0\x0B\"'");
        if ($path !== '' && is_readable($path)) {
            return $path;
        }

        $normalized = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        if ($normalized !== $path && is_readable($normalized)) {
            return $normalized;
        }

        $directory = dirname($path);
        $fileName = basename($path);
        if (is_dir($directory)) {
            $targetKey = preg_replace('/\s+/', '', strtolower($fileName));
            foreach (glob($directory . DIRECTORY_SEPARATOR . '*.csv') ?: [] as $candidate) {
                $candidateKey = preg_replace('/\s+/', '', strtolower(basename($candidate)));
                if ($candidateKey === $targetKey || stripos($candidateKey, 'databasepocsv') !== false) {
                    return $candidate;
                }
            }
        }

        return $path;
    }

    public function dashboard_datatable()
    {
        if (empty($this->session->userdata('id_user'))) {
            show_error('Unauthorized', 401);
            return;
        }

        $result = $this->MPO_Monitor->getDashboardDatatable($this->input->post(null, true) ?: []);
        $data = [];
        $start = (int) ($this->input->post('start') ?: 0);
        foreach ($result['data'] as $index => $row) {
            $picClass = $this->dashboardPicClass($row['pic']);
            $totalOutsClass = ((float) $row['total_outs'] > 0 && in_array($row['bowheer'], ['PT EMR - NRO', 'PT IFORTE - FIBERIZATION', 'PT VGREEN'], true))
                ? ' po-dash-alert'
                : '';
            $data[] = [
                $start + $index + 1,
                '<span class="' . $picClass . '">' . htmlspecialchars($row['pic'] ?: '-') . '</span>',
                '<span class="' . str_replace('po-dash-pic', 'po-dash-bow', $picClass) . '">' . htmlspecialchars($row['bowheer'] ?: '-') . '</span>',
                '<span class="text-right d-block">' . $this->dashboardNumber($row['all_po']) . '</span>',
                '<span class="po-dash-col-invoice text-right d-block">' . $this->dashboardNumber($row['done_inv_2026']) . '</span>',
                '<span class="po-dash-col-outs text-right d-block">' . $this->dashboardNumber($row['outs_2026_on_target']) . '</span>',
                '<span class="po-dash-col-ny-target text-right d-block">' . $this->dashboardNumber($row['ny_po_on_target_2026']) . '</span>',
                '<span class="po-dash-grand text-right d-block">' . $this->dashboardNumber($row['grandtotal_target']) . '</span>',
                '<span class="po-dash-col-ny-total text-right d-block">' . $this->dashboardNumber($row['ny_po_total']) . '</span>',
                '<span class="text-right d-block">' . $this->dashboardNumber($row['co_to_2027']) . '</span>',
                '<span class="text-right d-block' . $totalOutsClass . '">' . $this->dashboardNumber($row['total_outs']) . '</span>'
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'draw' => $result['draw'],
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'filteredTotals' => $this->formatDashboardTotals($result['filteredTotals'] ?? []),
                'data' => $data
            ]));
    }

    public function comparison_detail()
    {
        if (empty($this->session->userdata('id_user'))) {
            show_error('Unauthorized', 401);
            return;
        }

        $idBowheer = (int) $this->input->post('id_bowheer');
        $periodKey = trim((string) $this->input->post('period_key'));
        $groupBy = $this->input->post('group_by') === 'week' ? 'week' : 'month';
        $type = $this->input->post('type') === 'achieved' ? 'achieved' : 'target';

        $project = $this->db->select('bowheer')->get_where('tb_bowheer_po', ['id_bowheer' => $idBowheer])->row_array();
        if (!$project || $periodKey === '') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'title' => 'Detail PO',
                    'html' => '<div class="alert alert-warning mb-0">Parameter detail tidak valid.</div>'
                ]));
            return;
        }

        $rows = $this->MPO_Monitor->getComparisonDetail($idBowheer, $periodKey, $groupBy, $type);
        $title = '<span class="po-monitor-modal-eyebrow">Detail Perbandingan</span>'
            . htmlspecialchars(ucfirst($type) . ' - ' . $project['bowheer'] . ' - ' . $this->comparisonPeriodLabel($periodKey, $groupBy));

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'title' => $title,
                'html' => $this->renderComparisonDetailHtml($rows, $type)
            ]));
    }

    public function term_detail()
    {
        if (empty($this->session->userdata('id_user'))) {
            show_error('Unauthorized', 401);
            return;
        }

        $idBowheer = (int) $this->input->post('id_bowheer');
        $metric = trim((string) $this->input->post('metric'));
        $termIndex = (int) $this->input->post('term_index');

        $project = $this->db->select('bowheer')->get_where('tb_bowheer_po', ['id_bowheer' => $idBowheer])->row_array();
        if (!$project) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'title' => '<span class="po-monitor-modal-eyebrow">Detail Term</span>Detail PO',
                    'html' => '<div class="alert alert-warning mb-0">Parameter detail tidak valid.</div>'
                ]));
            return;
        }

        $rows = $this->MPO_Monitor->getBowheerTermDetail($idBowheer, $metric, $termIndex);
        $metricLabel = $this->termDetailMetricLabel($metric, $termIndex);
        $title = '<span class="po-monitor-modal-eyebrow">Detail Term</span>' . htmlspecialchars($project['bowheer']) . ' - ' . htmlspecialchars($metricLabel);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'title' => $title,
                'html' => $this->renderTermDetailHtml($rows, $metric)
            ]));
    }

    private function renderComparisonDetailHtml($rows, $type)
    {
        if (empty($rows)) {
            return '<div class="alert alert-info mb-0">Tidak ada detail PO untuk periode ini.</div>';
        }

        $grouped = $this->groupRowsByRegional($rows, 'amount');
        $total = $grouped['total_amount'];
        $html = '<div class="po-monitor-modal-summary">';
        $html .= '<div class="po-monitor-modal-stat"><span class="po-monitor-modal-stat__label">Total Row</span><span class="po-monitor-modal-stat__value js-po-detail-total-row">' . number_format(count($rows), 0, ',', '.') . '</span></div>';
        $html .= '<div class="po-monitor-modal-stat po-monitor-modal-stat--green"><span class="po-monitor-modal-stat__label">Jenis</span><span class="po-monitor-modal-stat__value">' . ($type === 'achieved' ? 'Achieved' : 'Target') . '</span></div>';
        $html .= '<div class="po-monitor-modal-stat po-monitor-modal-stat--amber"><span class="po-monitor-modal-stat__label">Total Amount</span><span class="po-monitor-modal-stat__value js-po-detail-total-amount">' . number_format($total, 0, ',', '.') . '</span></div>';
        $html .= '</div>';

        $html .= $this->renderRegionalSummaryHtml($grouped['summary'], 'Amount');
        $html .= $this->renderTermSummaryHtml($rows, 'amount', 'Amount');

        foreach ($grouped['groups'] as $regional => $regionalRows) {
            if (empty($regionalRows)) {
                continue;
            }

            $regionalTotal = (float) ($grouped['summary'][$regional]['amount'] ?? 0);
            $html .= $this->renderRegionalSectionHeader($regional, count($regionalRows), $regionalTotal);
            $html .= '<div class="table-responsive"><table class="table table-bordered table-sm table-striped mb-0 po-monitor-detail-table">';
            $html .= '<thead><tr>';
            $html .= '<th>No</th><th>Type Project</th><th>No PO</th><th>Tgl PO</th><th>Sub PO</th><th>Kota</th><th>Detail</th><th>Remarks</th><th>Term</th><th>' . ($type === 'achieved' ? 'Invoice Date' : 'Period / Claim Date') . '</th><th class="text-right">Amount</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($regionalRows as $index => $row) {
                $amount = (float) ($row['amount'] ?? 0);
                $termIndex = (int) ($row['term_index'] ?? 0);
                $isInvoicedTarget = $type === 'target' && (float) ($row['invoiced_amount'] ?? 0) > 0;
                if ($type === 'achieved') {
                    $period = $this->formatIndonesianDate($row['invoice_date'] ?? '');
                } elseif ($isInvoicedTarget && !empty($row['claim_invoice_date'])) {
                    $period = $this->formatIndonesianDate($row['claim_invoice_date']);
                } else {
                    $period = $this->formatIndonesianDate($row['target_week_start'] ?? '') . ' s/d ' . $this->formatIndonesianDate($row['target_week_end'] ?? '');
                }

                $html .= '<tr class="' . ($isInvoicedTarget ? 'po-monitor-detail-row-invoiced' : '') . '" data-term-index="' . $termIndex . '" data-filter-amount="' . htmlspecialchars((string) $amount) . '" data-invoiced="' . ($isInvoicedTarget ? '1' : '0') . '">';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['type_project'] ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['po_number'] ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($this->formatIndonesianDate($row['po_date'] ?? '')) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['no_po_sub'] ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['kota_po'] ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['detail_po'] ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['remarks'] ?: '-') . '</td>';
                $html .= '<td>' . ($termIndex > 0 ? 'Term ' . $termIndex : '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($period) . '</td>';
                $html .= '<td class="text-right">' . number_format($amount, 0, ',', '.') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody><tfoot><tr><th colspan="10" class="text-right">TOTAL ' . htmlspecialchars($regional) . '</th><th class="text-right">' . number_format($regionalTotal, 0, ',', '.') . '</th></tr></tfoot>';
            $html .= '</table></div>';
            $html .= $this->closeRegionalSection();
        }

        return $html;
    }

    private function renderTermDetailHtml($rows, $metric)
    {
        if (empty($rows)) {
            return '<div class="alert alert-info mb-0">Tidak ada detail PO untuk angka ini.</div>';
        }

        $totalRelease = 0;
        $totalTerm = 0;
        $totalInvoiced = 0;
        $totalRemaining = 0;
        $metricTotal = 0;

        foreach ($rows as $row) {
            $totalRelease += (float) ($row['current_release_value'] ?? 0);
            $totalTerm += (float) ($row['term_value'] ?? 0);
            $totalInvoiced += (float) ($row['invoiced_amount'] ?? 0);
            $totalRemaining += (float) ($row['remaining'] ?? 0);
        }

        if ($metric === 'total_po') {
            $metricTotal = $totalRelease;
        } elseif ($metric === 'term_value') {
            $metricTotal = $totalTerm;
        } elseif ($metric === 'term_done') {
            $metricTotal = $totalInvoiced;
        } else {
            $metricTotal = $totalRemaining;
        }

        $grouped = $this->groupRowsByRegional($rows, $this->termMetricAmountKey($metric));

        $html = '<div class="po-monitor-modal-summary">';
        $html .= '<div class="po-monitor-modal-stat"><span class="po-monitor-modal-stat__label">Total Row</span><span class="po-monitor-modal-stat__value js-po-detail-total-row">' . number_format(count($rows), 0, ',', '.') . '</span></div>';
        $html .= '<div class="po-monitor-modal-stat po-monitor-modal-stat--green"><span class="po-monitor-modal-stat__label">Total Angka</span><span class="po-monitor-modal-stat__value js-po-detail-total-amount">' . number_format($metricTotal, 0, ',', '.') . '</span></div>';
        $html .= '<div class="po-monitor-modal-stat po-monitor-modal-stat--amber"><span class="po-monitor-modal-stat__label">Outstanding</span><span class="po-monitor-modal-stat__value">' . number_format($totalRemaining, 0, ',', '.') . '</span></div>';
        $html .= '</div>';

        $html .= $this->renderRegionalSummaryHtml($grouped['summary'], 'Total Angka');
        $html .= $this->renderTermSummaryHtml($rows, $this->termMetricAmountKey($metric), 'Total Angka');

        foreach ($grouped['groups'] as $regional => $regionalRows) {
            if (empty($regionalRows)) {
                continue;
            }

            $regionalRelease = 0;
            $regionalTerm = 0;
            $regionalInvoiced = 0;
            $regionalRemaining = 0;
            foreach ($regionalRows as $row) {
                $regionalRelease += (float) ($row['current_release_value'] ?? 0);
                $regionalTerm += (float) ($row['term_value'] ?? 0);
                $regionalInvoiced += (float) ($row['invoiced_amount'] ?? 0);
                $regionalRemaining += (float) ($row['remaining'] ?? 0);
            }

            $html .= $this->renderRegionalSectionHeader($regional, count($regionalRows), (float) ($grouped['summary'][$regional]['amount'] ?? 0));
            $html .= '<div class="table-responsive"><table class="table table-bordered table-sm table-striped mb-0 po-monitor-detail-table">';
            $html .= '<thead><tr>';
            $html .= '<th>No</th><th>Type Project</th><th>No PO</th><th>Tgl PO</th><th>Sub PO</th><th>Kota</th><th>Detail</th><th>Remarks</th><th>Term</th><th class="text-right">PO Value</th><th class="text-right">Nilai Term</th><th class="text-right">Sudah Ditagih</th><th class="text-right">Sisa</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($regionalRows as $index => $row) {
                $termIndex = (int) ($row['term_index'] ?? 0);
                $filterAmount = (float) ($row[$this->termMetricAmountKey($metric)] ?? 0);
                $html .= '<tr data-term-index="' . $termIndex . '" data-filter-amount="' . htmlspecialchars((string) $filterAmount) . '">';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['type_project'] ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['po_number'] ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($this->formatIndonesianDate($row['po_date'] ?? '')) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['no_po_sub'] ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['kota_po'] ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['detail_po'] ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['remarks'] ?: '-') . '</td>';
                $html .= '<td>' . ($termIndex > 0 ? 'Term ' . $termIndex : '-') . '</td>';
                $html .= '<td class="text-right">' . number_format((float) ($row['current_release_value'] ?? 0), 0, ',', '.') . '</td>';
                $html .= '<td class="text-right">' . number_format((float) ($row['term_value'] ?? 0), 0, ',', '.') . '</td>';
                $html .= '<td class="text-right">' . number_format((float) ($row['invoiced_amount'] ?? 0), 0, ',', '.') . '</td>';
                $html .= '<td class="text-right">' . number_format((float) ($row['remaining'] ?? 0), 0, ',', '.') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody><tfoot><tr>';
            $html .= '<th colspan="9" class="text-right">TOTAL ' . htmlspecialchars($regional) . '</th>';
            $html .= '<th class="text-right">' . number_format($regionalRelease, 0, ',', '.') . '</th>';
            $html .= '<th class="text-right">' . number_format($regionalTerm, 0, ',', '.') . '</th>';
            $html .= '<th class="text-right">' . number_format($regionalInvoiced, 0, ',', '.') . '</th>';
            $html .= '<th class="text-right">' . number_format($regionalRemaining, 0, ',', '.') . '</th>';
            $html .= '</tr></tfoot></table></div>';
            $html .= $this->closeRegionalSection();
        }

        return $html;
    }

    private function regionalLabels()
    {
        return ['REGIONAL 1', 'REGIONAL 2', 'REGIONAL 3', 'REGIONAL 4', 'REGIONAL 5'];
    }

    private function normalizeRegionalLabel($regional)
    {
        $value = strtoupper(trim((string) $regional));
        $value = preg_replace('/[\s_\-]+/', ' ', $value);

        if (preg_match('/(?:REGIONAL|REGION|REG)\s*([1-5])\b/', $value, $match)) {
            return 'REGIONAL ' . $match[1];
        }

        if (preg_match('/^R?([1-5])$/', $value, $match)) {
            return 'REGIONAL ' . $match[1];
        }

        return $value !== '' ? $value : 'TANPA REGIONAL';
    }

    private function groupRowsByRegional($rows, $amountKey)
    {
        $groups = [];
        $summary = [];
        foreach ($this->regionalLabels() as $label) {
            $groups[$label] = [];
            $summary[$label] = ['count' => 0, 'amount' => 0];
        }

        $totalAmount = 0;
        $rows = $this->sortRowsByCityAndTerm($rows);

        foreach ($rows as $row) {
            $regional = $this->normalizeRegionalLabel($row['regional'] ?? '');
            if (!isset($groups[$regional])) {
                $groups[$regional] = [];
                $summary[$regional] = ['count' => 0, 'amount' => 0];
            }

            $amount = (float) ($row[$amountKey] ?? 0);
            $groups[$regional][] = $row;
            $summary[$regional]['count']++;
            $summary[$regional]['amount'] += $amount;
            $totalAmount += $amount;
        }

        return [
            'groups' => $groups,
            'summary' => $summary,
            'total_amount' => $totalAmount
        ];
    }

    private function sortRowsByCityAndTerm($rows)
    {
        usort($rows, function ($a, $b) {
            $cityA = strtoupper(trim((string) ($a['kota_po'] ?? '')));
            $cityB = strtoupper(trim((string) ($b['kota_po'] ?? '')));
            if ($cityA === '') {
                $cityA = 'ZZZ';
            }
            if ($cityB === '') {
                $cityB = 'ZZZ';
            }

            $cityCompare = strcmp($cityA, $cityB);
            if ($cityCompare !== 0) {
                return $cityCompare;
            }

            $termCompare = ((int) ($a['term_index'] ?? 0)) <=> ((int) ($b['term_index'] ?? 0));
            if ($termCompare !== 0) {
                return $termCompare;
            }

            return strcmp((string) ($a['po_number'] ?? ''), (string) ($b['po_number'] ?? ''));
        });

        return $rows;
    }

    private function renderRegionalSummaryHtml($summary, $valueLabel)
    {
        $html = '<div class="po-monitor-summary-band po-monitor-summary-band--regional">';
        $html .= '<div class="po-monitor-summary-band__header"><span>Filter Regional</span><b>Pilih salah satu regional untuk menyaring detail</b></div>';
        $html .= '<div class="po-monitor-regional-summary">';
        foreach ($this->regionalLabels() as $index => $regional) {
            $item = $summary[$regional] ?? ['count' => 0, 'amount' => 0];
            $html .= '<button type="button" class="po-monitor-regional-card po-monitor-regional-card--r' . ($index + 1) . '" data-regional-key="' . htmlspecialchars($this->regionalDomKey($regional)) . '">';
            $html .= '<span class="po-monitor-regional-card__label">' . htmlspecialchars($regional) . '<b>' . number_format((int) $item['count'], 0, ',', '.') . '</b></span>';
            $html .= '<span class="po-monitor-regional-card__value">' . number_format((float) $item['amount'], 0, ',', '.') . '</span>';
            $html .= '<span class="po-monitor-regional-card__caption">' . htmlspecialchars($valueLabel) . '</span>';
            $html .= '</button>';
        }
        $html .= '</div></div>';

        foreach ($summary as $regional => $item) {
            if (in_array($regional, $this->regionalLabels(), true) || (int) $item['count'] <= 0) {
                continue;
            }

            $html .= '<div class="po-monitor-regional-extra">';
            $html .= '<strong>' . htmlspecialchars($regional) . '</strong>';
            $html .= '<span>' . number_format((int) $item['count'], 0, ',', '.') . ' row</span>';
            $html .= '<span>' . number_format((float) $item['amount'], 0, ',', '.') . '</span>';
            $html .= '</div>';
        }

        return $html;
    }

    private function renderTermSummaryHtml($rows, $amountKey, $valueLabel)
    {
        $summary = [];
        for ($term = 1; $term <= 5; $term++) {
            $summary[$term] = ['count' => 0, 'amount' => 0];
        }

        foreach ($rows as $row) {
            $termIndex = (int) ($row['term_index'] ?? 0);
            if ($termIndex < 1 || $termIndex > 5) {
                continue;
            }

            $summary[$termIndex]['count']++;
            $summary[$termIndex]['amount'] += (float) ($row[$amountKey] ?? 0);
        }

        $html = '<div class="po-monitor-summary-band po-monitor-summary-band--term">';
        $html .= '<div class="po-monitor-summary-band__header"><span>Filter Term</span><b>Pilih term untuk melihat sebaran regional terkait</b></div>';
        $html .= '<div class="po-monitor-term-summary">';
        for ($term = 1; $term <= 5; $term++) {
            $item = $summary[$term];
            $html .= '<button type="button" class="po-monitor-term-card po-monitor-term-card--t' . $term . '" data-term-index="' . $term . '">';
            $html .= '<span class="po-monitor-term-card__label">Term ' . $term . '<b>' . number_format((int) $item['count'], 0, ',', '.') . '</b></span>';
            $html .= '<span class="po-monitor-term-card__value">' . number_format((float) $item['amount'], 0, ',', '.') . '</span>';
            $html .= '<span class="po-monitor-term-card__caption">' . htmlspecialchars($valueLabel) . '</span>';
            $html .= '</button>';
        }
        $html .= '</div></div>';

        return $html;
    }

    private function renderRegionalSectionHeader($regional, $count, $amount)
    {
        return '<div class="po-monitor-regional-group" data-regional-key="' . htmlspecialchars($this->regionalDomKey($regional)) . '">'
            . '<div class="po-monitor-regional-section">'
            . '<div><span>Regional</span><strong>' . htmlspecialchars($regional) . '</strong></div>'
            . '<div><span>Row</span><strong class="js-po-regional-section-row">' . number_format((int) $count, 0, ',', '.') . '</strong></div>'
            . '<div><span>Total</span><strong class="js-po-regional-section-total">' . number_format((float) $amount, 0, ',', '.') . '</strong></div>'
            . '</div>';
    }

    private function closeRegionalSection()
    {
        return '</div>';
    }

    private function regionalDomKey($regional)
    {
        $key = strtolower(trim((string) $regional));
        $key = preg_replace('/[^a-z0-9]+/', '-', $key);
        return trim($key, '-') ?: 'tanpa-regional';
    }

    private function termMetricAmountKey($metric)
    {
        if ($metric === 'total_po') {
            return 'current_release_value';
        }
        if ($metric === 'term_value') {
            return 'term_value';
        }
        if ($metric === 'term_done') {
            return 'invoiced_amount';
        }
        return 'remaining';
    }

    private function termDetailMetricLabel($metric, $termIndex)
    {
        $termSuffix = (int) $termIndex > 0 ? ' Term ' . (int) $termIndex : '';
        if ($metric === 'total_po') {
            return 'Total PO';
        }
        if ($metric === 'term_value') {
            return 'Nilai Term' . $termSuffix;
        }
        if ($metric === 'term_done') {
            return 'Sudah Ditagih' . $termSuffix;
        }
        if ($metric === 'term_remaining') {
            return 'Sisa' . $termSuffix;
        }
        return 'Outstanding Term';
    }

    private function comparisonPeriodLabel($periodKey, $groupBy)
    {
        if ($groupBy === 'week' && preg_match('/^(\d{4})-W(\d{2})$/', $periodKey, $match)) {
            return 'Week ' . (int) $match[2] . ' ' . $match[1];
        }

        if (preg_match('/^\d{4}-\d{2}$/', $periodKey)) {
            return $this->formatIndonesianMonth($periodKey);
        }

        return $periodKey;
    }

    private function formatIndonesianMonth($monthKey)
    {
        $timestamp = strtotime($monthKey . '-01');
        if (!$timestamp) {
            return $monthKey;
        }

        return $this->indonesianMonthName((int) date('n', $timestamp)) . ' ' . date('Y', $timestamp);
    }

    private function formatIndonesianDate($date)
    {
        $timestamp = strtotime((string) $date);
        if (!$timestamp) {
            return '-';
        }

        return (int) date('j', $timestamp) . ' ' . $this->indonesianMonthName((int) date('n', $timestamp)) . ' ' . date('Y', $timestamp);
    }

    private function indonesianMonthName($month)
    {
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
            12 => 'Desember'
        ];

        return $months[$month] ?? (string) $month;
    }

    private function formatDashboardTotals($totals)
    {
        return [
            'data_count' => number_format((float) ($totals['data_count'] ?? 0), 0, ',', '.'),
            'all_po' => $this->dashboardNumber($totals['all_po'] ?? 0),
            'done_inv_2026' => $this->dashboardNumber($totals['done_inv_2026'] ?? 0),
            'outs_2026_on_target' => $this->dashboardNumber($totals['outs_2026_on_target'] ?? 0),
            'ny_po_on_target_2026' => $this->dashboardNumber($totals['ny_po_on_target_2026'] ?? 0),
            'done_outs_ny_2026' => $this->dashboardNumber(($totals['done_inv_2026'] ?? 0) + ($totals['outs_2026_on_target'] ?? 0) + ($totals['ny_po_on_target_2026'] ?? 0)),
            'grandtotal_target' => $this->dashboardNumber($totals['grandtotal_target'] ?? 0),
            'ny_po_total' => $this->dashboardNumber($totals['ny_po_total'] ?? 0),
            'co_to_2027' => $this->dashboardNumber($totals['co_to_2027'] ?? 0),
            'total_outs' => $this->dashboardNumber($totals['total_outs'] ?? 0)
        ];
    }

    private function dashboardNumber($value)
    {
        $value = (float) $value;
        if (abs($value) < 0.5) {
            return '-';
        }

        $formatted = number_format(abs($value), 0, ',', '.');
        return $value < 0 ? '(' . $formatted . ')' : $formatted;
    }

    private function dashboardPicClass($pic)
    {
        $key = strtoupper(trim((string) $pic));
        if ($key === 'BP ZAENUL') {
            return 'po-dash-pic-z';
        }
        if ($key === 'BP WARDANI') {
            return 'po-dash-pic-w';
        }
        if ($key === 'BP SLAMET') {
            return 'po-dash-pic-s';
        }
        if ($key === 'BP FRINGGA') {
            return 'po-dash-pic-f';
        }
        if ($key === 'BP DONNY') {
            return 'po-dash-pic-d';
        }
        if ($key === 'BP SUMIRAT') {
            return 'po-dash-pic-su';
        }
        if ($key === 'BP HENDRY') {
            return 'po-dash-pic-h';
        }
        if ($key === 'BP WENDY') {
            return 'po-dash-pic-we';
        }
        if ($key === 'LOGISTIK') {
            return 'po-dash-pic-log';
        }
        if ($key === 'BP THOMAS') {
            return 'po-dash-pic-t';
        }
        return '';
    }

    public function po_datatable()
    {
        if (empty($this->session->userdata('id_user'))) {
            show_error('Unauthorized', 401);
            return;
        }

        $result = $this->MPO_Monitor->getPODatatable($this->input->post(null, true) ?: []);
        $data = [];
        $start = (int) ($this->input->post('start') ?: 0);
        foreach ($result['data'] as $index => $row) {
            $data[] = [
                $start + $index + 1,
                htmlspecialchars($row['po_number'] ?: '-'),
                htmlspecialchars($row['po_date'] ?: '-'),
                number_format((float) $row['current_release_value'], 0, ',', '.'),
                number_format((float) $row['total_invoiced'], 0, ',', '.'),
                number_format((float) $row['remaining'], 0, ',', '.'),
                htmlspecialchars($row['sla'] ?: 'AMAN'),
                '<a href="' . site_url('PO_Monitor/detail/' . (int) $row['id_po']) . '" class="btn btn-sm btn-primary">Detail</a>'
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'draw' => $result['draw'],
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $data
            ]));
    }

    public function claim_term()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idTerm = (int) $this->input->post('id_term');
        $idPo = (int) $this->input->post('id_po');
        $idAllocation = (int) $this->input->post('id_allocation');
        $invoiceDateRaw = $this->input->post('invoice_date');
        $invoiceDate = $invoiceDateRaw ? date('Y-m-d', strtotime($invoiceDateRaw)) : null;
        $amount = $this->normalizeAmount($this->input->post('invoice_amount'));

        $result = $this->MPO_Monitor->claimTerm($idTerm, $invoiceDate, $amount, $this->session->userdata('id_user'), $idAllocation);
        $this->session->set_flashdata('status', !empty($result['status']));
        $this->session->set_flashdata('error_log', $result['message']);
        redirect('PO_Monitor/detail/' . $idPo);
    }

    public function update_invoice_claim()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idTerm = (int) $this->input->post('id_term');
        $idPo = (int) $this->input->post('id_po');
        $idAllocation = (int) $this->input->post('id_allocation');
        $invoiceDateRaw = $this->input->post('invoice_date');
        $invoiceDate = $invoiceDateRaw ? date('Y-m-d', strtotime($invoiceDateRaw)) : null;
        $amount = $this->normalizeAmount($this->input->post('invoice_amount'));

        $result = $this->MPO_Monitor->replaceInvoiceClaim($idTerm, $idAllocation, $invoiceDate, $amount, $this->session->userdata('id_user'));
        $this->session->set_flashdata('status', !empty($result['status']));
        $this->session->set_flashdata('error_log', $result['message']);
        redirect('PO_Monitor/detail/' . $idPo);
    }

    public function reset_invoice_claim()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idTerm = (int) $this->input->post('id_term');
        $idPo = (int) $this->input->post('id_po');
        $idAllocation = (int) $this->input->post('id_allocation');

        $result = $this->MPO_Monitor->resetInvoiceClaim($idTerm, $idAllocation);
        $this->session->set_flashdata('status', !empty($result['status']));
        $this->session->set_flashdata('error_log', $result['message']);
        redirect('PO_Monitor/detail/' . $idPo);
    }

    public function batch_invoice_termin()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (strtoupper((string) $this->input->method(true)) !== 'POST') {
            show_404();
            return;
        }

        $invoiceDateRaw = $this->input->post('invoice_date');
        $invoiceDate = $invoiceDateRaw ? date('Y-m-d', strtotime($invoiceDateRaw)) : null;
        $poNumbers = $this->input->post('po_number');
        $termNos = $this->input->post('term_no');
        $amounts = $this->input->post('invoice_amount');

        if (empty($invoiceDate) || !is_array($poNumbers) || !is_array($termNos) || !is_array($amounts)) {
            $this->session->set_flashdata('status', false);
            $this->session->set_flashdata('error_log', 'Data batch invoice tidak lengkap.');
            redirect('PO_Monitor');
            return;
        }

        $lookup = [];
        foreach ($this->MPO_Monitor->getBatchInvoiceTerminRows() as $row) {
            $key = strtoupper(trim((string) $row['po_number'])) . '|' . (int) $row['term_index'];
            $lookup[$key] = $row;
        }

        $success = 0;
        $skipped = [];
        $usedAmount = [];
        $count = max(count($poNumbers), count($termNos), count($amounts));

        for ($index = 0; $index < $count; $index++) {
            $poNumber = trim((string) ($poNumbers[$index] ?? ''));
            $termNo = (int) ($termNos[$index] ?? 0);
            $amount = $this->normalizeAmount($amounts[$index] ?? 0);
            $key = strtoupper($poNumber) . '|' . $termNo;

            if ($poNumber === '' || $termNo < 1 || $termNo > 5 || $amount <= 0) {
                $skipped[] = 'Row ' . ($index + 1) . ' tidak lengkap.';
                continue;
            }

            if (empty($lookup[$key])) {
                $skipped[] = $poNumber . ' term ' . $termNo . ' tidak ditemukan.';
                continue;
            }

            $remaining = (float) ($lookup[$key]['remaining'] ?? 0) - (float) ($usedAmount[$key] ?? 0);
            if ($amount > $remaining + 0.000001) {
                $skipped[] = $poNumber . ' term ' . $termNo . ' melebihi sisa ' . number_format(max($remaining, 0), 0, ',', '.');
                continue;
            }

            $result = $this->MPO_Monitor->claimTerm((int) $lookup[$key]['id_term'], $invoiceDate, $amount, $this->session->userdata('id_user'), (int) ($lookup[$key]['id_allocation'] ?? 0));
            if (!empty($result['status'])) {
                $success++;
                $usedAmount[$key] = (float) ($usedAmount[$key] ?? 0) + $amount;
            } else {
                $skipped[] = $poNumber . ' term ' . $termNo . ': ' . ($result['message'] ?? 'gagal');
            }
        }

        $this->session->set_flashdata('status', $success > 0);
        $message = $success . ' invoice termin berhasil disimpan.';
        if (!empty($skipped)) {
            $message .= ' Skip: ' . implode('; ', array_slice($skipped, 0, 6));
            if (count($skipped) > 6) {
                $message .= '; +' . (count($skipped) - 6) . ' lainnya';
            }
        }
        $this->session->set_flashdata('error_log', $message);
        redirect('PO_Monitor');
    }
}
