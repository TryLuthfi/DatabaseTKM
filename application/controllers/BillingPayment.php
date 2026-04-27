<?php
defined('BASEPATH') or exit('No direct script access allowed');

class BillingPayment extends CI_Controller
{
    private function normalizeAmount($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = trim((string) $value);
        $normalized = str_replace(' ', '', $normalized);

        $lastDot = strrpos($normalized, '.');
        $lastComma = strrpos($normalized, ',');

        if ($lastDot !== false && $lastComma !== false) {
            if ($lastDot > $lastComma) {
                $normalized = str_replace(',', '', $normalized);
            } else {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            }
        } elseif ($lastComma !== false) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } else {
            $parts = explode('.', $normalized);
            if (count($parts) > 2) {
                $decimalPart = array_pop($parts);
                $normalized = implode('', $parts) . '.' . $decimalPart;
            }
        }

        return (float) preg_replace('/[^\d.\-]/', '', $normalized);
    }

    public function __construct()
    {
        parent::__construct();
        enforce_bilco_access();
        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->model('MBillingPayment');
    }

    private function normalizeDateTime($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && class_exists('PHPExcel_Shared_Date')) {
            try {
                return PHPExcel_Shared_Date::ExcelToPHPObject($value)->format('Y-m-d H:i:s');
            } catch (Exception $e) {
            }
        }

        $normalized = trim((string) $value);
        $normalized = str_replace('/', '-', $normalized);
        $timestamp = strtotime($normalized);

        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function findBowheerIdByName($name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $row = $this->db
            ->select('id_bowheer')
            ->where('nama_bowheer', $name)
            ->get('tb_master_bowheer_bilco')
            ->row_array();

        return $row ? (int) $row['id_bowheer'] : null;
    }

    private function getLastUpdateValue()
    {
        return date('Y-m-d');
    }

    private function validateInvoiceRows($rawRows)
    {
        $preparedRows = [];
        $errors = [];
        $invoiceNumbers = [];
        $currentUserId = (int) $this->session->userdata('id_user');

        foreach ($rawRows as $index => $rawRow) {
            $rowNumber = $index + 1;
            $idBowheer = isset($rawRow['id_bowheer']) ? (int) $rawRow['id_bowheer'] : 0;

            if ($idBowheer <= 0 && !empty($rawRow['nama_bowheer'])) {
                $idBowheer = $this->findBowheerIdByName($rawRow['nama_bowheer']);
            }

            $noInvoice = trim((string) ($rawRow['no_invoice'] ?? ''));
            $tglCreateInvoice = $this->normalizeDateTime($rawRow['tgl_create_invoice'] ?? null);
            $tglSubmitInvoice = $this->normalizeDateTime($rawRow['tgl_submit_invoice'] ?? null);
            $poNumber = trim((string) ($rawRow['po_number'] ?? ''));
            $poTgl = $this->normalizeDateTime($rawRow['po_tgl'] ?? null);
            $invoicePriceEst = $this->normalizeAmount($rawRow['invoice_price_est'] ?? 0);
            $invoicePriceNett = $this->normalizeAmount($rawRow['invoice_price_nett'] ?? 0);
            $regionalPayment = trim((string) ($rawRow['regional_payment'] ?? ''));
            $areaPayment = trim((string) ($rawRow['area_payment'] ?? ''));
            $deskripsiPayment = trim((string) ($rawRow['deskripsi_payment'] ?? ''));

            $rowErrors = [];

            if ($idBowheer <= 0) {
                $rowErrors[] = 'Bowheer wajib diisi';
            }

            if ($noInvoice === '') {
                $rowErrors[] = 'No invoice wajib diisi';
            }

            if ($tglSubmitInvoice === null) {
                $rowErrors[] = 'Tanggal submit invoice tidak valid';
            }

            if ($invoicePriceNett <= 0) {
                $rowErrors[] = 'Invoice nett harus lebih besar dari 0';
            }

            if ($noInvoice !== '') {
                $invoiceKey = strtolower($noInvoice);
                if (isset($invoiceNumbers[$invoiceKey])) {
                    $rowErrors[] = 'No invoice duplikat dalam batch';
                } else {
                    $invoiceNumbers[$invoiceKey] = true;
                }
            }

            $preparedRows[] = [
                'row_number' => $rowNumber,
                'id_bowheer' => $idBowheer,
                'no_invoice' => $noInvoice,
                'tgl_create_invoice' => $tglCreateInvoice ?: $tglSubmitInvoice,
                'tgl_submit_invoice' => $tglSubmitInvoice,
                'tgl_payment_invoice' => null,
                'po_number' => $poNumber,
                'po_tgl' => $poTgl,
                'invoice_price_est' => $invoicePriceEst,
                'invoice_price_nett' => $invoicePriceNett,
                'invoice_price_payment' => null,
                'status_invoice' => 'open',
                'date_insert' => date('Y-m-d'),
                'last_update' => $this->getLastUpdateValue(),
                'id_user' => $currentUserId ?: null,
                'area_payment' => $areaPayment,
                'regional_payment' => $regionalPayment,
                'deskripsi_payment' => $deskripsiPayment,
                'errors' => $rowErrors
            ];
        }

        $invoiceList = array_values(array_filter(array_column($preparedRows, 'no_invoice')));
        if (!empty($invoiceList)) {
            $existingInvoices = $this->db
                ->select('no_invoice')
                ->where_in('no_invoice', $invoiceList)
                ->get('tb_billingpayment')
                ->result_array();

            $existingMap = [];
            foreach ($existingInvoices as $existing) {
                $existingMap[strtolower($existing['no_invoice'])] = true;
            }

            foreach ($preparedRows as &$preparedRow) {
                if ($preparedRow['no_invoice'] !== '' && isset($existingMap[strtolower($preparedRow['no_invoice'])])) {
                    $preparedRow['errors'][] = 'No invoice sudah ada di database';
                }
            }
            unset($preparedRow);
        }

        foreach ($preparedRows as $preparedRow) {
            if (!empty($preparedRow['errors'])) {
                $errors[] = [
                    'row' => $preparedRow['row_number'],
                    'message' => implode(', ', array_unique($preparedRow['errors']))
                ];
            }
        }

        $validRows = [];
        foreach ($preparedRows as $preparedRow) {
            if (empty($preparedRow['errors'])) {
                unset($preparedRow['row_number'], $preparedRow['errors']);
                $validRows[] = $preparedRow;
            }
        }

        return [
            'rows' => $preparedRows,
            'valid_rows' => $validRows,
            'errors' => $errors
        ];
    }

    private function parseExcelHeader($header)
    {
        $header = strtolower(trim((string) $header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        $header = trim($header, '_');

        $aliases = [
            'id_bowheer' => ['id_bowheer', 'bowheer_id', 'id_project'],
            'nama_bowheer' => ['nama_bowheer', 'bowheer', 'project', 'project_bowheer', 'nama_project'],
            'no_invoice' => ['no_invoice', 'invoice', 'nomor_invoice'],
            'tgl_create_invoice' => ['tgl_create_invoice', 'tanggal_create_invoice', 'create_invoice_date'],
            'tgl_submit_invoice' => ['tgl_submit_invoice', 'tanggal_submit_invoice', 'submit_invoice_date'],
            'po_number' => ['po_number', 'nomor_po', 'po'],
            'po_tgl' => ['po_tgl', 'tanggal_po', 'po_date'],
            'invoice_price_est' => ['invoice_price_est', 'invoice_est', 'nilai_invoice_est'],
            'invoice_price_nett' => ['invoice_price_nett', 'invoice_nett', 'nilai_invoice_nett'],
            'regional_payment' => ['regional_payment', 'regional'],
            'area_payment' => ['area_payment', 'area', 'kota'],
            'deskripsi_payment' => ['deskripsi_payment', 'deskripsi', 'keterangan']
        ];

        foreach ($aliases as $field => $options) {
            if (in_array($header, $options, true)) {
                return $field;
            }
        }

        return null;
    }

    private function loadPHPExcel()
    {
        if (!class_exists('PHPExcel')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        }
    }

    private function readCsvSheetData($filePath)
    {
        $rows = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            return $rows;
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return $rows;
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!empty($data)) {
                if (isset($data[0])) {
                    $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
                }
                $rows[] = $data;
            }
        }

        fclose($handle);

        $sheetData = [];
        foreach ($rows as $rowIndex => $row) {
            $sheetRow = [];
            foreach ($row as $colIndex => $value) {
                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($colIndex);
                $sheetRow[$columnLetter] = $value;
            }
            $sheetData[$rowIndex + 1] = $sheetRow;
        }

        return $sheetData;
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'RINCIAN INVOICE';
            $data['judul'] = 'RINCIAN INVOICE';
            $data['getTargetPriorityBowheer'] = $this->MBillingPayment->getTargetPriorityBowheer();
            $data['getAllData'] = $this->MBillingPayment->getAllData();
            $data['masterBowheerList'] = $this->db
                ->select('id_bowheer, nama_bowheer')
                ->from('tb_master_bowheer_bilco')
                ->order_by('nama_bowheer', 'ASC')
                ->get()
                ->result_array();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('BillingPayment/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function getFilteredBillingPaymentAjax()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $bowheer = $this->input->post('bowheer');
        $regional = $this->input->post('regional');
        $city = $this->input->post('city');
        $priority = $this->input->post('priority');
        $statusInvoice = $this->input->post('status_invoice');

        if ($statusInvoice === 'all') {
            $statusInvoice = null;
        }

        $data = $this->MBillingPayment->getFilteredBillingPayment($bowheer, $regional, $city, $priority, $statusInvoice);
        $summary = $this->MBillingPayment->getOutstandingSummary($bowheer, $regional, $city, $priority);

        // Tentukan kolom yang tampil berdasarkan filter
        $priceHeader = $statusInvoice === 'partial' ? 'Outstanding Balance' : 'Price';
        $columns = ['No', 'Bowheer', 'Invoice', $priceHeader, 'Regional', 'Area', 'Date Submit', 'Due Date', 'Aging', "Priority", "PO Number", "Status Invoice", "Action"];

        echo json_encode([
            'columns' => $columns,
            'data' => $data,
            'summary' => $summary
        ]);

        log_message('debug', 'Last Query: ' . $this->db->last_query());
    }

    public function updateBillingInvoice()
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        $idBilling = (int) $this->input->post('id_billing');
        $idBowheer = (int) $this->input->post('id_bowheer');
        $noInvoice = trim((string) $this->input->post('no_invoice'));
        $tglCreateInvoice = $this->normalizeDateTime($this->input->post('tgl_create_invoice'));
        $tglSubmitInvoice = $this->normalizeDateTime($this->input->post('tgl_submit_invoice'));
        $tglPaymentInvoice = $this->normalizeDateTime($this->input->post('tgl_payment_invoice'));
        $poNumber = trim((string) $this->input->post('po_number'));
        $poTgl = $this->normalizeDateTime($this->input->post('po_tgl'));
        $invoicePriceEst = $this->normalizeAmount($this->input->post('invoice_price_est'));
        $invoicePriceNett = $this->normalizeAmount($this->input->post('invoice_price_nett'));
        $invoicePricePayment = $this->normalizeAmount($this->input->post('invoice_price_payment'));
        $statusInvoice = trim((string) $this->input->post('status_invoice'));
        $areaPayment = trim((string) $this->input->post('area_payment'));
        $regionalPayment = trim((string) $this->input->post('regional_payment'));
        $deskripsiPayment = trim((string) $this->input->post('deskripsi_payment'));

        if ($idBilling <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'ID invoice tidak valid'
            ]);
            return;
        }

        $existing = $this->db
            ->select('id_billing, status_invoice')
            ->get_where('tb_billingpayment', ['id_billing' => $idBilling])
            ->row_array();

        if (!$existing) {
            echo json_encode([
                'status' => false,
                'message' => 'Data invoice tidak ditemukan'
            ]);
            return;
        }

        if ($idBowheer <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Bowheer wajib dipilih'
            ]);
            return;
        }

        if ($noInvoice === '') {
            echo json_encode([
                'status' => false,
                'message' => 'No invoice wajib diisi'
            ]);
            return;
        }

        if ($tglSubmitInvoice === null) {
            echo json_encode([
                'status' => false,
                'message' => 'Tanggal submit invoice tidak valid'
            ]);
            return;
        }

        if ($invoicePriceNett <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Invoice nett harus lebih besar dari 0'
            ]);
            return;
        }

        $duplicate = $this->db
            ->select('id_billing')
            ->from('tb_billingpayment')
            ->where("LOWER(no_invoice) = " . $this->db->escape(strtolower($noInvoice)), null, false)
            ->where('id_billing !=', $idBilling)
            ->get()
            ->row_array();

        if ($duplicate) {
            echo json_encode([
                'status' => false,
                'message' => 'No invoice sudah ada di database'
            ]);
            return;
        }

        if ($invoicePricePayment > $invoicePriceNett) {
            echo json_encode([
                'status' => false,
                'message' => 'Payment tidak boleh melebihi invoice nett'
            ]);
            return;
        }

        if (!in_array($statusInvoice, ['open', 'partial', 'paid', 'reject'], true)) {
            if ($invoicePricePayment <= 0) {
                $statusInvoice = 'open';
            } elseif ($invoicePricePayment < $invoicePriceNett) {
                $statusInvoice = 'partial';
            } else {
                $statusInvoice = 'paid';
            }
        }

        if ($statusInvoice === 'open') {
            $invoicePricePayment = null;
            $tglPaymentInvoice = null;
        } elseif ($statusInvoice === 'reject') {
            $invoicePricePayment = null;
            $tglPaymentInvoice = null;
        } elseif ($statusInvoice === 'partial') {
            if ($invoicePricePayment <= 0 || $invoicePricePayment >= $invoicePriceNett) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Status partial harus memiliki payment lebih dari 0 dan kurang dari invoice nett'
                ]);
                return;
            }

            if ($tglPaymentInvoice === null) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Tanggal payment wajib diisi untuk status partial'
                ]);
                return;
            }
        } elseif ($statusInvoice === 'paid') {
            if ($invoicePricePayment <= 0) {
                $invoicePricePayment = $invoicePriceNett;
            }

            if ((float) $invoicePricePayment !== (float) $invoicePriceNett) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Status paid harus memiliki payment yang sama dengan invoice nett'
                ]);
                return;
            }

            if ($tglPaymentInvoice === null) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Tanggal payment wajib diisi untuk status paid'
                ]);
                return;
            }
        }

        $payload = [
            'id_bowheer' => $idBowheer,
            'no_invoice' => $noInvoice,
            'tgl_create_invoice' => $tglCreateInvoice ?: $tglSubmitInvoice,
            'tgl_submit_invoice' => $tglSubmitInvoice,
            'tgl_payment_invoice' => $tglPaymentInvoice,
            'po_number' => $poNumber,
            'po_tgl' => $poTgl,
            'invoice_price_est' => $invoicePriceEst,
            'invoice_price_nett' => $invoicePriceNett,
            'invoice_price_payment' => $invoicePricePayment,
            'status_invoice' => $statusInvoice,
            'last_update' => $this->getLastUpdateValue(),
            'area_payment' => $areaPayment,
            'regional_payment' => $regionalPayment,
            'deskripsi_payment' => $deskripsiPayment
        ];

        $this->db->where('id_billing', $idBilling);
        $updated = $this->db->update('tb_billingpayment', $payload);

        if ($updated) {
            echo json_encode([
                'status' => true,
                'message' => 'Invoice berhasil diperbarui'
            ]);
            return;
        }

        echo json_encode([
            'status' => false,
            'message' => 'Gagal memperbarui invoice'
        ]);
    }

    public function getOpenInvoices()
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        $keyword = $this->input->get('q');

        $this->db->select('tbp.id_billing, tbp.no_invoice, tbp.po_number, tbp.invoice_price_nett, tmbi.nama_bowheer');
        $this->db->from('tb_billingpayment tbp');
        $this->db->join('tb_master_bowheer_bilco tmbi', 'tbp.id_bowheer = tmbi.id_bowheer', 'left');
        $this->db->where('tbp.status_invoice', 'open');

        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db->like('tbp.no_invoice', $keyword);
            $this->db->or_like('tbp.po_number', $keyword);
            $this->db->or_like('tmbi.nama_bowheer', $keyword);
            $this->db->group_end();
        }

        $this->db->order_by('tbp.no_invoice', 'ASC');
        $this->db->limit(20);

        $query = $this->db->get()->result_array();

        $results = [];
        foreach ($query as $row) {
            $results[] = [
                'id' => $row['id_billing'],
                'text' => $row['no_invoice'],
                'no_invoice' => $row['no_invoice'],
                'po_number' => $row['po_number'],
                'nama_bowheer' => $row['nama_bowheer'],
                'invoice_price_nett' => $row['invoice_price_nett']
            ];
        }

        echo json_encode(['results' => $results]);
    }

    public function downloadBillingReport()
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        $bowheer = $this->input->get('bowheer');
        $regional = $this->input->get('regional');
        $city = $this->input->get('city');
        $priority = $this->input->get('priority');
        $statusInvoice = $this->input->get('status_invoice');

        if ($statusInvoice === 'all') {
            $statusInvoice = null;
        }

        $rows = $this->MBillingPayment->getFilteredBillingPayment($bowheer, $regional, $city, $priority, $statusInvoice);
        $filename = 'billing_report_' . date('Ymd_His') . '.xls';

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1">';
        echo '<tr><th colspan="12" style="font-weight:bold;font-size:16px;">Billing Payment Report</th></tr>';
        echo '<tr><th colspan="12">Generated At: ' . date('Y-m-d H:i:s') . '</th></tr>';
        echo '<tr>';
        echo '<th>No</th>';
        echo '<th>Bowheer</th>';
        echo '<th>No Invoice</th>';
        echo '<th>Invoice Price</th>';
        echo '<th>Regional</th>';
        echo '<th>Area</th>';
        echo '<th>Date Submit</th>';
        echo '<th>Due Date</th>';
        echo '<th>Aging</th>';
        echo '<th>Priority</th>';
        echo '<th>PO Number</th>';
        echo '<th>Status Invoice</th>';
        echo '</tr>';

        $total = 0;
        foreach ($rows as $index => $row) {
            $total += (float) $row['invoice_price_nett'];
            echo '<tr>';
            echo '<td>' . ($index + 1) . '</td>';
            echo '<td>' . htmlspecialchars((string) $row['nama_bowheer']) . '</td>';
            echo '<td>' . htmlspecialchars((string) $row['no_invoice']) . '</td>';
            echo '<td style="mso-number-format:\'#,##0.00\'">' . number_format((float) $row['invoice_price_nett'], 2, '.', '') . '</td>';
            echo '<td>' . htmlspecialchars((string) $row['regional_payment']) . '</td>';
            echo '<td>' . htmlspecialchars((string) $row['area_payment']) . '</td>';
            echo '<td>' . htmlspecialchars((string) $row['tgl_submit_invoice']) . '</td>';
            echo '<td>' . htmlspecialchars((string) $row['tgl_jatuh_tempo']) . '</td>';
            echo '<td>' . htmlspecialchars((string) $row['umur_invoice']) . '</td>';
            echo '<td>' . htmlspecialchars((string) $row['priority']) . '</td>';
            echo '<td>' . htmlspecialchars((string) $row['po_number']) . '</td>';
            echo '<td>' . htmlspecialchars((string) $row['status_invoice']) . '</td>';
            echo '</tr>';
        }

        echo '<tr>';
        echo '<th colspan="3" style="text-align:right;">Total</th>';
        echo '<th style="mso-number-format:\'#,##0.00\'">' . number_format($total, 2, '.', '') . '</th>';
        echo '<th colspan="8"></th>';
        echo '</tr>';
        echo '</table>';
        echo '</body></html>';
        exit;
    }

    public function saveBatchPayment()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $idBilling = $this->input->post('id_billing');
        $paymentPrice = $this->input->post('invoice_price_payment');
        $paymentDate = $this->input->post('tgl_payment_invoice');
        $statusInvoice = $this->input->post('status_invoice');

        if (empty($idBilling) || !is_array($idBilling)) {
            echo json_encode([
                'status' => false,
                'message' => 'Data invoice tidak ditemukan'
            ]);
            return;
        }

        $this->db->trans_start();

        for ($i = 0; $i < count($idBilling); $i++) {
            $id = (int) $idBilling[$i];
            $price = $this->normalizeAmount($paymentPrice[$i]);
            $date = $paymentDate[$i];
            $status = $statusInvoice[$i];

            if (empty($id) || empty($status)) {
                continue;
            }

            $billing = $this->db
                ->select('invoice_price_nett')
                ->get_where('tb_billingpayment', ['id_billing' => $id, 'status_invoice' => 'open'])
                ->row_array();

            if (!$billing) {
                continue;
            }

            $invoicePrice = (float) $billing['invoice_price_nett'];

            if ($price > $invoicePrice) {
                $price = $invoicePrice;
            }

            if ($status === 'reject') {
                $this->db->where('id_billing', $id);
                $this->db->where('status_invoice', 'open');
                $this->db->update('tb_billingpayment', [
                    'invoice_price_payment' => null,
                    'tgl_payment_invoice' => null,
                    'status_invoice' => 'reject',
                    'last_update' => $this->getLastUpdateValue()
                ]);
                continue;
            }

            if (empty($price) || empty($date)) {
                continue;
            }

            if (!in_array($status, ['partial', 'paid'], true)) {
                $status = 'paid';
            }

            if ($price >= $invoicePrice) {
                $price = $invoicePrice;
                $status = 'paid';
            }

            $this->db->where('id_billing', $id);
            $this->db->where('status_invoice', 'open');
            $this->db->update('tb_billingpayment', [
                'invoice_price_payment' => $price,
                'tgl_payment_invoice' => $date,
                'status_invoice' => $status,
                'last_update' => $this->getLastUpdateValue()
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            echo json_encode([
                'status' => true,
                'message' => 'Pembayaran berhasil disimpan'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menyimpan pembayaran'
            ]);
        }
    }

    public function updatePartialPayment()
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        $idBilling = (int) $this->input->post('id_billing');
        $paymentPrice = $this->normalizeAmount($this->input->post('invoice_price_payment'));
        $paymentDate = $this->input->post('tgl_payment_invoice');
        $statusInvoice = $this->input->post('status_invoice');

        if (empty($idBilling) || empty($paymentPrice) || empty($paymentDate)) {
            echo json_encode([
                'status' => false,
                'message' => 'Data edit partial belum lengkap'
            ]);
            return;
        }

        $billing = $this->db
            ->select('id_billing, invoice_price_nett, invoice_price_payment, status_invoice')
            ->get_where('tb_billingpayment', [
                'id_billing' => $idBilling,
                'status_invoice' => 'partial'
            ])
            ->row_array();

        if (!$billing) {
            echo json_encode([
                'status' => false,
                'message' => 'Invoice partial tidak ditemukan'
            ]);
            return;
        }

        $invoicePrice = (float) $billing['invoice_price_nett'];

        if ($paymentPrice > $invoicePrice) {
            echo json_encode([
                'status' => false,
                'message' => 'Nominal pembayaran tidak boleh melebihi nilai invoice'
            ]);
            return;
        }

        if (!in_array($statusInvoice, ['partial', 'paid', 'reject'])) {
            $statusInvoice = 'partial';
        }

        if ($statusInvoice === 'reject') {
            $this->db->where('id_billing', $idBilling);
            $updated = $this->db->update('tb_billingpayment', [
                'invoice_price_payment' => null,
                'tgl_payment_invoice' => null,
                'status_invoice' => 'reject',
                'last_update' => $this->getLastUpdateValue()
            ]);

            if ($updated) {
                echo json_encode([
                    'status' => true,
                    'message' => 'Data partial payment berhasil diubah menjadi reject'
                ]);
                return;
            }

            echo json_encode([
                'status' => false,
                'message' => 'Gagal memperbarui data partial payment'
            ]);
            return;
        }

        if ($paymentPrice >= $invoicePrice) {
            $paymentPrice = $invoicePrice;
            $statusInvoice = 'paid';
        }

        if ($statusInvoice === 'paid' && $paymentPrice < $invoicePrice) {
            echo json_encode([
                'status' => false,
                'message' => 'Status paid hanya bisa dipilih jika pembayaran sudah lunas'
            ]);
            return;
        }

        $this->db->where('id_billing', $idBilling);
        $updated = $this->db->update('tb_billingpayment', [
            'invoice_price_payment' => $paymentPrice,
            'tgl_payment_invoice' => $paymentDate,
            'status_invoice' => $statusInvoice,
            'last_update' => $this->getLastUpdateValue()
        ]);

        if ($updated) {
            echo json_encode([
                'status' => true,
                'message' => 'Data partial payment berhasil diperbarui'
            ]);
            return;
        }

        echo json_encode([
            'status' => false,
            'message' => 'Gagal memperbarui data partial payment'
        ]);
    }

    public function deleteBilling()
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        $idBilling = (int) $this->input->post('id_billing');

        if (empty($idBilling)) {
            echo json_encode([
                'status' => false,
                'message' => 'ID invoice tidak valid'
            ]);
            return;
        }

        $billing = $this->db
            ->select('id_billing, no_invoice')
            ->get_where('tb_billingpayment', ['id_billing' => $idBilling])
            ->row_array();

        if (!$billing) {
            echo json_encode([
                'status' => false,
                'message' => 'Data invoice tidak ditemukan'
            ]);
            return;
        }

        $this->db->where('id_billing', $idBilling);
        $deleted = $this->db->delete('tb_billingpayment');

        if ($deleted) {
            echo json_encode([
                'status' => true,
                'message' => 'Invoice berhasil dihapus'
            ]);
            return;
        }

        echo json_encode([
            'status' => false,
            'message' => 'Gagal menghapus invoice'
        ]);
    }

    public function saveManualBatchInvoice()
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        $idBowheer = $this->input->post('id_bowheer');
        $noInvoice = $this->input->post('no_invoice');
        $tglCreateInvoice = $this->input->post('tgl_create_invoice');
        $tglSubmitInvoice = $this->input->post('tgl_submit_invoice');
        $poNumber = $this->input->post('po_number');
        $poTgl = $this->input->post('po_tgl');
        $invoicePriceEst = $this->input->post('invoice_price_est');
        $invoicePriceNett = $this->input->post('invoice_price_nett');
        $regionalPayment = $this->input->post('regional_payment');
        $areaPayment = $this->input->post('area_payment');
        $deskripsiPayment = $this->input->post('deskripsi_payment');

        if (empty($idBowheer) || empty($noInvoice) || !is_array($idBowheer) || !is_array($noInvoice)) {
            echo json_encode([
                'status' => false,
                'message' => 'Data invoice manual belum lengkap'
            ]);
            return;
        }

        $rows = [];
        $rowCount = count($noInvoice);

        for ($i = 0; $i < $rowCount; $i++) {
            $rows[] = [
                'id_bowheer' => $idBowheer[$i] ?? null,
                'no_invoice' => $noInvoice[$i] ?? null,
                'tgl_create_invoice' => $tglCreateInvoice[$i] ?? null,
                'tgl_submit_invoice' => $tglSubmitInvoice[$i] ?? null,
                'po_number' => $poNumber[$i] ?? null,
                'po_tgl' => $poTgl[$i] ?? null,
                'invoice_price_est' => $invoicePriceEst[$i] ?? null,
                'invoice_price_nett' => $invoicePriceNett[$i] ?? null,
                'regional_payment' => $regionalPayment[$i] ?? null,
                'area_payment' => $areaPayment[$i] ?? null,
                'deskripsi_payment' => $deskripsiPayment[$i] ?? null
            ];
        }

        $validated = $this->validateInvoiceRows($rows);

        if (empty($validated['valid_rows'])) {
            echo json_encode([
                'status' => false,
                'message' => !empty($validated['errors']) ? $validated['errors'][0]['message'] : 'Tidak ada data valid untuk disimpan',
                'errors' => $validated['errors']
            ]);
            return;
        }

        $inserted = $this->db->insert_batch('tb_billingpayment', $validated['valid_rows']);

        if ($inserted) {
            echo json_encode([
                'status' => true,
                'message' => count($validated['valid_rows']) . ' invoice berhasil disimpan',
                'errors' => $validated['errors']
            ]);
            return;
        }

        echo json_encode([
            'status' => false,
            'message' => 'Gagal menyimpan invoice manual'
        ]);
    }

    public function previewInvoiceImport()
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size'] = 4096;
        $config['encrypt_name'] = true;

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_excel')) {
            echo json_encode([
                'status' => false,
                'message' => strip_tags($this->upload->display_errors())
            ]);
            return;
        }

        $fileData = $this->upload->data();
        $filePath = $fileData['full_path'];

        try {
            $extension = strtolower(pathinfo($fileData['file_name'], PATHINFO_EXTENSION));

            if ($extension === 'csv') {
                $this->loadPHPExcel();
                $sheetData = $this->readCsvSheetData($filePath);
            } else {
                $this->loadPHPExcel();
                $objPHPExcel = PHPExcel_IOFactory::load($filePath);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            }
        } catch (Exception $e) {
            @unlink($filePath);
            echo json_encode([
                'status' => false,
                'message' => 'File import tidak bisa dibaca'
            ]);
            return;
        }

        @unlink($filePath);

        if (count($sheetData) < 2) {
            echo json_encode([
                'status' => false,
                'message' => 'File import tidak memiliki data'
            ]);
            return;
        }

        $headerRow = reset($sheetData);
        $headerMap = [];
        foreach ($headerRow as $column => $header) {
            $mappedField = $this->parseExcelHeader($header);
            if ($mappedField) {
                $headerMap[$column] = $mappedField;
            }
        }

        $requiredFields = ['no_invoice', 'tgl_submit_invoice', 'invoice_price_nett'];
        foreach ($requiredFields as $requiredField) {
            if (!in_array($requiredField, $headerMap, true)) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Header file wajib memuat ' . $requiredField
                ]);
                return;
            }
        }

        if (!in_array('id_bowheer', $headerMap, true) && !in_array('nama_bowheer', $headerMap, true)) {
            echo json_encode([
                'status' => false,
                'message' => 'Header file wajib memuat id_bowheer atau nama_bowheer'
            ]);
            return;
        }

        $rows = [];
        foreach ($sheetData as $rowIndex => $excelRow) {
            if ($rowIndex === 1) {
                continue;
            }

            $row = [];
            foreach ($headerMap as $column => $field) {
                $row[$field] = $excelRow[$column];
            }

            $isBlank = true;
            foreach ($row as $value) {
                if (trim((string) $value) !== '') {
                    $isBlank = false;
                    break;
                }
            }

            if (!$isBlank) {
                $rows[] = $row;
            }
        }

        $validated = $this->validateInvoiceRows($rows);
        $previewRows = [];

        foreach ($validated['rows'] as $row) {
            $previewRows[] = [
                'row_number' => $row['row_number'],
                'id_bowheer' => $row['id_bowheer'],
                'no_invoice' => $row['no_invoice'],
                'tgl_create_invoice' => $row['tgl_create_invoice'],
                'tgl_submit_invoice' => $row['tgl_submit_invoice'],
                'po_number' => $row['po_number'],
                'po_tgl' => $row['po_tgl'],
                'invoice_price_est' => $row['invoice_price_est'],
                'invoice_price_nett' => $row['invoice_price_nett'],
                'regional_payment' => $row['regional_payment'],
                'area_payment' => $row['area_payment'],
                'deskripsi_payment' => $row['deskripsi_payment'],
                'status' => empty($row['errors']) ? 'valid' : 'invalid',
                'message' => empty($row['errors']) ? 'Siap diimport' : implode(', ', array_unique($row['errors']))
            ];
        }

        echo json_encode([
            'status' => true,
            'message' => count($validated['valid_rows']) . ' data valid dari ' . count($previewRows) . ' baris',
            'rows' => $previewRows,
            'valid_rows' => $validated['valid_rows'],
            'error_rows' => $validated['errors']
        ]);
    }

    public function saveImportedInvoices()
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        $rowsJson = $this->input->post('rows_json');
        $rows = json_decode($rowsJson, true);

        if (empty($rows) || !is_array($rows)) {
            echo json_encode([
                'status' => false,
                'message' => 'Tidak ada data import yang siap disimpan'
            ]);
            return;
        }

        $validated = $this->validateInvoiceRows($rows);

        if (empty($validated['valid_rows'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Semua data import tidak valid',
                'errors' => $validated['errors']
            ]);
            return;
        }

        $inserted = $this->db->insert_batch('tb_billingpayment', $validated['valid_rows']);

        if ($inserted) {
            echo json_encode([
                'status' => true,
                'message' => count($validated['valid_rows']) . ' invoice berhasil diimport',
                'errors' => $validated['errors']
            ]);
            return;
        }

        echo json_encode([
            'status' => false,
            'message' => 'Gagal menyimpan hasil import Excel'
        ]);
    }

    public function downloadInvoiceImportTemplate()
    {
        $filename = 'format_import_invoice_' . date('Ymd_His') . '.csv';
        $headers = [
            'id_bowheer',
            'nama_bowheer',
            'no_invoice',
            'tgl_create_invoice',
            'tgl_submit_invoice',
            'po_number',
            'po_tgl',
            'invoice_price_est',
            'invoice_price_nett',
            'regional_payment',
            'area_payment',
            'deskripsi_payment'
        ];

        $exampleRow = [
            '1',
            'Contoh Bowheer',
            'INV-001',
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
            'PO-001',
            date('Y-m-d H:i:s'),
            '30000000',
            '29195703.95',
            'JABODETABEK',
            'JAKARTA',
            'Contoh deskripsi invoice'
        ];

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
        fputcsv($output, $exampleRow);
        fclose($output);
        exit;
    }

    public function downloadBowheerReference()
    {
        $filename = 'referensi_bowheer_' . date('Ymd_His') . '.csv';
        $bowheerList = $this->db
            ->select('id_bowheer, nama_bowheer')
            ->from('tb_master_bowheer_bilco')
            ->order_by('nama_bowheer', 'ASC')
            ->get()
            ->result_array();

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['id_bowheer', 'nama_bowheer']);

        foreach ($bowheerList as $row) {
            fputcsv($output, [
                $row['id_bowheer'],
                $row['nama_bowheer']
            ]);
        }

        fclose($output);
        exit;
    }

}
