<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PO_EMR_Myrep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MPO_MyRep');
        $this->load->helper('access');
        $this->guardEmrAccess();
    }

    public function index()
    {
        $selectedRegional = $this->normalizeFilterValues($this->input->get('regional'));
        $selectedCity = $this->normalizeFilterValues($this->input->get('city'));
        $selectedStage = $this->normalizeFilterValues($this->input->get('stage'));

        $data['title'] = 'PO Ekamas Mora Republik';
        $data['selectedRegional'] = $selectedRegional;
        $data['selectedCity'] = $selectedCity;
        $data['selectedStage'] = $selectedStage;
        $data['isReady'] = $this->MPO_MyRep->emrTargetReady();
        $data['regionalOptions'] = $data['isReady'] ? $this->MPO_MyRep->getEmrTargetRegionalOptions() : [];
        $data['allCityOptions'] = $data['isReady'] ? $this->MPO_MyRep->getEmrTargetCityOptions() : [];
        $data['cityOptions'] = $data['isReady'] ? $this->MPO_MyRep->getEmrTargetCityOptions($selectedRegional) : [];
        $data['cityOptionsByRegional'] = $data['isReady'] ? $this->MPO_MyRep->getEmrTargetCityOptionsByRegional() : [];
        $data['regionalOptionsByCity'] = $data['isReady'] ? $this->MPO_MyRep->getEmrTargetRegionalOptionsByCity() : [];
        $aggregateData = $data['isReady'] ? $this->MPO_MyRep->getEmrTargetAggregateData($selectedCity, $selectedStage, $selectedRegional) : [];
        $data['summary'] = $aggregateData['summary'] ?? $this->buildSummary([]);
        $data['terminBreakdownRows'] = $aggregateData['terminBreakdownRows'] ?? $this->buildTerminBreakdown([]);
        $data['terminPicSummaryRows'] = $data['isReady'] ? $this->MPO_MyRep->getEmrTargetTerminPicSummary($selectedCity, $selectedStage, $selectedRegional) : [];

        $this->load->view('Templates/01_Header_EMR', $data);
        $this->load->view('PO_EMR_Myrep/index', $data);
        $this->load->view('Templates/99_JS_EMR');
    }

    public function datatableMonitor()
    {
        if (!$this->MPO_MyRep->emrTargetReady()) {
            $this->jsonResponse($this->emptyDataTableResponse());
            return;
        }

        $request = $this->getDataTableRequest();
        $result = $this->MPO_MyRep->getEmrTargetClusterDataTable(
            $request['city'],
            $request['stage'],
            $request['regional'],
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir']
        );

        $rows = [];
        foreach ($result['rows'] as $index => $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $summaryStatus = strtoupper(trim((string) ($row['po_stage_status'] ?? 'NOT ISSUED')));
            $terminTotal = (int) ($row['termin_total_count'] ?? 0);
            $terminProgress = (int) ($row['termin_progress_count'] ?? $row['termin_paid_count'] ?? 0);
            $terminPercent = $terminTotal > 0 ? min(100, round(($terminProgress / $terminTotal) * 100)) : 0;
            $detailUrl = $this->buildDetailUrl($clusterId, $request['back_url']);

            $rows[] = [
                $request['start'] + $index + 1,
                '<strong><a href="' . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string) ($row['cluster_name'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</a></strong><div class="small text-muted">' . htmlspecialchars((string) ($row['team_name'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</div>',
                htmlspecialchars((string) ($row['city_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($this->formatAreaLabel((string) ($row['area_number'] ?? ''), (string) ($row['regional_name'] ?? '')), ENT_QUOTES, 'UTF-8'),
                '<span class="badge badge-info">' . htmlspecialchars((string) ($row['status_current'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</span>',
                '<div>Cluster: ' . (int) ($row['po_cluster_count'] ?? 0) . '</div><div>Subfeeder: ' . (int) ($row['po_subfeeder_count'] ?? 0) . '</div><div><span class="badge badge-' . $this->stageBadgeClass($summaryStatus) . '">' . htmlspecialchars($summaryStatus, ENT_QUOTES, 'UTF-8') . '</span></div>',
                $this->formatNumber((float) ($row['po_total_value'] ?? 0)),
                '<div class="po-mini-progress"><div class="po-mini-progress__head"><span>Termin Invoice</span><span>' . $terminPercent . '%</span></div><div class="po-mini-progress__track"><span style="width: ' . $terminPercent . '%;"></span></div><div class="po-mini-progress__meta"><span>' . $terminProgress . ' invoiced</span><span>' . $terminTotal . ' termin</span></div></div>',
                !empty($row['last_po_date']) ? htmlspecialchars((string) $row['last_po_date'], ENT_QUOTES, 'UTF-8') : '-',
                '<a href="' . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . '" class="btn btn-sm btn-primary">Detail</a>',
            ];
        }

        $this->jsonResponse([
            'draw' => $request['draw'],
            'recordsTotal' => (int) ($result['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
            'footer' => $this->formatPoFooterSummary((array) ($result['footer'] ?? [])),
            'data' => $rows,
        ]);
    }

    public function datatablePo()
    {
        if (!$this->MPO_MyRep->emrTargetReady()) {
            $this->jsonResponse($this->emptyDataTableResponse());
            return;
        }

        $request = $this->getDataTableRequest();
        $result = $this->MPO_MyRep->getEmrTargetPoDataTable(
            $request['city'],
            $request['stage'],
            $request['regional'],
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir'],
            $request['pic'],
            $request['term_stage'],
            $request['nro_status']
        );

        $rows = [];
        foreach ($result['rows'] as $index => $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $stage = strtoupper(trim((string) ($row['po_stage_status'] ?? '-')));
            $tipePo = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER')));
            $currentPic = strtoupper(trim((string) ($row['current_pic'] ?? '-')));
            $currentPicLabel = $this->picDisplayLabel($currentPic);
            $statusCurrentCell = $this->formatStatusCurrentCell($row);
            $detailUrl = $this->buildDetailUrl($clusterId, $request['back_url']);

            $rows[] = [
                $request['start'] + $index + 1,
                '<strong><a href="' . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string) ($row['po_number'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</a></strong><div class="small text-muted">' . htmlspecialchars((string) ($row['status_po'] ?? '-'), ENT_QUOTES, 'UTF-8') . '</div>',
                '<span class="badge badge-' . ($tipePo === 'SUBFEEDER' ? 'warning' : 'primary') . '">' . htmlspecialchars($tipePo, ENT_QUOTES, 'UTF-8') . '</span>',
                htmlspecialchars((string) ($row['po_category'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                !empty($row['po_date']) ? htmlspecialchars((string) $row['po_date'], ENT_QUOTES, 'UTF-8') : '-',
                htmlspecialchars((string) ($row['cluster_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($row['city_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($this->formatAreaLabel((string) ($row['area_number'] ?? ''), (string) ($row['regional_name'] ?? '')), ENT_QUOTES, 'UTF-8'),
                $statusCurrentCell,
                '<span class="badge badge-' . $this->stageBadgeClass($stage) . '">' . htmlspecialchars($stage, ENT_QUOTES, 'UTF-8') . '</span>',
                '<span class="badge badge-' . $this->picBadgeClass($currentPic) . '">' . htmlspecialchars($currentPicLabel, ENT_QUOTES, 'UTF-8') . '</span>',
                $this->formatNumber((float) ($row['current_termin_value'] ?? 0)),
                $this->formatNumber((float) ($row['po_value'] ?? 0)),
                (int) ($row['termin_progress_count'] ?? 0) . '/' . (int) ($row['termin_total_count'] ?? 0),
                $this->formatNumberOrDash((float) ($row['outstanding_total'] ?? 0)),
                '<a href="' . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . '" class="btn btn-sm btn-primary">Detail</a>',
            ];
        }

        $this->jsonResponse([
            'draw' => $request['draw'],
            'recordsTotal' => (int) ($result['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
            'footer' => $this->formatPoFooterSummary((array) ($result['footer'] ?? [])),
            'data' => $rows,
        ]);
    }

    public function detail($clusterId = 0)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            redirect('PO_EMR_Myrep');
            return;
        }

        $cluster = $this->MPO_MyRep->getEmrTargetClusterById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Detail PO target tidak ditemukan.');
            redirect('PO_EMR_Myrep');
            return;
        }

        $poHeaders = $this->MPO_MyRep->getEmrTargetPoHeadersByClusterId($clusterId);
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

        $data['title'] = 'Detail PO EMR MyRep';
        $data['cluster'] = $cluster;
        $data['poGroups'] = $poGroups;

        $this->load->view('Templates/01_Header_EMR', $data);
        $this->load->view('PO_EMR_Myrep/detail', $data);
        $this->load->view('Templates/99_JS_EMR');
    }

    public function downloadReport()
    {
        if (!$this->MPO_MyRep->emrTargetReady()) {
            show_404();
            return;
        }

        $selectedRegional = $this->normalizeFilterValues($this->input->get('regional'));
        $selectedCity = $this->normalizeFilterValues($this->input->get('city'));
        $selectedTermStage = $this->normalizeFilterValues($this->input->get('term_stage'));
        $selectedPic = $this->normalizeFilterValues($this->input->get('pic'));
        $selectedNroStatus = $this->normalizeFilterValues($this->input->get('nro_status'));
        $rows = $this->MPO_MyRep->getEmrTargetPoTerminReportRows($selectedCity, $selectedTermStage, $selectedRegional, $selectedPic, $selectedNroStatus);

        $filename = 'report_po_emr_myrep_target_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, [
            'No',
            'Nomor PO',
            'Tipe PO',
            'Kategori PO',
            'Tanggal PO',
            'Cluster',
            'Kode Cluster',
            'Kota',
            'Area',
            'Status PO',
            'On Target',
            'Status Current',
            'TERM PO',
            'PIC',
            'Status NRO',
            'Nilai Tagihan',
            'Nilai PO',
            'Termin Progress',
            'Outstanding',
            'Remark',
        ]);

        foreach ($rows as $index => $row) {
            fputcsv($output, [
                $index + 1,
                (string) ($row['po_number'] ?? ''),
                (string) ($row['po_type'] ?? ''),
                (string) ($row['po_category'] ?? ''),
                (string) ($row['po_date'] ?? ''),
                (string) ($row['cluster_name'] ?? ''),
                (string) ($row['cluster_code'] ?? ''),
                (string) ($row['city_name'] ?? ''),
                $this->formatAreaLabel((string) ($row['area_number'] ?? ''), (string) ($row['regional_name'] ?? '')),
                (string) ($row['status_po'] ?? ''),
                (int) ($row['on_target'] ?? 0) === 1 ? 'TRUE' : 'FALSE',
                $this->formatStatusCurrentExport($row),
                (string) ($row['po_stage_status'] ?? ''),
                $this->picDisplayLabel((string) ($row['current_pic'] ?? '')),
                (string) ($row['current_nro_status'] ?? ''),
                (float) ($row['current_termin_value'] ?? 0),
                (float) ($row['po_value'] ?? 0),
                (int) ($row['termin_progress_count'] ?? 0) . '/' . (int) ($row['termin_total_count'] ?? 0),
                (float) ($row['outstanding_total'] ?? 0),
                (string) ($row['remark_po'] ?? ''),
            ]);
        }

        fclose($output);
        exit;
    }

    private function guardEmrAccess()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if ((string) $this->session->userdata('nama_level') === 'Super Admin') {
            return;
        }

        if ($this->isEmrSession()) {
            return;
        }

        render_no_access('Anda tidak memiliki akses ke halaman PO EMR MyRep.');
    }

    private function isEmrSession()
    {
        $homebase = strtoupper(trim((string) $this->session->userdata('homebase')));
        $lokasiUser = strtoupper(trim((string) $this->session->userdata('lokasi_user')));
        return $homebase === 'EMR' || $lokasiUser === 'EMR';
    }

    private function normalizeFilterValues($value)
    {
        $values = is_array($value) ? $value : [$value];
        $normalized = [];

        foreach ($values as $item) {
            $item = strtoupper(trim((string) $item));
            if ($item !== '' && !in_array($item, $normalized, true)) {
                $normalized[] = $item;
            }
        }

        return $normalized;
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
            'regional' => $this->normalizeFilterValues($this->input->post('regional')),
            'city' => $this->normalizeFilterValues($this->input->post('city')),
            'stage' => $this->normalizeFilterValues($this->input->post('stage')),
            'pic' => $this->normalizeFilterValues($this->input->post('pic')),
            'term_stage' => $this->normalizeFilterValues($this->input->post('term_stage')),
            'nro_status' => $this->normalizeFilterValues($this->input->post('nro_status')),
            'back_url' => (string) $this->input->post('back_url'),
        ];
    }

    private function buildDetailUrl($clusterId, $backUrl = '')
    {
        $url = base_url('PO_EMR_Myrep/detail/' . (int) $clusterId);
        $backUrl = trim((string) $backUrl);
        if ($backUrl === '') {
            $backUrl = base_url('PO_EMR_Myrep');
        }

        if (strpos($backUrl, base_url('PO_EMR_Myrep')) !== 0) {
            $backUrl = base_url('PO_EMR_Myrep');
        }

        return $url . '?back=' . rawurlencode($backUrl);
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

    private function picBadgeClass($pic)
    {
        $pic = strtoupper(trim((string) $pic));
        if ($pic === 'AREA' || $pic === 'TKM - AREA') {
            return 'warning';
        }
        if ($pic === 'HO' || $pic === 'TKM - HO') {
            return 'primary';
        }
        if ($pic === 'DC EMR' || $pic === 'EMMR - DC') {
            return 'info';
        }
        if ($pic === 'EMR NRO' || $pic === 'NRO' || $pic === 'FLOW NRO' || $pic === 'EMMR - AREA' || $pic === 'WAITING CW ATP') {
            return 'danger';
        }
        if ($pic === 'WASPANG' || $pic === 'EMMR - WASPANG' || $pic === 'WAITING FAC') {
            return 'warning';
        }
        if ($pic === 'PLANNING' || $pic === 'EMMR - PLANNING') {
            return 'primary';
        }
        if ($pic === 'TL' || $pic === 'EMMR - TEAM LEADER' || $pic === 'FAC BELUM JATUH TEMPO') {
            return 'secondary';
        }
        if ($pic === 'LOGISTIK' || $pic === 'EMMR - LOGISTIK' || $pic === 'EMMR - DOKUMEN PERMIT') {
            return 'info';
        }
        if ($pic === 'TKM' || $pic === 'TKM - FINANCE') {
            return 'success';
        }
        if ($pic === 'CLOSED') {
            return 'dark';
        }
        return 'secondary';
    }

    private function picDisplayLabel($pic)
    {
        $pic = strtoupper(trim((string) $pic));
        if ($pic === 'AREA') {
            return 'TKM - AREA';
        }
        if ($pic === 'HO') {
            return 'TKM - HO';
        }
        if ($pic === 'EMR NRO' || $pic === 'NRO') {
            return 'EMMR - AREA';
        }
        if ($pic === 'DC EMR') {
            return 'EMMR - DC';
        }
        if ($pic === 'TL') {
            return 'EMMR - TEAM LEADER';
        }
        if ($pic === 'WASPANG') {
            return 'EMMR - WASPANG';
        }
        if ($pic === 'PLANNING') {
            return 'EMMR - PLANNING';
        }
        if ($pic === 'LOGISTIK') {
            return 'EMMR - LOGISTIK';
        }
        return $pic !== '' ? $pic : '-';
    }

    private function formatAreaLabel($areaNumber = '', $regionalName = '')
    {
        $areaName = $this->MPO_MyRep->getEmrTargetAreaLabel($areaNumber, $regionalName);
        return $areaName !== '' ? $areaName : '-';
    }

    private function formatStatusCurrentCell(array $row)
    {
        $statusCurrent = strtoupper(trim((string) ($row['status_current'] ?? '')));
        $statusDetail = strtoupper(trim((string) $this->MPO_MyRep->getEmrTargetDetailedStatusLabel($row)));

        if ($statusCurrent === '') {
            $statusCurrent = '-';
        }
        if ($statusDetail === '' || $statusDetail === $statusCurrent) {
            $statusDetail = '-';
        }

        return '<div><span class="badge badge-info">' . htmlspecialchars($statusCurrent, ENT_QUOTES, 'UTF-8') . '</span></div>'
            . '<div class="small text-muted mt-1">' . htmlspecialchars($statusDetail, ENT_QUOTES, 'UTF-8') . '</div>';
    }

    private function formatStatusCurrentExport(array $row)
    {
        $statusCurrent = strtoupper(trim((string) ($row['status_current'] ?? '')));
        $statusDetail = strtoupper(trim((string) $this->MPO_MyRep->getEmrTargetDetailedStatusLabel($row)));

        if ($statusCurrent === '') {
            $statusCurrent = '-';
        }
        if ($statusDetail === '' || $statusDetail === $statusCurrent) {
            return $statusCurrent;
        }

        return $statusCurrent . ' | ' . $statusDetail;
    }

    private function formatPoFooterSummary(array $footer)
    {
        $count = (int) ($footer['count'] ?? 0);
        $progressDone = (int) ($footer['termin_progress_count'] ?? 0);
        $progressTotal = (int) ($footer['termin_total_count'] ?? 0);

        return [
            'count' => $count,
            'count_label' => $this->formatNumber($count) . ' PO',
            'current_termin_value' => $this->formatNumber((float) ($footer['current_termin_value'] ?? 0)),
            'po_value' => $this->formatNumber((float) ($footer['po_value'] ?? 0)),
            'progress' => $progressDone . '/' . $progressTotal,
            'outstanding_total' => $this->formatNumber((float) ($footer['outstanding_total'] ?? 0)),
            'total_invoiced' => $this->formatNumber((float) ($footer['total_invoiced'] ?? 0)),
        ];
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

    private function buildSummary(array $rows)
    {
        $summary = [
            'total_po' => count($rows),
            'total_cluster' => 0,
            'total_po_value' => 0,
            'total_outstanding' => 0,
            'total_invoiced' => 0,
            'stage' => [
                'DP' => 0,
                'ATP CW' => 0,
                'FULL OPM' => 0,
                'RFS' => 0,
                'FAC' => 0,
                'CLOSED' => 0,
            ],
        ];

        $clusterSet = [];
        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            if ($clusterId > 0) {
                $clusterSet[$clusterId] = true;
            }
            $summary['total_po_value'] += (float) ($row['po_value'] ?? 0);
            $summary['total_outstanding'] += (float) ($row['outstanding_total'] ?? 0);
            $summary['total_invoiced'] += (float) ($row['total_invoiced'] ?? 0);

            $stage = strtoupper(trim((string) ($row['po_stage_status'] ?? '')));
            if (isset($summary['stage'][$stage])) {
                $summary['stage'][$stage]++;
            }
        }

        $summary['total_cluster'] = count($clusterSet);
        return $summary;
    }

    private function buildTerminBreakdown(array $rows)
    {
        $breakdown = [
            'CLUSTER' => [
                'po_type' => 'CLUSTER',
                'po_count' => 0,
                'total_po_value' => 0,
                'term_done_count' => 0,
                'termin_values' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'total_invoiced_value' => 0,
                'outstanding_value' => 0,
            ],
            'SUBFEEDER' => [
                'po_type' => 'SUBFEEDER',
                'po_count' => 0,
                'total_po_value' => 0,
                'term_done_count' => 0,
                'termin_values' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'total_invoiced_value' => 0,
                'outstanding_value' => 0,
            ],
        ];

        foreach ($rows as $row) {
            $type = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER')));
            $type = $type === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';

            $breakdown[$type]['po_count']++;
            $breakdown[$type]['total_po_value'] += (float) ($row['po_value'] ?? 0);
            $breakdown[$type]['term_done_count'] += (int) ($row['termin_progress_count'] ?? 0);
            $breakdown[$type]['total_invoiced_value'] += (float) ($row['total_invoiced'] ?? 0);
            $breakdown[$type]['outstanding_value'] += (float) ($row['outstanding_total'] ?? 0);

            $planPerTermin = (array) ($row['plan_invoice_per_termin'] ?? []);
            for ($i = 1; $i <= 5; $i++) {
                $breakdown[$type]['termin_values'][$i] += (float) ($planPerTermin[$i] ?? 0);
            }
        }

        return array_values($breakdown);
    }

    private function buildClusterRows(array $rows)
    {
        $clusters = [];
        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            if ($clusterId <= 0) {
                continue;
            }

            if (!isset($clusters[$clusterId])) {
                $clusters[$clusterId] = [
                    'id_myrep_cluster' => $clusterId,
                    'cluster_name' => (string) ($row['cluster_name'] ?? '-'),
                    'team_name' => (string) ($row['team_name'] ?? '-'),
                    'city_name' => (string) ($row['city_name'] ?? '-'),
                    'regional_name' => (string) ($row['regional_name'] ?? '-'),
                    'status_current' => (string) ($row['status_current'] ?? '-'),
                    'po_cluster_count' => 0,
                    'po_subfeeder_count' => 0,
                    'po_total_value' => 0,
                    'termin_total_count' => 0,
                    'termin_progress_count' => 0,
                    'termin_paid_count' => 0,
                    'last_po_date' => null,
                    'po_stage_status' => 'NOT ISSUED',
                ];
            }

            $type = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER')));
            if ($type === 'SUBFEEDER') {
                $clusters[$clusterId]['po_subfeeder_count']++;
            } else {
                $clusters[$clusterId]['po_cluster_count']++;
            }

            $clusters[$clusterId]['po_total_value'] += (float) ($row['po_value'] ?? 0);
            $clusters[$clusterId]['termin_total_count'] += (int) ($row['termin_total_count'] ?? 0);
            $clusters[$clusterId]['termin_progress_count'] += (int) ($row['termin_progress_count'] ?? 0);
            $clusters[$clusterId]['termin_paid_count'] += (int) ($row['termin_paid_count'] ?? 0);

            $poDate = (string) ($row['po_date'] ?? '');
            if ($poDate !== '' && (empty($clusters[$clusterId]['last_po_date']) || $poDate > (string) $clusters[$clusterId]['last_po_date'])) {
                $clusters[$clusterId]['last_po_date'] = $poDate;
                $clusters[$clusterId]['po_stage_status'] = (string) ($row['po_stage_status'] ?? 'NOT ISSUED');
            }
        }

        return array_values($clusters);
    }
}
