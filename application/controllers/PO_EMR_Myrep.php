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
        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedStage = strtoupper(trim((string) $this->input->get('stage')));

        $data['title'] = 'PO EMR MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStage'] = $selectedStage;
        $data['isReady'] = $this->MPO_MyRep->tablesReady();
        $data['cityOptions'] = $data['isReady'] ? $this->MPO_MyRep->getEmrTargetCityOptions() : [];
        $data['poRows'] = $data['isReady'] ? $this->MPO_MyRep->getEmrTargetPoListRows($selectedCity, $selectedStage) : [];
        $data['summary'] = $this->buildSummary($data['poRows']);

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
        if (!$this->MPO_MyRep->tablesReady()) {
            show_404();
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedStage = strtoupper(trim((string) $this->input->get('stage')));
        $rows = $this->MPO_MyRep->getEmrTargetPoListRows($selectedCity, $selectedStage);

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
            'Status Target',
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
}
