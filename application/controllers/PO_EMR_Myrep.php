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
        $data['poRows'] = $data['isReady'] ? $this->MPO_MyRep->getEmrTargetPoListRows($selectedCity, $selectedStage, $selectedRegional) : [];
        $data['clusterRows'] = $this->buildClusterRows($data['poRows']);
        $data['summary'] = $this->buildSummary($data['poRows']);
        $data['terminBreakdownRows'] = $this->buildTerminBreakdown($data['poRows']);

        $this->load->view('Templates/01_Header_EMR', $data);
        $this->load->view('PO_EMR_Myrep/index', $data);
        $this->load->view('Templates/99_JS_EMR');
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
        $selectedStage = $this->normalizeFilterValues($this->input->get('stage'));
        $rows = $this->MPO_MyRep->getEmrTargetPoListRows($selectedCity, $selectedStage, $selectedRegional);

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
            'Regional',
            'Status PO',
            'On Target',
            'Stage',
            'Nilai PO',
            'Termin Progress',
            'Outstanding',
            'Total Invoiced',
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
                (string) ($row['regional_name'] ?? ''),
                (string) ($row['status_po'] ?? ''),
                (int) ($row['on_target'] ?? 0) === 1 ? 'TRUE' : 'FALSE',
                (string) ($row['po_stage_status'] ?? ''),
                (float) ($row['po_value'] ?? 0),
                (int) ($row['termin_progress_count'] ?? 0) . '/' . (int) ($row['termin_total_count'] ?? 0),
                (float) ($row['outstanding_total'] ?? 0),
                (float) ($row['total_invoiced'] ?? 0),
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
                'total_po_value' => 0,
                'term_done_count' => 0,
                'termin_values' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'total_invoiced_value' => 0,
                'outstanding_value' => 0,
            ],
            'SUBFEEDER' => [
                'po_type' => 'SUBFEEDER',
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
