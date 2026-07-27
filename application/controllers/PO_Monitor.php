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
        $selectedBowheer = $this->input->get('bowheer');
        $selectedSla = $this->input->get('sla');
        $comparisonFromMonth = $this->input->get('from_month') ?: date('Y-m');
        $comparisonToMonth = $this->input->get('to_month') ?: date('Y-m');
        $comparisonCumulative = in_array(strtolower((string) $this->input->get('cumulative')), ['1', 'true', 'yes'], true);

        if (!is_array($selectedBowheer)) {
            $selectedBowheer = empty($selectedBowheer) ? [] : [$selectedBowheer];
        }

        if (!is_array($selectedSla)) {
            $selectedSla = empty($selectedSla) ? [] : [$selectedSla];
        }

        $filteredPoList = [];
        $bowheerSummary = $this->MPO_Monitor->getPOSummaryByBowheer();
        $data['title'] = 'PO Monitoring';
        $data['poList'] = $filteredPoList;
        $data['bowheerSummary'] = $bowheerSummary;
        $data['batchInvoiceRows'] = [];
        $data['dashboardSummary'] = $this->MPO_Monitor->getDashboardSummary();
        $data['dashboardInitialTotals'] = $this->MPO_Monitor->getDashboardInitialTotals();
        $data['comparisonMatrix'] = $this->MPO_Monitor->getComparisonMatrix($comparisonFromMonth, $comparisonToMonth, 'month', false);
        $data['comparisonCumulative'] = $comparisonCumulative;
        $data['breakdownFilterOptions'] = [
            'projects' => [],
            'pics' => [],
            'regionals' => [],
            'areas' => [],
            'months' => [],
            'weeks' => []
        ];
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

    public function rebuild_dashboard_metrics()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->canManagePoImport()) {
            show_error('Forbidden', 403);
            return;
        }

        $this->MPO_Monitor->rebuildDashboardMetricsFromClaims(null);
        $this->session->set_flashdata('status', true);
        $this->session->set_flashdata('error_log', 'Dashboard Target PO berhasil direbuild dari claim invoice.');
        redirect('PO_Monitor');
    }

    public function backfill_ny_po_reference_groups()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->canManagePoImport()) {
            show_error('Forbidden', 403);
            return;
        }

        $summary = $this->MPO_Monitor->backfillNyPoReferenceGroupLinks((int) $this->session->userdata('id_user'));
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => empty($summary['errors']),
                'message' => empty($summary['errors'])
                    ? 'Backfill NY PO reference group berhasil.'
                    : 'Backfill selesai dengan sebagian error.',
                'summary' => $summary,
            ]));
    }

    public function backfill_myrep_po_monitor()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->canManagePoImport()) {
            show_error('Forbidden', 403);
            return;
        }

        $poNumbersRaw = (string) ($this->input->post('po_numbers') ?: $this->input->get('po_numbers'));
        $poNumbers = preg_split('/[\s,;]+/', $poNumbersRaw, -1, PREG_SPLIT_NO_EMPTY);
        $limit = (int) ($this->input->post('limit') ?: $this->input->get('limit') ?: 50);
        $offset = (int) ($this->input->post('offset') ?: $this->input->get('offset') ?: 0);
        $allowAll = in_array(strtolower((string) ($this->input->post('all') ?: $this->input->get('all'))), ['1', 'true', 'yes'], true);

        $summary = $this->MPO_Monitor->backfillPoMonitorFromMyRepHeaders(
            (array) $poNumbers,
            (int) $this->session->userdata('id_user'),
            $limit,
            $offset,
            $allowAll
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => !empty($summary['status']),
                'message' => !empty($summary['status'])
                    ? 'Backfill PO MyRep ke PO Monitor berhasil.'
                    : 'Backfill selesai dengan sebagian error.',
                'summary' => $summary,
            ]));
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

        $this->MPO_Monitor->syncMyRepClaimsForPoNumber((string) ($po['po_number'] ?? ''), (int) $this->session->userdata('id_user'));
        $po = $this->MPO_Monitor->getPOById((int) $id_po);

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
        $normalized = str_replace(["\xE2\x88\x92", "\xE2\x80\x93", "\xE2\x80\x94"], '-', $normalized);
        $isNegative = preg_match('/^\s*\(.*\)\s*$/', $normalized) === 1
            || preg_match('/^\s*-/', $normalized) === 1
            || preg_match('/-\s*$/', $normalized) === 1;
        $normalized = preg_replace('/\s+/', '', $normalized);
        $normalized = preg_replace('/[^\d,.\-]/', '', $normalized);
        $normalized = trim($normalized, '-');

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

        $amount = (float) preg_replace('/[^\d.]/', '', $normalized);
        return $isNegative && $amount > 0 ? -1 * $amount : $amount;
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
            'ny_po_ref',
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
            . ', replace: ' . (int) ($summary['replaced'] ?? 0)
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

    public function download_ny_po_reference()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $headers = ['NY PO REF', 'CURRENT PO', 'STATUS REF', 'BOWHEER', 'TYPE PROJECT', 'REGIONAL', 'KOTA PO', 'DETAIL PO', 'TERM', 'AMOUNT', 'PERIOD'];
        $rows = $this->MPO_Monitor->getNyPoReferenceRows();
        $fileName = 'ny-po-reference-' . date('Ymd-His') . '.xls';

        $html = '<html><head><meta charset="utf-8"><style>';
        $html .= 'body{font-family:Arial,sans-serif;}';
        $html .= 'table{border-collapse:collapse;}';
        $html .= 'th,td{border:1px solid #999;padding:5px 7px;font-size:10pt;mso-number-format:\@;vertical-align:top;}';
        $html .= 'th{background:#d9ead3;font-weight:bold;text-align:center;white-space:nowrap;}';
        $html .= '</style></head><body><table><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $html .= '</tr>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars((string) ($row['ny_po_ref'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($row['linked_po_number'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($row['pipeline_status'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($row['bowheer'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($row['type_project'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($row['regional'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($row['kota_po'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($row['detail_po'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($row['term_label'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($row['amount'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($row['period'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
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

    public function breakdown_datatable()
    {
        if (empty($this->session->userdata('id_user'))) {
            show_error('Unauthorized', 401);
            return;
        }

        $mode = $this->normalizeBreakdownMode($this->input->post('mode'));
        $draw = (int) ($this->input->post('draw') ?: 0);
        $start = max(0, (int) ($this->input->post('start') ?: 0));
        $length = (int) ($this->input->post('length') === null ? 10 : $this->input->post('length'));
        $filters = $this->collectBreakdownFilters();
        $search = trim((string) $this->input->post('breakdown_search'));

        $rawRows = $this->getBreakdownRowsForRequest($mode, $filters);
        $filteredRows = array_values(array_filter($rawRows, function ($row) use ($filters) {
            return $this->breakdownRawMatchesFilters($row, $filters);
        }));
        $groupedRows = $this->groupBreakdownRows($filteredRows, $mode);
        $recordsTotal = count($groupedRows);

        if ($search !== '') {
            $groupedRows = array_values(array_filter($groupedRows, function ($row) use ($search) {
                return $this->breakdownGroupedMatchesSearch($row, $search);
            }));
        }

        $recordsFiltered = count($groupedRows);
        $pageRows = $length < 0 ? $groupedRows : array_slice($groupedRows, $start, $length);
        $data = [];
        foreach ($pageRows as $index => $row) {
            $data[] = $this->formatBreakdownDatatableRow($row, $mode, $start + $index + 1);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'totals' => $this->formatBreakdownTotals($this->calculateBreakdownTotals($groupedRows), $recordsFiltered, count($pageRows)),
                'data' => $data
            ]));
    }

    public function breakdown_options()
    {
        if (empty($this->session->userdata('id_user'))) {
            show_error('Unauthorized', 401);
            return;
        }

        $filters = $this->collectBreakdownFilters();
        $except = trim((string) $this->input->post('except'));
        $rawRows = $this->getCachedBreakdownRows();
        $options = $this->buildBreakdownFilterOptions($rawRows, $filters, $except);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => true, 'options' => $options]));
    }

    public function breakdown_detail()
    {
        if (empty($this->session->userdata('id_user'))) {
            show_error('Unauthorized', 401);
            return;
        }

        $mode = $this->normalizeBreakdownMode($this->input->post('mode'));
        $key = trim((string) $this->input->post('key'));
        $filters = $this->collectBreakdownFilters();

        $rawRows = $this->getBreakdownRowsForRequest($mode, $filters);
        $rows = array_values(array_filter($rawRows, function ($row) use ($filters, $mode, $key) {
            return $this->breakdownRawMatchesFilters($row, $filters)
                && $this->breakdownRawMatchesGroup($row, $mode, $key);
        }));

        $title = '<span class="po-monitor-modal-eyebrow">Breakdown Detail</span>'
            . htmlspecialchars($this->breakdownModeLabel($mode) . ' - ' . $this->breakdownDetailLabel($rows, $mode, $key));

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'title' => $title,
                'html' => $this->renderBreakdownDetailHtml($rows)
            ]));
    }

    private function getCachedBreakdownRows()
    {
        static $rows = null;
        if ($rows === null) {
            $rows = $this->MPO_Monitor->getBreakdownTargetInvoiceRows(null, null);
        }

        return $rows;
    }

    private function getBreakdownRowsForRequest($mode, array $filters)
    {
        if ($this->useDashboardBreakdownRows($mode, $filters)) {
            return $this->MPO_Monitor->getDashboardTargetInvoiceBreakdownRows();
        }

        return $this->getCachedBreakdownRows();
    }

    private function useDashboardBreakdownRows($mode, array $filters)
    {
        if (!in_array($mode, ['project', 'pic'], true)) {
            return false;
        }

        if (!empty($filters['invoicedOnly'])) {
            return false;
        }

        foreach (['project', 'pic', 'regional', 'area', 'month', 'week'] as $field) {
            if (!empty($filters[$field])) {
                return false;
            }
        }

        return true;
    }

    private function normalizeBreakdownMode($mode)
    {
        $mode = trim((string) $mode);
        return in_array($mode, ['project', 'pic', 'regional', 'area', 'period', 'date'], true) ? $mode : 'project';
    }

    private function collectBreakdownFilters()
    {
        return [
            'project' => $this->postStringArray('project'),
            'pic' => $this->postStringArray('pic'),
            'regional' => $this->postStringArray('regional'),
            'area' => $this->postStringArray('area'),
            'month' => $this->postStringArray('month'),
            'week' => $this->postStringArray('week'),
            'invoicedOnly' => (bool) $this->input->post('invoiced_only')
        ];
    }

    private function postStringArray($key)
    {
        $value = $this->input->post($key);
        if ($value === null || $value === '') {
            return [];
        }
        if (!is_array($value)) {
            $value = [$value];
        }

        $result = [];
        foreach ($value as $item) {
            $item = trim((string) $item);
            if ($item !== '' && !in_array($item, $result, true)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private function breakdownRawMatchesFilters($row, array $filters, $except = '')
    {
        foreach (['project', 'pic', 'regional', 'area', 'month', 'week'] as $field) {
            if ($except === $field) {
                continue;
            }
            if (!empty($filters[$field]) && !in_array((string) ($row[$field] ?? ''), $filters[$field], true)) {
                return false;
            }
        }

        if ($except !== 'invoicedOnly' && !empty($filters['invoicedOnly']) && strtoupper((string) ($row['row_type'] ?? '')) !== 'ACHIEVED') {
            return false;
        }

        return true;
    }

    private function buildBreakdownFilterOptions(array $rows, array $filters, $except = '')
    {
        $config = [
            'projects' => ['id' => 'project', 'field' => 'project', 'label' => 'project'],
            'pics' => ['id' => 'pic', 'field' => 'pic', 'label' => 'pic'],
            'regionals' => ['id' => 'regional', 'field' => 'regional', 'label' => 'regional'],
            'areas' => ['id' => 'area', 'field' => 'area', 'label' => 'area'],
            'months' => ['id' => 'month', 'field' => 'month', 'label' => 'month_year_label'],
            'weeks' => ['id' => 'week', 'field' => 'week', 'label' => 'week']
        ];
        $options = [];

        foreach ($config as $key => $item) {
            $map = [];
            foreach ($rows as $row) {
                if (!$this->breakdownRawMatchesFilters($row, $filters, $item['id'] === $except ? $item['id'] : '')) {
                    continue;
                }

                $value = trim((string) ($row[$item['field']] ?? ''));
                if ($value === '' || $value === '-') {
                    continue;
                }
                $label = trim((string) ($row[$item['label']] ?? ''));
                if ($key === 'months' && $label === '') {
                    $label = $this->breakdownMonthYearLabel($value, (string) ($row['month_label'] ?? ''));
                }
                $map[$value] = $label ?: $value;
            }

            if ($key === 'months') {
                ksort($map, SORT_NATURAL | SORT_FLAG_CASE);
            } else {
                asort($map, SORT_NATURAL | SORT_FLAG_CASE);
            }
            $options[$key] = [];
            foreach ($map as $value => $label) {
                $options[$key][] = ['value' => $value, 'label' => $label];
            }
        }

        return $options;
    }

    private function breakdownMonthYearLabel($monthValue, $fallbackLabel = '')
    {
        $monthValue = trim((string) $monthValue);
        $fallbackLabel = trim((string) $fallbackLabel);
        $timestamp = preg_match('/^\d{4}-\d{2}$/', $monthValue) ? strtotime($monthValue . '-01') : false;
        if ($timestamp) {
            return strtoupper(date('Y - F', $timestamp));
        }

        return $fallbackLabel !== '' ? $fallbackLabel : $monthValue;
    }

    private function groupBreakdownRows(array $rows, $mode)
    {
        $groups = [];
        foreach ($rows as $row) {
            $meta = [];
            if ($mode === 'project') {
                $key = (string) ($row['project'] ?? '-');
                $label = $key;
                $meta['pic'] = (string) ($row['pic'] ?? '-');
            } elseif ($mode === 'pic') {
                $key = (string) ($row['pic'] ?? '-');
                $label = $key;
            } elseif ($mode === 'regional') {
                $key = (string) ($row['regional'] ?? '-');
                $label = $key;
            } elseif ($mode === 'area') {
                $key = (string) ($row['area'] ?? '-');
                $label = $key;
                $meta['regional'] = (string) ($row['regional'] ?? '-');
            } elseif ($mode === 'date') {
                $key = (string) (($row['date'] ?? '') ?: ($row['period_start'] ?? '-'));
                $label = (string) (($row['date_label'] ?? '') ?: $key);
                $meta['month'] = (string) (($row['month_label'] ?? '') ?: ($row['month'] ?? '-'));
                $meta['week'] = (string) ($row['week'] ?? '-');
            } else {
                $key = (string) (($row['month'] ?? '-') . '|' . ($row['week'] ?? '-'));
                $label = (string) (($row['month_label'] ?? '') ?: ($row['month'] ?? '-'));
                $meta['week'] = (string) ($row['week'] ?? '-');
            }

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'key' => $key,
                    'label' => $label,
                    'target' => 0,
                    'achieved' => 0,
                    'meta' => $meta,
                    'projectCount' => [],
                    'areaCount' => []
                ];
            }

            $groups[$key]['target'] += (float) ($row['target'] ?? 0);
            $groups[$key]['achieved'] += (float) ($row['achieved'] ?? 0);
            if (!empty($row['project'])) {
                $groups[$key]['projectCount'][(string) $row['project']] = true;
            }
            if (!empty($row['area']) && $row['area'] !== '-') {
                $groups[$key]['areaCount'][(string) $row['area']] = true;
            }
        }

        $result = [];
        foreach ($groups as $item) {
            $item['outstanding'] = max($item['target'] - $item['achieved'], 0);
            $item['percent'] = $this->breakdownPercent($item['target'], $item['achieved']);
            $item['totalProject'] = count($item['projectCount']);
            $item['totalArea'] = count($item['areaCount']);
            $result[] = $item;
        }

        usort($result, function ($a, $b) use ($mode) {
            if ($mode === 'date') {
                return strcmp((string) $a['key'], (string) $b['key']);
            }
            if ((float) $a['target'] === (float) $b['target']) {
                return strcasecmp((string) $a['label'], (string) $b['label']);
            }
            return (float) $a['target'] < (float) $b['target'] ? 1 : -1;
        });

        return $result;
    }

    private function breakdownGroupedMatchesSearch($row, $search)
    {
        $search = strtolower(trim((string) $search));
        if ($search === '') {
            return true;
        }

        $meta = is_array($row['meta'] ?? null) ? $row['meta'] : [];
        $haystack = strtolower(implode(' ', [
            $row['label'] ?? '',
            $meta['pic'] ?? '',
            $meta['regional'] ?? '',
            $meta['month'] ?? '',
            $meta['week'] ?? '',
            $row['target'] ?? '',
            $row['achieved'] ?? '',
            $row['outstanding'] ?? ''
        ]));

        return strpos($haystack, $search) !== false;
    }

    private function formatBreakdownDatatableRow($row, $mode, $number)
    {
        $target = (float) ($row['target'] ?? 0);
        $achieved = (float) ($row['achieved'] ?? 0);
        $outstanding = (float) ($row['outstanding'] ?? 0);
        $percent = (float) ($row['percent'] ?? 0);
        $progress = $this->renderBreakdownProgress($percent);
        $status = $this->renderBreakdownStatus($percent);
        $detail = $this->renderBreakdownDetailButton($mode, $row['key'] ?? '');
        $meta = is_array($row['meta'] ?? null) ? $row['meta'] : [];

        if ($mode === 'project') {
            return [$number, htmlspecialchars($row['label']), htmlspecialchars($meta['pic'] ?? '-'), $this->breakdownMoney($target), $this->breakdownMoney($achieved), $this->breakdownMoney($outstanding), $progress, $status, $detail];
        }
        if ($mode === 'pic' || $mode === 'regional') {
            return [$number, htmlspecialchars($row['label']), $this->breakdownMoney($target), $this->breakdownMoney($achieved), $this->breakdownMoney($outstanding), $progress, $status, $detail];
        }
        if ($mode === 'area') {
            return [$number, htmlspecialchars($meta['regional'] ?? '-'), htmlspecialchars($row['label']), $this->breakdownMoney($target), $this->breakdownMoney($achieved), $this->breakdownMoney($outstanding), $progress, $status, $detail];
        }
        if ($mode === 'date') {
            return [$number, htmlspecialchars($row['label']), htmlspecialchars($meta['month'] ?? '-'), htmlspecialchars($meta['week'] ?? '-'), $this->breakdownMoney($target), $this->breakdownMoney($achieved), $this->breakdownMoney($outstanding), $progress, number_format((int) ($row['totalProject'] ?? 0), 0, ',', '.'), number_format((int) ($row['totalArea'] ?? 0), 0, ',', '.'), $status, $detail];
        }

        return [$number, htmlspecialchars($row['label']), htmlspecialchars($meta['week'] ?? '-'), $this->breakdownMoney($target), $this->breakdownMoney($achieved), $this->breakdownMoney($outstanding), $progress, number_format((int) ($row['totalProject'] ?? 0), 0, ',', '.'), number_format((int) ($row['totalArea'] ?? 0), 0, ',', '.'), $status, $detail];
    }

    private function calculateBreakdownTotals(array $rows)
    {
        $target = 0;
        $achieved = 0;
        foreach ($rows as $row) {
            $target += (float) ($row['target'] ?? 0);
            $achieved += (float) ($row['achieved'] ?? 0);
        }

        return [
            'target' => $target,
            'achieved' => $achieved,
            'outstanding' => max($target - $achieved, 0),
            'percent' => $this->breakdownPercent($target, $achieved)
        ];
    }

    private function formatBreakdownTotals(array $totals, $recordsFiltered, $shown)
    {
        $info = $recordsFiltered > $shown ? ' <span class="text-muted font-weight-normal">(menampilkan ' . number_format($shown, 0, ',', '.') . ' dari ' . number_format($recordsFiltered, 0, ',', '.') . ')</span>' : '';
        return [
            'label' => 'Total' . $info,
            'target' => $this->breakdownMoney($totals['target'] ?? 0),
            'achieved' => $this->breakdownMoney($totals['achieved'] ?? 0),
            'outstanding' => $this->breakdownMoney($totals['outstanding'] ?? 0),
            'percent' => number_format((float) ($totals['percent'] ?? 0), 1, ',', '.') . ' %'
        ];
    }

    private function breakdownPercent($target, $achieved)
    {
        $target = (float) $target;
        $achieved = (float) $achieved;
        if ($target <= 0) {
            return $achieved > 0 ? 100 : 0;
        }

        return ($achieved / $target) * 100;
    }

    private function breakdownMoney($value)
    {
        return 'RP. ' . number_format((float) $value, 0, ',', '.');
    }

    private function renderBreakdownProgress($percent)
    {
        $width = min(max((float) $percent, 0), 100);
        return '<div class="po-breakdown-progress"><div class="po-breakdown-progress__track"><div class="po-breakdown-progress__bar" style="width:' . number_format($width, 1, '.', '') . '%"></div></div><span class="po-breakdown-progress__text">' . number_format((float) $percent, 1, ',', '.') . ' %</span></div>';
    }

    private function renderBreakdownStatus($percent)
    {
        $percent = (float) $percent;
        if ($percent >= 100) {
            $label = 'Tercapai';
            $className = 'po-monitor-sla-pill--aman';
        } elseif ($percent >= 80) {
            $label = 'On Track';
            $className = 'po-monitor-sla-pill--aman';
        } elseif ($percent >= 50) {
            $label = 'Perlu Dorong';
            $className = 'po-monitor-sla-pill--warning';
        } else {
            $label = 'Prioritas';
            $className = 'po-monitor-sla-pill--overdue';
        }

        return '<span class="po-monitor-sla-pill ' . $className . '">' . htmlspecialchars($label) . '</span>';
    }

    private function renderBreakdownDetailButton($mode, $key)
    {
        return '<button type="button" class="po-monitor-list-detail-btn js-po-breakdown-detail" title="Detail" data-breakdown-mode="' . htmlspecialchars($mode) . '" data-breakdown-key="' . htmlspecialchars((string) $key) . '"><i class="fas fa-eye"></i></button>';
    }

    private function breakdownRawMatchesGroup($row, $mode, $key)
    {
        $key = (string) $key;
        if ($mode === 'project') {
            return (string) ($row['project'] ?? '-') === $key;
        }
        if ($mode === 'pic') {
            return (string) ($row['pic'] ?? '-') === $key;
        }
        if ($mode === 'regional') {
            return (string) ($row['regional'] ?? '-') === $key;
        }
        if ($mode === 'area') {
            return (string) ($row['area'] ?? '-') === $key;
        }
        if ($mode === 'date') {
            return (string) (($row['date'] ?? '') ?: ($row['period_start'] ?? '-')) === $key;
        }

        return (string) (($row['month'] ?? '-') . '|' . ($row['week'] ?? '-')) === $key;
    }

    private function breakdownModeLabel($mode)
    {
        $labels = [
            'project' => 'Project',
            'pic' => 'PIC',
            'regional' => 'Regional',
            'area' => 'Kota / Area',
            'period' => 'Periode',
            'date' => 'Tanggal'
        ];

        return $labels[$mode] ?? 'Breakdown';
    }

    private function breakdownDetailLabel(array $rows, $mode, $key)
    {
        if (!empty($rows)) {
            $row = $rows[0];
            if ($mode === 'period') {
                return (($row['month_label'] ?? '') ?: ($row['month'] ?? '-')) . ' - ' . ($row['week'] ?? '-');
            }
            if ($mode === 'date') {
                return (($row['date_label'] ?? '') ?: ($row['date'] ?? $key));
            }
            $field = $mode === 'project' ? 'project' : ($mode === 'area' ? 'area' : $mode);
            return (string) ($row[$field] ?? $key);
        }

        return (string) $key;
    }

    private function renderBreakdownDetailHtml(array $rows)
    {
        if (empty($rows)) {
            return '<div class="alert alert-info mb-0">Tidak ada detail untuk filter ini.</div>';
        }

        $totalTarget = 0;
        $totalAchieved = 0;
        foreach ($rows as $row) {
            $totalTarget += (float) ($row['target'] ?? 0);
            $totalAchieved += (float) ($row['achieved'] ?? 0);
        }
        $totalOutstanding = max($totalTarget - $totalAchieved, 0);

        $html = '<div class="po-monitor-modal-stat-grid mb-3">';
        $html .= '<div class="po-monitor-modal-stat"><span class="po-monitor-modal-stat__label">Target</span><span class="po-monitor-modal-stat__value">' . $this->breakdownMoney($totalTarget) . '</span></div>';
        $html .= '<div class="po-monitor-modal-stat po-monitor-modal-stat--green"><span class="po-monitor-modal-stat__label">Achieved</span><span class="po-monitor-modal-stat__value">' . $this->breakdownMoney($totalAchieved) . '</span></div>';
        $html .= '<div class="po-monitor-modal-stat po-monitor-modal-stat--amber"><span class="po-monitor-modal-stat__label">Outstanding</span><span class="po-monitor-modal-stat__value">' . $this->breakdownMoney($totalOutstanding) . '</span></div>';
        $html .= '<div class="po-monitor-modal-stat"><span class="po-monitor-modal-stat__label">Rows</span><span class="po-monitor-modal-stat__value">' . number_format(count($rows), 0, ',', '.') . '</span></div>';
        $html .= '</div>';

        $html .= '<div class="table-responsive"><table class="table table-sm po-monitor-detail-table mb-0"><thead><tr>';
        $html .= '<th>No</th><th>Type</th><th>Project</th><th>PIC</th><th>PO</th><th>Sub PO</th><th>Regional</th><th>Area</th><th>Periode / Tanggal</th><th>Detail</th><th>Remarks</th><th class="text-right">Amount</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($rows as $index => $row) {
            $isAchieved = strtoupper((string) ($row['row_type'] ?? '')) === 'ACHIEVED';
            $type = $isAchieved ? 'Achieved' : 'Target';
            $typeClass = $isAchieved ? 'badge-success' : 'badge-primary';
            $amount = $isAchieved ? (float) ($row['achieved'] ?? 0) : (float) ($row['target'] ?? 0);
            $period = ($row['date_label'] ?? '') ?: (($row['month_label'] ?? '') ?: (($row['period_start'] ?? '') ?: '-'));

            $html .= '<tr>';
            $html .= '<td>' . ($index + 1) . '</td>';
            $html .= '<td><span class="badge ' . $typeClass . '">' . $type . '</span></td>';
            $html .= '<td>' . htmlspecialchars($row['project'] ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['pic'] ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['po_number'] ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['sub_po'] ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['regional'] ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['area'] ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($period) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['detail_po'] ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['remarks'] ?? '-') . '</td>';
            $html .= '<td class="text-right">' . $this->breakdownMoney($amount) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody><tfoot><tr><th colspan="11">Total</th><th class="text-right">' . $this->breakdownMoney($totalTarget + $totalAchieved) . '</th></tr></tfoot></table></div>';

        return $html;
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
        $fromMonth = $this->input->post('from_month') ?: null;
        $toMonth = $this->input->post('to_month') ?: null;

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

        if (strtoupper(trim((string) $project['bowheer'])) === 'PT EMR - NRO') {
            $syncMonth = $this->comparisonSyncMonthKey($periodKey, $groupBy);
            if ($periodKey === '__total__') {
                $this->MPO_Monitor->syncEmrNroComparisonClaims($fromMonth, $toMonth, (int) $this->session->userdata('id_user'));
            } elseif ($syncMonth !== '') {
                $this->MPO_Monitor->syncEmrNroComparisonClaims($syncMonth, $syncMonth, (int) $this->session->userdata('id_user'));
            }
        }

        $rows = $this->MPO_Monitor->getComparisonDetail($idBowheer, $periodKey, $groupBy, $type, $fromMonth, $toMonth);
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

    public function comparison_week_table()
    {
        if (empty($this->session->userdata('id_user'))) {
            show_error('Unauthorized', 401);
            return;
        }

        $fromMonth = $this->input->post('from_month') ?: $this->input->get('from_month') ?: date('Y-m');
        $toMonth = $this->input->post('to_month') ?: $this->input->get('to_month') ?: date('Y-m');
        $matrix = $this->MPO_Monitor->getComparisonMatrix($fromMonth, $toMonth, 'week', false);
        $html = $this->load->view('PO_Monitor/_comparison_table', [
            'matrix' => $matrix,
            'groupBy' => 'week',
            'tableId' => 'table_po_target_invoice_compare_week',
        ], true);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'html' => $html,
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
        if ((string) $periodKey === '__total__') {
            return 'Total Target';
        }

        if ($groupBy === 'week' && preg_match('/^(\d{4})-W(\d{2})$/', $periodKey, $match)) {
            return 'Week ' . (int) $match[2] . ' ' . $match[1];
        }

        if (preg_match('/^\d{4}-\d{2}$/', $periodKey)) {
            return $this->formatIndonesianMonth($periodKey);
        }

        return $periodKey;
    }

    private function comparisonSyncMonthKey($periodKey, $groupBy)
    {
        if ($groupBy !== 'week') {
            return preg_match('/^\d{4}-\d{2}$/', $periodKey) ? $periodKey : '';
        }

        if (!preg_match('/^(\d{4})-W(\d{1,2})$/', $periodKey, $match)) {
            return '';
        }

        $year = (int) $match[1];
        $week = (int) $match[2];
        $jan1 = new DateTime($year . '-01-01');
        $start = clone $jan1;
        $start->modify('-' . (int) $jan1->format('w') . ' days');
        $start->modify('+' . (($week - 1) * 7) . ' days');

        $counts = [];
        for ($i = 0; $i < 7; $i++) {
            $day = clone $start;
            $day->modify('+' . $i . ' days');
            $key = $day->format('Y-m');
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        arsort($counts);
        return (string) key($counts);
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
            'acceleration_target_2026' => $this->dashboardNumber(($totals['all_po'] ?? 0) + ($totals['done_inv_2026'] ?? 0) + ($totals['outs_2026_on_target'] ?? 0) + ($totals['ny_po_on_target_2026'] ?? 0)),
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
            $sla = strtoupper(trim((string) ($row['sla'] ?: 'AMAN')));
            $slaClass = 'po-monitor-sla-pill--aman';
            if ($sla === 'WARNING') {
                $slaClass = 'po-monitor-sla-pill--warning';
            } elseif ($sla === 'OVERDUE') {
                $slaClass = 'po-monitor-sla-pill--overdue';
            }

            $data[] = [
                $start + $index + 1,
                htmlspecialchars($row['po_number'] ?: '-'),
                htmlspecialchars($row['po_date'] ?: '-'),
                '<span class="po-monitor-money">RP. ' . number_format((float) $row['current_release_value'], 0, ',', '.') . '</span>',
                '<span class="po-monitor-money">RP. ' . number_format((float) $row['total_invoiced'], 0, ',', '.') . '</span>',
                '<span class="po-monitor-money">RP. ' . number_format((float) $row['remaining'], 0, ',', '.') . '</span>',
                '<span class="po-monitor-sla-pill ' . $slaClass . '">' . htmlspecialchars($sla) . '</span>',
                '<a href="' . site_url('PO_Monitor/detail/' . (int) $row['id_po']) . '" class="po-monitor-list-detail-btn" title="Detail PO" aria-label="Detail PO"><i class="fas fa-eye"></i></a>'
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

        if (empty($invoiceDate)) {
            $this->session->set_flashdata('status', false);
            $this->session->set_flashdata('error_log', 'Tanggal invoice general wajib diisi.');
            redirect('PO_Monitor');
            return;
        }

        if (!is_array($poNumbers) || !is_array($termNos) || !is_array($amounts)) {
            $this->session->set_flashdata('status', false);
            $this->session->set_flashdata('error_log', 'Data batch invoice tidak lengkap.');
            redirect('PO_Monitor');
            return;
        }

        $lookup = [];
        foreach ($this->MPO_Monitor->getBatchInvoiceTerminRowsByPoNumbers($poNumbers) as $row) {
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

    public function batch_invoice_lookup()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
            return;
        }

        if (strtoupper((string) $this->input->method(true)) !== 'POST') {
            show_404();
            return;
        }

        $poNumbers = $this->input->post('po_numbers');
        if (!is_array($poNumbers)) {
            $poNumbers = [$this->input->post('po_number')];
        }

        $lookup = [];
        foreach ($this->MPO_Monitor->getBatchInvoiceTerminRowsByPoNumbers($poNumbers) as $row) {
            $poNumber = trim((string) ($row['po_number'] ?? ''));
            $termNo = (int) ($row['term_index'] ?? 0);
            if ($poNumber === '' || $termNo <= 0) {
                continue;
            }

            $poKey = strtoupper($poNumber);
            if (!isset($lookup[$poKey])) {
                $lookup[$poKey] = [
                    'po_number' => $poNumber,
                    'nama_bowheer' => (string) ($row['nama_bowheer'] ?? ''),
                    'terms' => []
                ];
            }
            $lookup[$poKey]['terms'][$termNo] = [
                'id_term' => (int) ($row['id_term'] ?? 0),
                'term_value' => (float) ($row['term_value'] ?? 0),
                'invoiced_amount' => (float) ($row['invoiced_amount'] ?? 0),
                'remaining' => (float) ($row['remaining'] ?? 0),
                'invoice_date' => (string) ($row['invoice_date'] ?? '')
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => true, 'lookup' => $lookup]));
    }
}
