<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PO_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MPO_MyRep');
        $this->load->model('MPO_Monitor');
        $this->load->model('MChecklist_Dokument_MyRep');
        $this->load->model('MMainfeeder_MyRep');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('PO_MyRep');
            $this->myrepAccess->enforceByMethod('PO_MyRep', (string) $this->router->fetch_method(), [
                'saveTerminCertificate' => 'EDIT',
                'batchInvoiceTermin' => 'EDIT',
                'batchTerminCertificate' => 'EDIT',
                'batchSavePo' => 'EDIT',
                'setPoNyRef' => 'EDIT',
                'updatePoHeader' => 'EDIT',
                'deletePoHeader' => 'EDIT',
            ]);
        }
    }

    public function mainfeeder($mainfeederId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $mainfeederId = (int) $mainfeederId;
        if ($mainfeederId <= 0) {
            redirect('PO_MyRep?po_type=MAINFEEDER');
            return;
        }

        $mainfeeder = $this->MMainfeeder_MyRep->getById($mainfeederId);
        if (empty($mainfeeder)) {
            $this->session->set_flashdata('error', 'Data mainfeeder tidak ditemukan.');
            redirect('PO_MyRep/mainfeeder');
            return;
        }

        $poHeaders = $this->MMainfeeder_MyRep->getPoHeaders($mainfeederId);
        foreach ($poHeaders as &$poHeader) {
            $poHeader['termin_rows'] = $this->MMainfeeder_MyRep->getTerminRowsByPoId((int) ($poHeader['id_po_header'] ?? 0));
        }
        unset($poHeader);

        $projectType = $this->MMainfeeder_MyRep->normalizeStandaloneProjectType($mainfeeder['project_type'] ?? 'MAINFEEDER');
        $projectLabel = $projectType === 'FWA' ? 'FWA' : 'Mainfeeder';
        $certificateTerms = array_values(array_filter(
            $this->MPO_MyRep->getCertificateDetailRows('', '', $projectType, 0, 'ALL'),
            static function ($row) use ($mainfeederId) {
                return (int) ($row['id_mainfeeder'] ?? 0) === $mainfeederId;
            }
        ));
        $data['title'] = 'PO ' . $projectLabel;
        $data['section'] = 'po';
        $data['moduleTitle'] = 'PO ' . $projectLabel;
        $data['mainfeeder'] = $mainfeeder;
        $data['poHeaders'] = $poHeaders;
        $data['certificateTerms'] = $certificateTerms;
        $data['poCategoryOptions'] = ['INITIAL' => 'PO Initial', 'FINAL' => 'PO Final', 'AMANDMENT' => 'PO Amandement'];
        $data['poStatusOptions'] = ['NOT ISSUED' => 'NOT ISSUED', 'ISSUED' => 'ISSUED', 'PARTIAL PAYMENT' => 'PARTIAL PAYMENT', 'FULLY PAID' => 'FULLY PAID', 'CLOSED' => 'CLOSED'];
        $data['terminStatusOptions'] = ['NOT READY' => 'NOT READY', 'READY BILLING' => 'READY BILLING', 'BILLED' => 'BILLED', 'PAID' => 'PAID'];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Mainfeeder_MyRep/module_detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedStatus = strtoupper(trim((string) $this->input->get('status')));
        $selectedPoType = strtoupper(trim((string) $this->input->get('po_type')));

        $data['title'] = 'PO MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['selectedPoType'] = in_array($selectedPoType, ['CLUSTER', 'SUBFEEDER', 'MAINFEEDER', 'FWA'], true) ? $selectedPoType : '';
        $data['isReady'] = $this->MPO_MyRep->tablesReady();
        $data['cityOptions'] = $this->MPO_MyRep->getCityOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MPO_MyRep->getRows($selectedCity, $selectedStatus)
            : [];
        $data['mainfeederRows'] = $data['isReady'] && $this->MMainfeeder_MyRep->tablesReady()
            ? $this->MMainfeeder_MyRep->getRows($selectedCity, '')
            : [];
        $data['poListRows'] = $data['isReady']
            ? $this->MPO_MyRep->getPoListRows($selectedCity, $selectedStatus)
            : [];
        $data['terminBreakdownRows'] = $data['isReady']
            ? $this->MPO_MyRep->getTerminBreakdownByType($selectedCity, $selectedStatus)
            : [];
        $data['certificateSummaryRows'] = $data['isReady']
            ? $this->MPO_MyRep->getCertificateSummaryByTerm($selectedCity, $selectedStatus)
            : [];
        $data['certificateBatchRows'] = $data['isReady']
            ? $this->MPO_MyRep->getCertificateDetailRows($selectedCity, $selectedStatus, '', 0, 'ALL')
            : [];
        $data['certificateReleasedUninvoicedSummary'] = $data['isReady']
            ? $this->MPO_MyRep->getCertificateReleasedUninvoicedSummary($selectedCity, $selectedStatus)
            : [];
        $data['canBatchInvoice'] = $this->myrepAccess->hasPermission('PO_MyRep', 'EDIT');
        $data['canBatchCertificate'] = $data['canBatchInvoice']
            && (
                strtoupper(trim((string) $this->session->userdata('lokasi_user'))) === 'HO'
                || (string) $this->session->userdata('nama_level') === 'Super Admin'
            );
        $data['summary'] = $this->MPO_MyRep->getDashboardSummary($data['clusterRows']);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('PO_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function datatableMonitor()
    {
        if (empty($this->session->userdata('id_user')) || !$this->MPO_MyRep->tablesReady()) {
            $this->jsonResponse($this->emptyDataTableResponse());
            return;
        }

        $request = $this->getDataTableRequest();
        $result = $this->MPO_MyRep->getMonitorDataTable(
            $request['city'],
            $request['status'],
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir']
        );

        $rows = [];
        foreach ($result['rows'] as $index => $row) {
            $summaryStatus = strtoupper(trim((string) ($row['po_stage_status'] ?? 'NOT ISSUED')));
            $terminTotal = (int) ($row['termin_total_count'] ?? 0);
            $terminProgress = (int) ($row['termin_progress_count'] ?? $row['termin_paid_count'] ?? 0);
            $terminPercent = $terminTotal > 0 ? min(100, round(($terminProgress / $terminTotal) * 100)) : 0;
            $detailUrl = $this->getPoDetailUrl($row);
            $mainfeederCount = (int) ($row['po_mainfeeder_count'] ?? 0);

            $rows[] = [
                $request['start'] + $index + 1,
                '<strong><a href="' . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string) ($row['cluster_name'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</a></strong><div class="small text-muted">' . htmlspecialchars((string) ($row['team_name'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</div>',
                htmlspecialchars((string) ($row['city_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($row['regional_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                '<span class="badge badge-info">' . htmlspecialchars((string) ($row['status_current'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</span>',
                '<div>Cluster: ' . (int) ($row['po_cluster_count'] ?? 0) . '</div><div>Subfeeder: ' . (int) ($row['po_subfeeder_count'] ?? 0) . '</div>' . ($mainfeederCount > 0 ? '<div>Mainfeeder: ' . $mainfeederCount . '</div>' : '') . '<div><span class="badge badge-' . $this->stageBadgeClass($summaryStatus) . '">' . htmlspecialchars($summaryStatus, ENT_QUOTES, 'UTF-8') . '</span></div>',
                $this->formatNumber((float) ($row['po_total_value'] ?? 0)),
                $this->formatNumberOrDash((float) ($row['total_invoiced'] ?? $row['done_invoice_total'] ?? 0)),
                $this->formatNumberOrDash((float) ($row['outstanding_total'] ?? 0)),
                '<div class="po-mini-progress"><div class="po-mini-progress__head"><span>Termin Billed/Paid</span><span>' . $terminPercent . '%</span></div><div class="po-mini-progress__track"><span style="width: ' . $terminPercent . '%;"></span></div><div class="po-mini-progress__meta"><span>' . $terminProgress . ' billed/paid</span><span>' . $terminTotal . ' termin</span></div></div>',
                !empty($row['last_po_date']) ? htmlspecialchars((string) $row['last_po_date'], ENT_QUOTES, 'UTF-8') : '-',
                '<a href="' . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . '" class="btn btn-sm btn-primary">Detail</a>',
            ];
        }

        $this->jsonResponse([
            'draw' => $request['draw'],
            'recordsTotal' => (int) ($result['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
            'data' => $rows,
        ]);
    }

    public function datatablePo()
    {
        if (empty($this->session->userdata('id_user')) || !$this->MPO_MyRep->tablesReady()) {
            $this->jsonResponse($this->emptyDataTableResponse());
            return;
        }

        $request = $this->getDataTableRequest();
        $result = $this->MPO_MyRep->getPoListDataTable(
            $request['city'],
            $request['status'],
            $request['po_type_filter'],
            $request['stage_filter'],
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir']
        );

        $rows = [];
        foreach ($result['rows'] as $index => $row) {
            $tipePo = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER')));
            $statusPo = strtoupper(trim((string) ($row['po_stage_status'] ?? 'NOT ISSUED')));
            $terminTotal = (int) ($row['termin_total_count'] ?? 0);
            $terminProgress = (int) ($row['termin_progress_count'] ?? 0);
            $detailUrl = $this->getPoDetailUrl($row);
            $typeBadge = in_array($tipePo, ['MAINFEEDER', 'FWA'], true) ? 'dark' : ($tipePo === 'SUBFEEDER' ? 'warning' : 'primary');

            $rows[] = [
                $request['start'] + $index + 1,
                '<span class="badge badge-' . $typeBadge . '">' . htmlspecialchars($tipePo, ENT_QUOTES, 'UTF-8') . '</span>',
                '<strong><a href="' . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string) ($row['po_number'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</a></strong><div class="small text-muted">' . htmlspecialchars((string) ($row['po_category'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</div>',
                !empty($row['po_date']) ? htmlspecialchars((string) $row['po_date'], ENT_QUOTES, 'UTF-8') : '-',
                '<a href="' . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string) ($row['cluster_name'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</a>',
                htmlspecialchars((string) ($row['city_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($row['regional_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                '<span class="badge badge-' . $this->stageBadgeClass($statusPo) . '">' . htmlspecialchars($statusPo, ENT_QUOTES, 'UTF-8') . '</span>',
                $this->formatNumber((float) ($row['po_value'] ?? 0)),
                $terminProgress . '/' . $terminTotal,
                $this->formatNumberOrDash((float) (($row['done_invoice_per_termin'][1] ?? 0))),
                $this->formatNumberOrDash((float) (($row['outstanding_invoice_per_termin'][1] ?? 0))),
                $this->formatNumberOrDash((float) (($row['done_invoice_per_termin'][2] ?? 0))),
                $this->formatNumberOrDash((float) (($row['outstanding_invoice_per_termin'][2] ?? 0))),
                $this->formatNumberOrDash((float) (($row['done_invoice_per_termin'][3] ?? 0))),
                $this->formatNumberOrDash((float) (($row['outstanding_invoice_per_termin'][3] ?? 0))),
                $this->formatNumberOrDash((float) (($row['done_invoice_per_termin'][4] ?? 0))),
                $this->formatNumberOrDash((float) (($row['outstanding_invoice_per_termin'][4] ?? 0))),
                $this->formatNumberOrDash((float) (($row['done_invoice_per_termin'][5] ?? 0))),
                $this->formatNumberOrDash((float) (($row['outstanding_invoice_per_termin'][5] ?? 0))),
                $this->formatNumberOrDash((float) ($row['total_invoiced'] ?? 0)),
                $this->formatNumberOrDash((float) ($row['outstanding_total'] ?? 0)),
                '<a href="' . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . '" class="btn btn-sm btn-primary">Detail</a>',
            ];
        }

        $this->jsonResponse([
            'draw' => $request['draw'],
            'recordsTotal' => (int) ($result['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
            'data' => $rows,
        ]);
    }

    public function download_cluster_reference()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $rows = $this->MPO_MyRep->getRows('', '');
        $headers = ['CLUSTER INPUT', 'CLUSTER CODE', 'CLUSTER NAME', 'KOTA', 'REGIONAL', 'STATUS'];
        $fileName = 'myrep-cluster-reference-' . date('Ymd-His') . '.xls';

        $html = '<html><head><meta charset="utf-8"><style>';
        $html .= 'body{font-family:Arial,sans-serif;}';
        $html .= 'table{border-collapse:collapse;}';
        $html .= 'th,td{border:1px solid #999;padding:5px 7px;font-size:10pt;mso-number-format:\@;vertical-align:top;}';
        $html .= 'th{background:#d9eaf7;font-weight:bold;text-align:center;white-space:nowrap;}';
        $html .= '</style></head><body><table><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $html .= '</tr>';

        $seen = [];
        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            if ($clusterId <= 0 || isset($seen[$clusterId])) {
                continue;
            }
            $seen[$clusterId] = true;

            $clusterCode = trim((string) ($row['cluster_code'] ?? ''));
            $clusterName = trim((string) ($row['cluster_name'] ?? ''));
            $cityName = trim((string) ($row['city_name'] ?? ''));
            $regionalName = trim((string) ($row['regional_name'] ?? ''));
            $clusterInput = trim($clusterCode . ' | ' . $clusterName . ' | ' . $cityName, " \t\n\r\0\x0B|");

            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($clusterInput, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($clusterCode, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($clusterName, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($cityName, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($regionalName, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($row['status_current'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        $this->output
            ->set_content_type('application/vnd.ms-excel')
            ->set_header('Content-Disposition: attachment; filename="' . $fileName . '"')
            ->set_header('Cache-Control: max-age=0')
            ->set_output($html);
    }

    public function detail($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            redirect('PO_MyRep');
            return;
        }

        $cluster = $this->MPO_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Detail cluster PO tidak ditemukan.');
            redirect('PO_MyRep');
            return;
        }

        $this->MPO_MyRep->syncTerminEstimatesForCluster($clusterId, '', (int) $this->session->userdata('id_user'));
        $this->MPO_Monitor->ensurePoMonitorFromMyRepCluster($clusterId, (int) $this->session->userdata('id_user'));
        $cluster = $this->MPO_MyRep->getClusterById($clusterId);

        $poHeaders = $this->MPO_MyRep->getPoHeadersByClusterId($clusterId);
        $poGroups = [
            'CLUSTER' => [],
            'SUBFEEDER' => [],
        ];

        foreach ($poHeaders as $header) {
            $header['termin_rows'] = $this->MPO_MyRep->getTerminRowsByPoId((int) ($header['id_po_header'] ?? 0));
            $groupKey = strtoupper(trim((string) ($header['po_type'] ?? 'CLUSTER')));
            if (!isset($poGroups[$groupKey])) {
                $poGroups[$groupKey] = [];
            }
            $poGroups[$groupKey][] = $header;
        }

        $data['title'] = 'Detail PO MyRep';
        $data['cluster'] = $cluster;
        $data['poGroups'] = $poGroups;
        $data['certificateTerms'] = $this->MChecklist_Dokument_MyRep->getCertificateTermRows(
            (int) ($cluster['rfs_cluster_id'] ?? 0),
            $clusterId
        );
        $data['poTypeOptions'] = ['CLUSTER' => 'PO Cluster', 'SUBFEEDER' => 'PO Subfeeder'];
        $data['poCategoryOptions'] = ['INITIAL' => 'PO Initial', 'FINAL' => 'PO Final', 'AMANDMENT' => 'PO Amandement'];
        $data['poStatusOptions'] = ['NOT ISSUED' => 'NOT ISSUED', 'ISSUED' => 'ISSUED', 'PARTIAL PAYMENT' => 'PARTIAL PAYMENT', 'FULLY PAID' => 'FULLY PAID', 'CLOSED' => 'CLOSED'];
        $data['terminStatusOptions'] = ['NOT READY' => 'NOT READY', 'READY BILLING' => 'READY BILLING', 'BILLED' => 'BILLED', 'PAID' => 'PAID'];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('PO_MyRep/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function savePo()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $poType = strtoupper(trim((string) $this->input->post('po_type')));
        $poCategory = strtoupper(trim((string) $this->input->post('po_category')));
        $poNumber = trim((string) $this->input->post('po_number'));
        $poDate = $this->normalizeDate($this->input->post('po_date'));
        $poValue = $this->normalizeNumber($this->input->post('po_value'));
        $statusPo = strtoupper(trim((string) $this->input->post('status_po')));
        $poVersionLabel = trim((string) $this->input->post('po_version_label'));
        $remarkPo = trim((string) $this->input->post('remark_po'));
        $parentPoHeaderId = (int) $this->input->post('parent_po_header_id');
        $nyPoRef = strtoupper(trim((string) $this->input->post('ny_po_ref')));

        if ($clusterId <= 0 || $poNumber === '' || $poDate === null || $poValue <= 0) {
            $this->session->set_flashdata('error', 'Cluster, nomor PO, tanggal PO, dan nilai PO wajib diisi.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        if (!in_array($poType, ['CLUSTER', 'SUBFEEDER'], true)) {
            $poType = 'CLUSTER';
        }

        if (!in_array($poCategory, ['INITIAL', 'FINAL', 'AMANDMENT'], true)) {
            $poCategory = 'INITIAL';
        }

        if (!in_array($statusPo, ['NOT ISSUED', 'ISSUED', 'PARTIAL PAYMENT', 'FULLY PAID', 'CLOSED'], true)) {
            $statusPo = 'ISSUED';
        }
        if ($nyPoRef !== '' && !preg_match('/^NY-\d+$/', $nyPoRef)) {
            $this->session->set_flashdata('error', 'NY PO REF tidak valid. Gunakan format NY-123.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        $result = $this->MPO_MyRep->createPoHeader($clusterId, [
            'parent_po_header_id' => $parentPoHeaderId > 0 ? $parentPoHeaderId : null,
            'po_type' => $poType,
            'po_category' => $poCategory,
            'po_number' => $poNumber,
            'po_date' => $poDate,
            'po_value' => $poValue,
            'status_po' => $statusPo,
            'po_version_label' => $poVersionLabel,
            'remark_po' => $remarkPo,
            'po_monitor_ny_ref' => $nyPoRef,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        if ($result > 0) {
            $this->MPO_Monitor->ensurePoMonitorFromMyRepPoHeader((int) $result, $userId);
            if ($nyPoRef !== '') {
                $linkResult = $this->MPO_Monitor->linkNyPoReferenceToMyRepHeader((int) $result, $nyPoRef, $userId);
                if (empty($linkResult['status'])) {
                    $this->session->set_flashdata('error', 'PO tersimpan, tapi NY PO REF gagal link: ' . ($linkResult['message'] ?? 'unknown error'));
                    redirect('PO_MyRep/detail/' . $clusterId);
                    return;
                }
            }
        }

        $this->session->set_flashdata($result > 0 ? 'success' : 'error', $result > 0 ? 'PO berhasil disimpan.' : 'PO gagal disimpan.');
        redirect('PO_MyRep/detail/' . $clusterId);
    }

    public function updatePoHeader()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $poHeaderId = (int) $this->input->post('id_po_header');
        $header = $this->MPO_MyRep->getPoHeaderById($poHeaderId);
        if (empty($header)) {
            $this->session->set_flashdata('error', 'Header PO tidak ditemukan.');
            redirect('PO_MyRep');
            return;
        }

        $clusterId = (int) ($header['id_myrep_cluster'] ?? 0);
        $poType = strtoupper(trim((string) $this->input->post('po_type')));
        $poCategory = strtoupper(trim((string) $this->input->post('po_category')));
        $poNumber = trim((string) $this->input->post('po_number'));
        $poDate = $this->normalizeDate($this->input->post('po_date'));
        $poValue = $this->normalizeNumber($this->input->post('po_value'));
        $statusPo = strtoupper(trim((string) $this->input->post('status_po')));
        $poVersionLabel = trim((string) $this->input->post('po_version_label'));
        $remarkPo = trim((string) $this->input->post('remark_po'));
        $parentPoHeaderId = (int) $this->input->post('parent_po_header_id');
        $nyPoRef = strtoupper(trim((string) $this->input->post('ny_po_ref')));

        if ($poNumber === '' || $poDate === null || $poValue <= 0) {
            $this->session->set_flashdata('error', 'Nomor PO, tanggal PO, dan nilai PO wajib diisi.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        if (!in_array($poType, ['CLUSTER', 'SUBFEEDER'], true)) {
            $poType = 'CLUSTER';
        }

        if (!in_array($poCategory, ['INITIAL', 'FINAL', 'AMANDMENT'], true)) {
            $poCategory = 'INITIAL';
        }

        if (!in_array($statusPo, ['NOT ISSUED', 'ISSUED', 'PARTIAL PAYMENT', 'FULLY PAID', 'CLOSED'], true)) {
            $statusPo = 'ISSUED';
        }

        if ($nyPoRef !== '' && !preg_match('/^NY-\d+$/', $nyPoRef)) {
            $this->session->set_flashdata('error', 'NY PO REF tidak valid. Gunakan format NY-123.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        if ($this->MPO_MyRep->poHeaderExists($clusterId, $poType, $poCategory, $poNumber, $poHeaderId)) {
            $this->session->set_flashdata('error', 'PO dengan tipe, kategori, dan nomor yang sama sudah ada.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        $oldNyPoRef = strtoupper(trim((string) ($header['po_monitor_ny_ref'] ?? '')));
        $result = $this->MPO_MyRep->updatePoHeader($poHeaderId, [
            'parent_po_header_id' => $parentPoHeaderId > 0 ? $parentPoHeaderId : null,
            'po_type' => $poType,
            'po_category' => $poCategory,
            'po_number' => $poNumber,
            'po_date' => $poDate,
            'po_value' => $poValue,
            'status_po' => $statusPo,
            'po_version_label' => $poVersionLabel,
            'remark_po' => $remarkPo,
            'po_monitor_ny_ref' => $nyPoRef,
            'updated_by' => $userId,
        ]);

        if (!$result) {
            $this->session->set_flashdata('error', 'Header PO gagal diupdate.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        $ensure = $this->MPO_Monitor->ensurePoMonitorFromMyRepPoHeader($poHeaderId, $userId);
        if ($nyPoRef !== '') {
            $linkResult = $this->MPO_Monitor->linkNyPoReferenceToMyRepHeader($poHeaderId, $nyPoRef, $userId);
            if (empty($linkResult['status'])) {
                $this->session->set_flashdata('error', 'Header PO terupdate, tapi NY PO REF gagal link: ' . ($linkResult['message'] ?? 'unknown error'));
                redirect('PO_MyRep/detail/' . $clusterId);
                return;
            }
        }
        if ($oldNyPoRef !== '' && $oldNyPoRef !== $nyPoRef) {
            $this->MPO_Monitor->unlinkNyPoReferenceFromPo($oldNyPoRef, (int) ($ensure['id_po'] ?? 0));
        }

        $this->session->set_flashdata('success', 'Header PO berhasil diupdate.');
        redirect('PO_MyRep/detail/' . $clusterId);
    }

    public function deletePoHeader()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $poHeaderId = (int) $this->input->post('id_po_header');
        $header = $this->MPO_MyRep->getPoHeaderById($poHeaderId);
        if (empty($header)) {
            $this->session->set_flashdata('error', 'Header PO tidak ditemukan.');
            redirect('PO_MyRep');
            return;
        }

        $clusterId = (int) ($header['id_myrep_cluster'] ?? 0);
        $mirrorResult = $this->MPO_Monitor->deletePoMonitorMirrorFromMyRepHeader($poHeaderId);
        if (empty($mirrorResult['status'])) {
            $this->session->set_flashdata('error', 'Mirror PO Monitor gagal dihapus: ' . ($mirrorResult['message'] ?? 'unknown error'));
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        $deleted = $this->MPO_MyRep->deletePoHeader($poHeaderId, (int) $this->session->userdata('id_user'));
        $this->session->set_flashdata($deleted ? 'success' : 'error', $deleted ? 'PO berhasil dihapus.' : 'PO gagal dihapus.');
        redirect('PO_MyRep/detail/' . $clusterId);
    }

    public function batchSavePo()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $clusterIds = (array) $this->input->post('cluster_id');
        $poTypes = (array) $this->input->post('po_type');
        $poCategories = (array) $this->input->post('po_category');
        $poNumbers = (array) $this->input->post('po_number');
        $poDates = (array) $this->input->post('po_date');
        $poValues = (array) $this->input->post('po_value');
        $nyPoRefs = (array) $this->input->post('ny_po_ref');

        $updatedCount = 0;
        $skippedMessages = [];
        $seenKeys = [];
        $clusterCache = [];
        $userId = (int) $this->session->userdata('id_user');

        foreach ($poNumbers as $index => $poNumberRaw) {
            $entityId = (int) ($clusterIds[$index] ?? 0);
            $poType = strtoupper(trim((string) ($poTypes[$index] ?? 'CLUSTER')));
            $poCategory = strtoupper(trim((string) ($poCategories[$index] ?? 'INITIAL')));
            $poNumber = trim((string) $poNumberRaw);
            $poDate = $this->normalizeDate($poDates[$index] ?? '');
            $poValue = $this->normalizeNumber($poValues[$index] ?? '');
            $nyPoRef = strtoupper(trim((string) ($nyPoRefs[$index] ?? '')));

            if ($entityId <= 0 && $poNumber === '' && $poDate === null && $poValue <= 0) {
                continue;
            }

            if (!in_array($poType, ['CLUSTER', 'SUBFEEDER', 'MAINFEEDER', 'FWA'], true)) {
                $poType = 'CLUSTER';
            }
            $isStandalonePo = in_array($poType, ['MAINFEEDER', 'FWA'], true);
            if (!in_array($poCategory, ['INITIAL', 'FINAL'], true)) {
                $poCategory = 'INITIAL';
            }

            $cacheKey = ($isStandalonePo ? 'MF' : 'CL') . '|' . $entityId;
            if (!isset($clusterCache[$cacheKey])) {
                $clusterCache[$cacheKey] = $entityId > 0
                    ? ($isStandalonePo ? $this->MMainfeeder_MyRep->getById($entityId) : $this->MPO_MyRep->getClusterById($entityId))
                    : [];
            }
            $cluster = $clusterCache[$cacheKey];
            $clusterLabel = !empty($cluster['cluster_name'])
                ? (string) $cluster['cluster_name']
                : (!empty($cluster['mainfeeder_name'])
                    ? (string) $cluster['mainfeeder_name']
                    : 'Baris ' . ($index + 1));
            $rowLabel = $clusterLabel . ' / ' . ($poNumber !== '' ? $poNumber : ('PO ' . $poCategory));

            if ($entityId <= 0 || empty($cluster)) {
                $skippedMessages[] = $rowLabel . ': cluster/mainfeeder tidak valid.';
                continue;
            }
            if ($isStandalonePo) {
                $projectType = $this->MMainfeeder_MyRep->normalizeStandaloneProjectType($cluster['project_type'] ?? 'MAINFEEDER');
                if ($poType !== $projectType) {
                    $skippedMessages[] = $rowLabel . ': tipe PO tidak sesuai dengan tipe project ' . $projectType . '.';
                    continue;
                }
            }
            if ($poNumber === '' || $poDate === null || $poValue <= 0) {
                $skippedMessages[] = $rowLabel . ': nomor PO, tanggal PO, dan nilai PO wajib valid.';
                continue;
            }
            if ($nyPoRef !== '' && !preg_match('/^NY-\d+$/', $nyPoRef)) {
                $skippedMessages[] = $rowLabel . ': NY PO REF tidak valid. Gunakan format NY-123.';
                continue;
            }

            $dedupeKey = ($isStandalonePo ? 'MF' : 'CL') . '|' . $entityId . '|' . $poType . '|' . $poCategory . '|' . strtoupper($poNumber);
            if (isset($seenKeys[$dedupeKey])) {
                $skippedMessages[] = $rowLabel . ': duplikat dalam batch.';
                continue;
            }
            $seenKeys[$dedupeKey] = true;

            $exists = $isStandalonePo
                ? $this->MMainfeeder_MyRep->poHeaderExists($entityId, $poType, $poCategory, $poNumber)
                : $this->MPO_MyRep->poHeaderExists($entityId, $poType, $poCategory, $poNumber);
            if ($exists) {
                $skippedMessages[] = $rowLabel . ': PO dengan tipe dan kategori yang sama sudah ada.';
                continue;
            }

            $payload = [
                'parent_po_header_id' => null,
                'po_type' => $poType,
                'po_category' => $poCategory,
                'po_number' => $poNumber,
                'po_date' => $poDate,
                'po_value' => $poValue,
                'status_po' => 'ISSUED',
                'po_version_label' => '',
                'remark_po' => '',
                'po_monitor_ny_ref' => $nyPoRef,
                'created_by' => $userId,
                'updated_by' => $userId,
            ];
            $result = $isStandalonePo
                ? $this->MMainfeeder_MyRep->createPoHeader($entityId, $payload)
                : $this->MPO_MyRep->createPoHeader($entityId, $payload);

            if ($result > 0) {
                $this->MPO_Monitor->ensurePoMonitorFromMyRepPoHeader((int) $result, $userId);
                if ($nyPoRef !== '') {
                    if ($isStandalonePo) {
                        $this->MPO_MyRep->updatePoHeaderNyRef((int) $result, $nyPoRef, $userId);
                    }
                    $linkResult = $this->MPO_Monitor->linkNyPoReferenceToMyRepHeader((int) $result, $nyPoRef, $userId);
                    if (empty($linkResult['status'])) {
                        $skippedMessages[] = $rowLabel . ': PO tersimpan tapi NY PO REF gagal link (' . ($linkResult['message'] ?? 'unknown error') . ').';
                    }
                }
                $updatedCount++;
            } else {
                $skippedMessages[] = $rowLabel . ': gagal disimpan.';
            }
        }

        if ($updatedCount > 0) {
            $message = $updatedCount . ' PO initial/final berhasil disimpan.';
            if (!empty($skippedMessages)) {
                $message .= ' ' . count($skippedMessages) . ' baris dilewati: ' . implode('; ', array_slice($skippedMessages, 0, 5));
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', 'Tidak ada PO initial/final yang berhasil disimpan. ' . implode('; ', array_slice($skippedMessages, 0, 5)));
        }

        redirect('PO_MyRep');
    }

    public function setPoNyRef()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $poHeaderId = (int) $this->input->post('id_po_header');
        $clusterId = (int) $this->input->post('cluster_id');
        $nyPoRef = strtoupper(trim((string) $this->input->post('ny_po_ref')));
        $userId = (int) $this->session->userdata('id_user');

        if ($poHeaderId <= 0 || $clusterId <= 0) {
            $this->session->set_flashdata('error', 'Data PO MyRep tidak valid.');
            redirect('PO_MyRep');
            return;
        }

        if ($nyPoRef !== '' && !preg_match('/^NY-\d+$/', $nyPoRef)) {
            $this->session->set_flashdata('error', 'NY PO REF tidak valid. Gunakan format NY-123.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        $oldNyPoRef = '';
        if ($this->db->table_exists('tb_myrep_po_header') && $this->db->field_exists('po_monitor_ny_ref', 'tb_myrep_po_header')) {
            $oldHeader = $this->db
                ->select('po_monitor_ny_ref')
                ->where('id_po_header', $poHeaderId)
                ->get('tb_myrep_po_header')
                ->row_array();
            $oldNyPoRef = strtoupper(trim((string) ($oldHeader['po_monitor_ny_ref'] ?? '')));
        }

        $saved = $this->MPO_MyRep->updatePoHeaderNyRef($poHeaderId, $nyPoRef, $userId);
        if (!$saved) {
            $this->session->set_flashdata('error', 'NY PO REF gagal disimpan.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        if ($nyPoRef !== '') {
            $linkResult = $this->MPO_Monitor->linkNyPoReferenceToMyRepHeader($poHeaderId, $nyPoRef, $userId);
            if (empty($linkResult['status'])) {
                $this->MPO_MyRep->updatePoHeaderNyRef($poHeaderId, $oldNyPoRef, $userId);
                $this->session->set_flashdata('error', 'NY PO REF tersimpan, tapi gagal link ke PO Monitor: ' . ($linkResult['message'] ?? 'unknown error'));
                redirect('PO_MyRep/detail/' . $clusterId);
                return;
            }
        }

        if ($oldNyPoRef !== '' && $oldNyPoRef !== $nyPoRef) {
            $ensure = $this->MPO_Monitor->ensurePoMonitorFromMyRepPoHeader($poHeaderId, $userId);
            $this->MPO_Monitor->unlinkNyPoReferenceFromPo($oldNyPoRef, (int) ($ensure['id_po'] ?? 0));
        }

        $this->session->set_flashdata('success', $nyPoRef !== '' ? 'NY PO REF berhasil disimpan dan di-link ke PO Monitor.' : 'NY PO REF berhasil dikosongkan.');
        redirect('PO_MyRep/detail/' . $clusterId);
    }

    public function updateTermin()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $terminId = (int) $this->input->post('id_po_termin');
        $termin = $this->MPO_MyRep->getTerminById($terminId);
        if (empty($termin)) {
            $this->session->set_flashdata('error', 'Data termin tidak ditemukan.');
            redirect('PO_MyRep');
            return;
        }

        $statusTermin = strtoupper(trim((string) $this->input->post('status_termin')));
        if (!in_array($statusTermin, ['NOT READY', 'READY BILLING', 'BILLED', 'PAID'], true)) {
            $statusTermin = 'NOT READY';
        }
        $invoiceDate = $this->normalizeDate($this->input->post('invoice_date'));
        $invoiceValue = $this->normalizeNumber($this->input->post('invoice_value')) ?: null;
        $terminNo = (int) ($termin['termin_no'] ?? 0);
        if (in_array($statusTermin, ['BILLED', 'PAID'], true) && $invoiceDate === null) {
            $this->session->set_flashdata('error', 'Termin belum bisa berstatus ' . $statusTermin . ' karena tanggal invoice wajib diisi.');
            redirect('PO_MyRep/detail/' . (int) ($termin['id_myrep_cluster'] ?? 0));
            return;
        }
        if (
            $terminNo >= 2
            && $terminNo <= 5
            && in_array($statusTermin, ['BILLED', 'PAID'], true)
            && $this->normalizeCertificateDateValue((string) ($termin['sertifikat_invoice_date'] ?? '')) === ''
        ) {
            $this->session->set_flashdata('error', 'Termin ' . $terminNo . ' belum bisa ditagihkan karena sertifikat belum berisi tanggal release yang valid.');
            redirect('PO_MyRep/detail/' . (int) ($termin['id_myrep_cluster'] ?? 0));
            return;
        }
        if ($terminNo === 5 && in_array($statusTermin, ['BILLED', 'PAID'], true) && !$this->isFacTerminDue($termin)) {
            $this->session->set_flashdata('error', 'Termin 5 FAC belum bisa ditagihkan karena belum BJT 90 hari dari tanggal sertifikat RFS.');
            redirect('PO_MyRep/detail/' . (int) ($termin['id_myrep_cluster'] ?? 0));
            return;
        }

        $result = $this->MPO_MyRep->updateTermin($terminId, [
            'status_termin' => $statusTermin,
            'invoice_number' => trim((string) $this->input->post('invoice_number')),
            'invoice_date' => $invoiceDate,
            'invoice_value' => $invoiceValue,
            'bast_date' => $this->normalizeDate($this->input->post('bast_date')),
            'payment_date' => $this->normalizeDate($this->input->post('payment_date')),
            'remark_termin' => trim((string) $this->input->post('remark_termin')),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ]);

        if ($result) {
            $this->MPO_Monitor->syncMyRepTerminClaim($terminId, (int) $this->session->userdata('id_user'));
        }

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Termin berhasil diupdate.' : 'Termin gagal diupdate.');
        redirect('PO_MyRep/detail/' . (int) ($termin['id_myrep_cluster'] ?? 0));
    }

    public function batchInvoiceTermin()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $invoiceDate = $this->normalizeDate($this->input->post('invoice_date'));
        $poNumbers = (array) $this->input->post('po_number');
        $termInputs = (array) $this->input->post('term_no');
        $invoiceValues = (array) $this->input->post('invoice_value');

        if ($invoiceDate === null) {
            $this->session->set_flashdata('error', 'Tanggal invoice general wajib diisi.');
            redirect('PO_MyRep');
            return;
        }

        if (!$this->MPO_MyRep->ensurePoTerminInvoiceValueColumn()) {
            $this->session->set_flashdata('error', 'Kolom nilai invoice belum tersedia dan gagal dibuat.');
            redirect('PO_MyRep');
            return;
        }

        $updatedCount = 0;
        $skippedMessages = [];
        $seenKeys = [];
        foreach ($poNumbers as $index => $poNumber) {
            $poNumber = trim((string) $poNumber);
            $termNo = $this->normalizeTerminNoInput($termInputs[$index] ?? '');
            $invoiceValue = $this->normalizeNumber($invoiceValues[$index] ?? '');

            if ($poNumber === '' && $termNo <= 0 && abs((float) $invoiceValue) < 0.000001) {
                continue;
            }

            $rowLabel = $poNumber !== '' ? $poNumber . ' T' . ($termNo > 0 ? $termNo : '?') : 'Baris ' . ($index + 1);
            if ($poNumber === '' || $termNo < 1 || $termNo > 5) {
                $skippedMessages[] = $rowLabel . ': nomor PO atau term tidak valid.';
                continue;
            }
            if (abs((float) $invoiceValue) < 0.000001) {
                $skippedMessages[] = $rowLabel . ': nilai invoice wajib diisi dan tidak boleh 0.';
                continue;
            }

            $dedupeKey = strtoupper($poNumber) . '|' . $termNo;
            if (isset($seenKeys[$dedupeKey])) {
                $skippedMessages[] = $rowLabel . ': duplikat dalam batch.';
                continue;
            }
            $seenKeys[$dedupeKey] = true;

            $termin = $this->MPO_MyRep->getTerminByPoNumberAndTerm($poNumber, $termNo);
            if (empty($termin)) {
                $skippedMessages[] = $rowLabel . ': termin PO tidak ditemukan.';
                continue;
            }

            $statusTermin = strtoupper(trim((string) ($termin['status_termin'] ?? 'NOT READY')));
            if ($statusTermin === 'PAID') {
                $skippedMessages[] = $rowLabel . ': termin sudah PAID.';
                continue;
            }
            if ($termNo >= 2 && $termNo <= 5 && $this->normalizeCertificateDateValue((string) ($termin['sertifikat_invoice_date'] ?? '')) === '') {
                $skippedMessages[] = $rowLabel . ': sertifikat belum release.';
                continue;
            }
            if ($termNo === 5 && !$this->isFacTerminDue($termin)) {
                $skippedMessages[] = $rowLabel . ': FAC belum BJT 90 hari.';
                continue;
            }

            $terminUpdatePayload = [
                'status_termin' => 'BILLED',
                'invoice_number' => '',
                'invoice_date' => $invoiceDate,
                'invoice_value' => $invoiceValue,
                'bast_date' => $termin['bast_date'] ?? null,
                'payment_date' => $termin['payment_date'] ?? null,
                'remark_termin' => trim((string) ($termin['remark_termin'] ?? '')),
                'updated_by' => (int) $this->session->userdata('id_user'),
            ];
            $terminPoType = strtoupper(trim((string) ($termin['po_type'] ?? 'CLUSTER')));
            $isStandalonePo = in_array($terminPoType, ['MAINFEEDER', 'FWA'], true);
            $updated = $isStandalonePo
                ? $this->MMainfeeder_MyRep->updateTermin((int) ($termin['id_po_termin'] ?? 0), $terminUpdatePayload)
                : $this->MPO_MyRep->updateTermin((int) ($termin['id_po_termin'] ?? 0), $terminUpdatePayload);

            if ($updated) {
                $this->MPO_Monitor->syncMyRepTerminClaim((int) ($termin['id_po_termin'] ?? 0), (int) $this->session->userdata('id_user'));
                $updatedCount++;
            } else {
                $skippedMessages[] = $rowLabel . ': gagal disimpan.';
            }
        }

        if ($updatedCount > 0) {
            $message = $updatedCount . ' invoice termin berhasil disimpan.';
            if (!empty($skippedMessages)) {
                $message .= ' ' . count($skippedMessages) . ' baris dilewati: ' . implode('; ', array_slice($skippedMessages, 0, 5));
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', 'Tidak ada invoice termin yang berhasil disimpan. ' . implode('; ', array_slice($skippedMessages, 0, 5)));
        }

        redirect('PO_MyRep');
    }

    public function saveTerminCertificate()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $canReleaseCertificate = strtoupper(trim((string) $this->session->userdata('lokasi_user'))) === 'HO'
            || (string) $this->session->userdata('nama_level') === 'Super Admin';
        if (!$canReleaseCertificate) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk menyimpan sertifikat.');
            redirect('PO_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $terminId = (int) $this->input->post('id_po_termin');
        $certificateValue = trim((string) $this->input->post('sertifikat_invoice'));
        $certificateMode = strtolower(trim((string) $this->input->post('certificate_mode')));
        if (!in_array($certificateMode, ['claim', 'status'], true)) {
            $certificateMode = 'claim';
        }

        if ($clusterId <= 0 || $terminId <= 0) {
            $this->session->set_flashdata('error', 'Data sertifikat tidak valid.');
            redirect('PO_MyRep');
            return;
        }

        $cluster = $this->MPO_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Cluster PO tidak ditemukan.');
            redirect('PO_MyRep');
            return;
        }

        $termRows = $this->MChecklist_Dokument_MyRep->getCertificateTermRows(
            (int) ($cluster['rfs_cluster_id'] ?? 0),
            $clusterId
        );
        $selectedTerm = null;
        foreach ($termRows as $termRow) {
            if ((int) ($termRow['id_po_termin'] ?? 0) === $terminId) {
                $selectedTerm = $termRow;
                break;
            }
        }

        if (empty($selectedTerm)) {
            $this->session->set_flashdata('error', 'Termin sertifikat tidak ditemukan di cluster ini.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        $isReady = !empty($selectedTerm['is_release_ready']);
        $isReleased = !empty($selectedTerm['is_certificate_released']);
        $certificateDateValue = $this->MChecklist_Dokument_MyRep->normalizeCertificateDateForRelease($certificateValue);
        if ($certificateMode === 'claim' && $certificateDateValue === '') {
            $this->session->set_flashdata('error', 'Claim sertifikat wajib memakai format tanggal yang valid.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }
        if ($certificateMode === 'claim' && !$isReady && !$isReleased) {
            $this->session->set_flashdata('error', 'Tanggal release sertifikat belum bisa disimpan karena syarat release belum terpenuhi. Status text tetap boleh disimpan.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }
        $saveCertificateValue = $certificateMode === 'claim'
            ? $certificateDateValue
            : $certificateValue;

        $result = $this->MChecklist_Dokument_MyRep->updateTerminCertificate(
            $terminId,
            $saveCertificateValue,
            (int) $this->session->userdata('id_user')
        );

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Sertifikat termin berhasil disimpan.' : 'Sertifikat termin gagal disimpan.');
        if (trim((string) $this->input->post('redirect_scope')) === 'dashboard') {
            $query = [];
            $selectedCity = strtoupper(trim((string) $this->input->post('selected_city')));
            $selectedStatus = strtoupper(trim((string) $this->input->post('selected_status')));
            if ($selectedCity !== '') {
                $query['city'] = $selectedCity;
            }
            if ($selectedStatus !== '') {
                $query['status'] = $selectedStatus;
            }
            $suffix = !empty($query) ? '?' . http_build_query($query) : '';
            redirect('PO_MyRep' . $suffix);
            return;
        }
        redirect('PO_MyRep/detail/' . $clusterId);
    }

    public function batchTerminCertificate()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $canReleaseCertificate = strtoupper(trim((string) $this->session->userdata('lokasi_user'))) === 'HO'
            || (string) $this->session->userdata('nama_level') === 'Super Admin';
        if (!$canReleaseCertificate) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk batch update status/tanggal sertifikat.');
            redirect('PO_MyRep');
            return;
        }

        $poNumbers = (array) $this->input->post('certificate_po_number');
        $termInputs = (array) $this->input->post('certificate_term_no');
        $certificateValues = (array) $this->input->post('certificate_value');

        $updatedCount = 0;
        $skippedMessages = [];
        $seenKeys = [];
        $termRowsCache = [];
        foreach ($poNumbers as $index => $poNumber) {
            $poNumber = trim((string) $poNumber);
            $termNo = $this->normalizeTerminNoInput($termInputs[$index] ?? '');
            $certificateValue = trim((string) ($certificateValues[$index] ?? ''));
            $rowLabel = $poNumber !== '' ? $poNumber . ' T' . ($termNo > 0 ? $termNo : '?') : 'Baris ' . ($index + 1);

            if ($poNumber === '' && $termNo <= 0 && $certificateValue === '') {
                continue;
            }
            if ($poNumber === '' || $termNo < 2 || $termNo > 5) {
                $skippedMessages[] = $rowLabel . ': nomor PO wajib dan term sertifikat harus 2-5.';
                continue;
            }
            if ($certificateValue === '') {
                $skippedMessages[] = $rowLabel . ': status/tanggal sertifikat wajib diisi.';
                continue;
            }

            $dedupeKey = strtoupper($poNumber) . '|' . $termNo;
            if (isset($seenKeys[$dedupeKey])) {
                $skippedMessages[] = $rowLabel . ': duplikat dalam batch.';
                continue;
            }
            $seenKeys[$dedupeKey] = true;

            $normalizedDate = $this->normalizeCertificateDateValue($certificateValue);
            $isDateValue = $normalizedDate !== '';

            $termin = $this->MPO_MyRep->getTerminByPoNumberAndTerm($poNumber, $termNo);
            if (empty($termin)) {
                $skippedMessages[] = $rowLabel . ': termin PO tidak ditemukan.';
                continue;
            }

            if ($isDateValue) {
                $terminPoType = strtoupper(trim((string) ($termin['po_type'] ?? 'CLUSTER')));
                $isStandalonePo = in_array($terminPoType, ['MAINFEEDER', 'FWA'], true);
                $cacheKey = $isStandalonePo
                    ? $terminPoType . '|' . (int) ($termin['id_mainfeeder'] ?? 0)
                    : 'CLUSTER|' . (int) ($termin['id_myrep_cluster'] ?? 0);

                if (!isset($termRowsCache[$cacheKey])) {
                    if ($isStandalonePo) {
                        $termRowsCache[$cacheKey] = $this->MPO_MyRep->getCertificateDetailRows('', '', $terminPoType, 0, 'ALL');
                    } else {
                        $clusterId = (int) ($termin['id_myrep_cluster'] ?? 0);
                        $cluster = $this->MPO_MyRep->getClusterById($clusterId);
                        $termRowsCache[$cacheKey] = !empty($cluster)
                            ? $this->MChecklist_Dokument_MyRep->getCertificateTermRows((int) ($cluster['rfs_cluster_id'] ?? 0), $clusterId)
                            : [];
                    }
                }

                $selectedTerm = null;
                foreach ($termRowsCache[$cacheKey] as $termRow) {
                    if ((int) ($termRow['id_po_termin'] ?? 0) === (int) ($termin['id_po_termin'] ?? 0)) {
                        $selectedTerm = $termRow;
                        break;
                    }
                }
                if (empty($selectedTerm) || (empty($selectedTerm['is_release_ready']) && empty($selectedTerm['is_certificate_released']))) {
                    $skippedMessages[] = $rowLabel . ': tanggal sertifikat belum bisa disimpan karena syarat release belum terpenuhi.';
                    continue;
                }
                $saveValue = $normalizedDate;
            } else {
                $saveValue = $this->MChecklist_Dokument_MyRep->normalizeCertificateValueForSave($certificateValue);
            }

            $terminPoType = strtoupper(trim((string) ($termin['po_type'] ?? 'CLUSTER')));
            $isStandalonePo = in_array($terminPoType, ['MAINFEEDER', 'FWA'], true);
            $updated = $isStandalonePo
                ? $this->MMainfeeder_MyRep->updateTerminCertificate(
                    (int) ($termin['id_po_termin'] ?? 0),
                    $saveValue,
                    (int) $this->session->userdata('id_user')
                )
                : $this->MChecklist_Dokument_MyRep->updateTerminCertificate(
                    (int) ($termin['id_po_termin'] ?? 0),
                    $saveValue,
                    (int) $this->session->userdata('id_user')
                );

            if ($updated) {
                $updatedCount++;
            } else {
                $skippedMessages[] = $rowLabel . ': gagal disimpan.';
            }
        }

        if ($updatedCount > 0) {
            $message = $updatedCount . ' status/tanggal sertifikat berhasil disimpan.';
            if (!empty($skippedMessages)) {
                $message .= ' ' . count($skippedMessages) . ' baris dilewati: ' . implode('; ', array_slice($skippedMessages, 0, 5));
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', 'Tidak ada status/tanggal sertifikat yang berhasil disimpan. ' . implode('; ', array_slice($skippedMessages, 0, 5)));
        }

        redirect('PO_MyRep');
    }

    public function getTerminBreakdownDetail()
    {
        if (empty($this->session->userdata('id_user'))) {
            return $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
        }

        $city = strtoupper(trim((string) $this->input->post('city')));
        $status = strtoupper(trim((string) $this->input->post('status')));
        $poType = strtoupper(trim((string) $this->input->post('po_type')));
        $metric = trim((string) $this->input->post('metric'));
        $termNo = (int) $this->input->post('term_no');

        $rows = $this->MPO_MyRep->getTerminBreakdownDetailRows($city, $status, $poType, $metric, $termNo);
        $metricLabel = strtoupper($poType) . ' - ' . strtoupper($metric);
        if ($metric === 'outstanding_term' && $termNo > 0) {
            $metricLabel = strtoupper($poType) . ' - OUTSTANDING ' . $termNo;
        } elseif ($metric === 'invoice_term' && $termNo > 0) {
            $metricLabel = strtoupper($poType) . ' - INVOICE ' . $termNo;
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'title' => $metricLabel,
                'rows' => $rows,
            ]));
    }

    public function getCertificateSummaryDetail()
    {
        if (empty($this->session->userdata('id_user'))) {
            return $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
        }

        $city = strtoupper(trim((string) $this->input->post('city')));
        $status = strtoupper(trim((string) $this->input->post('status')));
        $poType = strtoupper(trim((string) $this->input->post('po_type')));
        $termNo = (int) $this->input->post('term_no');
        $certificateStatus = strtoupper(trim((string) $this->input->post('certificate_status')));

        $rows = $this->MPO_MyRep->getCertificateDetailRows($city, $status, $poType, $termNo, $certificateStatus);
        $canReleaseCertificate = $this->myrepAccess->hasPermission('PO_MyRep', 'EDIT')
            && (
                strtoupper(trim((string) $this->session->userdata('lokasi_user'))) === 'HO'
                || (string) $this->session->userdata('nama_level') === 'Super Admin'
            );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'title' => $this->buildCertificateDetailTitle($poType, $termNo, $certificateStatus),
                'can_release_certificate' => $canReleaseCertificate,
                'rows' => $rows,
            ]));
    }

    public function getCertificateReleasedUninvoicedDetail()
    {
        if (empty($this->session->userdata('id_user'))) {
            return $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
        }

        $city = strtoupper(trim((string) $this->input->post('city')));
        $status = strtoupper(trim((string) $this->input->post('status')));
        $termNo = (int) $this->input->post('term_no');
        if ($termNo < 2 || $termNo > 5) {
            $termNo = 0;
        }

        $rows = $this->MPO_MyRep->getCertificateReleasedUninvoicedDetailRows($city, $status, $termNo);
        $title = $termNo > 0
            ? 'Detail Ready Invoice - Term ' . $termNo
            : 'Detail Ready Invoice - Semua Term';

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'title' => $title,
                'term_no' => $termNo,
                'ready_rows' => $rows['ready'] ?? [],
                'blocked_rows' => $rows['blocked'] ?? [],
            ]));
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];
            return checkdate($month, $day, $year)
                ? sprintf('%04d-%02d-%02d', $year, $month, $day)
                : null;
        }

        if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2,4})$/', $value, $matches)) {
            $first = (int) $matches[1];
            $second = (int) $matches[2];
            $year = (int) $matches[3];
            if ($year < 100) {
                $year += 2000;
            }

            if ($first > 12 && $second <= 12) {
                $day = $first;
                $month = $second;
            } elseif ($second > 12 && $first <= 12) {
                $month = $first;
                $day = $second;
            } else {
                $day = $first;
                $month = $second;
            }

            return checkdate($month, $day, $year)
                ? sprintf('%04d-%02d-%02d', $year, $month, $day)
                : null;
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function getDataTableRequest()
    {
        $order = (array) $this->input->post('order');
        $firstOrder = (array) ($order[0] ?? []);
        $search = (array) $this->input->post('search');
        $length = (int) $this->input->post('length');
        if ($length <= 0) {
            $length = 10;
        }

        return [
            'draw' => (int) $this->input->post('draw'),
            'start' => max(0, (int) $this->input->post('start')),
            'length' => min($length, 100),
            'search' => (string) ($search['value'] ?? ''),
            'order_column' => (int) ($firstOrder['column'] ?? 0),
            'order_dir' => (string) ($firstOrder['dir'] ?? 'asc'),
            'city' => strtoupper(trim((string) $this->input->post('city'))),
            'status' => strtoupper(trim((string) $this->input->post('status'))),
            'po_type_filter' => strtoupper(trim((string) $this->input->post('po_type_filter'))),
            'stage_filter' => strtoupper(trim((string) $this->input->post('stage_filter'))),
        ];
    }

    private function stageBadgeClass($stage)
    {
        $stage = strtoupper(trim((string) $stage));
        if ($stage === 'DP') {
            return 'danger';
        }
        if ($stage === 'ATP CW') {
            return 'warning';
        }
        if ($stage === 'FULL OPM') {
            return 'info';
        }
        if ($stage === 'RFS') {
            return 'primary';
        }
        if ($stage === 'FAC') {
            return 'success';
        }
        if ($stage === 'CLOSED') {
            return 'dark';
        }

        return 'secondary';
    }

    private function getPoDetailUrl(array $row)
    {
        $poType = strtoupper(trim((string) ($row['po_type'] ?? '')));
        $mainfeederId = (int) ($row['id_mainfeeder'] ?? 0);
        if (in_array($poType, ['MAINFEEDER', 'FWA'], true) || $mainfeederId > 0) {
            return base_url('PO_MyRep/mainfeeder/' . $mainfeederId);
        }

        return base_url('PO_MyRep/detail/' . (int) ($row['id_myrep_cluster'] ?? 0));
    }

    private function formatNumber($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }

    private function formatNumberOrDash($value)
    {
        return (float) $value == 0.0 ? '-' : $this->formatNumber($value);
    }

    private function jsonResponse(array $payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function emptyDataTableResponse()
    {
        return [
            'draw' => (int) $this->input->post('draw'),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
        ];
    }

    private function buildCertificateDetailTitle($poType, $termNo, $certificateStatus)
    {
        $labels = [
            'ALL' => 'Semua Sertifikat',
            'RELEASED' => 'Released',
            'READY' => 'Ready Release',
            'WAITING_ASTRI' => 'Waiting ASTRI',
            'WAITING_FAC' => 'Waiting FAC/BJT',
            'BLOCKED_BILLING' => 'Blocked Billing',
        ];
        $title = $labels[$certificateStatus] ?? 'Detail Sertifikat';
        if (in_array($poType, ['CLUSTER', 'SUBFEEDER'], true)) {
            $title .= ' - ' . $poType;
        }
        if ($termNo >= 2 && $termNo <= 5) {
            $title .= ' Term ' . $termNo;
        }

        return $title;
    }

    private function isFacTerminDue(array $termin)
    {
        $headerId = (int) ($termin['id_po_header'] ?? 0);
        if ($headerId <= 0 || !$this->db->table_exists('tb_myrep_po_termin')) {
            return false;
        }
        if (!$this->db->field_exists('sertifikat_invoice_date', 'tb_myrep_po_termin')) {
            return false;
        }

        $term4 = $this->db
            ->select('sertifikat_invoice_date')
            ->from('tb_myrep_po_termin')
            ->where('id_po_header', $headerId)
            ->where('termin_no', 4)
            ->limit(1)
            ->get()
            ->row_array();
        $rfsCertificateDate = $this->normalizeCertificateDateValue((string) ($term4['sertifikat_invoice_date'] ?? ''));
        if ($rfsCertificateDate === '') {
            return false;
        }

        return date('Y-m-d') >= date('Y-m-d', strtotime($rfsCertificateDate . ' +90 days'));
    }

    private function normalizeCertificateDateValue($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return date('Y-m-d', strtotime($value));
        }
        if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2,4})$/', $value, $matches)) {
            $first = (int) $matches[1];
            $second = (int) $matches[2];
            $year = (int) $matches[3];
            if ($year < 100) {
                $year += 2000;
            }

            if ($first > 12 && $second <= 12) {
                $day = $first;
                $month = $second;
            } else {
                $month = $first;
                $day = $second;
            }

            return checkdate($month, $day, $year)
                ? sprintf('%04d-%02d-%02d', $year, $month, $day)
                : '';
        }

        return '';
    }

    private function getAllowedCertificateStatusValues()
    {
        return [
            'REVISI',
            'FULL UPLOAD',
            'APPROVED 1',
            'LOGISTIK',
            'PLANNING',
            'TEAM LEADER',
            'WASPANG',
            'PERMIT',
        ];
    }

    private function normalizeNumber($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value) && !is_string($value)) {
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

        if ($normalized === '') {
            return 0;
        }

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
            if (count($parts) > 2 || strlen($lastPart) === 3 || strlen($lastPart) > 2) {
                $normalized = str_replace(',', '', $normalized);
            } else {
                $normalized = str_replace(',', '.', $normalized);
            }
        } elseif ($lastDot !== false) {
            $parts = explode('.', $normalized);
            $lastPart = end($parts);
            if (count($parts) > 2 || strlen($lastPart) === 3 || strlen($lastPart) > 2) {
                $normalized = str_replace('.', '', $normalized);
            }
        }

        $amount = (float) preg_replace('/[^\d.]/', '', $normalized);
        return $isNegative && $amount > 0 ? -1 * $amount : $amount;
    }

    private function normalizeTerminNoInput($value)
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '') {
            return 0;
        }

        if (preg_match('/([1-5])/', $value, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }
}
