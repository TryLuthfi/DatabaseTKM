<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MMyRepublik_Project');
        $this->load->model('MMonitoring_RFS_MyRep');
        $this->load->model('MBatch_Approval_MyRep');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $year = (int) date('Y');
        $rows = $this->MMyRepublik_Project->tablesReady()
            ? $this->MMyRepublik_Project->getClusterRows('', '', 'HP')
            : [];

        $data['title'] = 'Dashboard My Republik';
        $data['dashboardYear'] = $year;
        $data['isReady'] = $this->MMyRepublik_Project->tablesReady();
        $data['overview'] = $this->MMyRepublik_Project->getOverview($rows);
        $data['statusCards'] = $this->MMyRepublik_Project->getStatusCards($rows, 'HP');
        $data['stageSummary'] = $this->buildStageSummary($rows);
        $data['citySummary'] = $this->buildCitySummary($rows);
        $data['topCities'] = array_slice($this->buildTkmCitySummary($year, $data['citySummary']), 0, 8);
        $data['monthlyTrend'] = $this->buildMonthlyTrend($year);
        $data['annualSummary'] = $this->buildAnnualTargetSummary($year);
        $data['batchSummary'] = $this->buildBatchSummary();
        $data['chartPayload'] = $this->buildChartPayload($data);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Dashboard/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    private function buildStageSummary(array $rows)
    {
        $summary = [
            'bak' => 0,
            'valsal' => 0,
            'batch' => 0,
            'drm' => 0,
            'implementasi' => 0,
            'rfs' => 0,
            'atp' => 0,
            'dokument' => 0,
            'total' => 0,
        ];

        foreach ($rows as $row) {
            $value = $this->resolveClusterHomepassValue($row);
            $stage = $this->resolveStageKey($row);
            if (isset($summary[$stage])) {
                $summary[$stage] += $value;
            }
            $summary['total'] += $value;
        }

        return $summary;
    }

    private function buildCitySummary(array $rows)
    {
        $cities = [];
        foreach ($rows as $row) {
            $city = strtoupper(trim((string) ($row['city_name'] ?? '-')));
            if ($city === '') {
                $city = '-';
            }

            if (!isset($cities[$city])) {
                $cities[$city] = [
                    'city_name' => $city,
                    'cluster_count' => 0,
                    'hp_total' => 0,
                    'hp_rfs_total' => 0,
                    'po_total' => 0,
                    'po_count' => 0,
                    'released_count' => 0,
                    'rfs_count' => 0,
                    'atp_count' => 0,
                ];
            }

            $status = strtoupper(trim((string) ($row['status_current'] ?? '')));
            $cities[$city]['cluster_count']++;
            $cities[$city]['hp_total'] += $this->resolveClusterHomepassValue($row);
            $cities[$city]['hp_rfs_total'] += (float) ($row['homepass_rfs'] ?? 0);
            $cities[$city]['po_total'] += (float) ($row['po_total_value'] ?? 0);
            $cities[$city]['po_count'] += (int) ($row['po_count'] ?? 0);
            if ($status === 'RELEASED') {
                $cities[$city]['released_count']++;
            }
            if ($status === 'RFS') {
                $cities[$city]['rfs_count']++;
            }
            if ($status === 'ATP') {
                $cities[$city]['atp_count']++;
            }
        }

        $result = array_values($cities);
        usort($result, static function ($a, $b) {
            return (float) ($b['hp_rfs_total'] ?? 0) <=> (float) ($a['hp_rfs_total'] ?? 0);
        });

        return $result;
    }

    private function buildTkmCitySummary($year, array $clusterCitySummary)
    {
        if (
            !$this->db->table_exists('tb_rfs_myrep_monthly_target') ||
            !$this->db->table_exists('tb_rfs_myrep_cluster') ||
            !$this->db->table_exists('tb_rfs_myrep_claim')
        ) {
            return [];
        }

        $clusterMap = [];
        foreach ($clusterCitySummary as $cityRow) {
            $cityName = strtoupper(trim((string) ($cityRow['city_name'] ?? '-')));
            if ($cityName !== '') {
                $clusterMap[$cityName] = $cityRow;
            }
        }

        $rows = $this->MMonitoring_RFS_MyRep->getAnnualCitySummary((int) $year, 1, 12, '');
        foreach ($rows as &$row) {
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '-')));
            $clusterMeta = $clusterMap[$cityName] ?? [];
            $row['city_name'] = $cityName !== '' ? $cityName : '-';
            $row['cluster_count'] = (int) ($clusterMeta['cluster_count'] ?? 0);
            $row['po_total'] = (float) ($clusterMeta['po_total'] ?? 0);
            $row['po_count'] = (int) ($clusterMeta['po_count'] ?? 0);
            $row['rfs_count'] = (int) ($clusterMeta['rfs_count'] ?? 0);
            $row['atp_count'] = (int) ($clusterMeta['atp_count'] ?? 0);
            $row['hp_rfs_total'] = (float) ($row['realization_tkm'] ?? 0);
        }
        unset($row);

        usort($rows, static function ($a, $b) {
            return (float) ($b['hp_rfs_total'] ?? 0) <=> (float) ($a['hp_rfs_total'] ?? 0);
        });

        return $rows;
    }

    private function buildMonthlyTrend($year)
    {
        $monthNames = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];
        $trend = [];
        foreach ($monthNames as $month => $label) {
            $trend[$month] = [
                'label' => $label,
                'target_myrep' => 0,
                'realization_myrep' => 0,
                'target_tkm' => 0,
                'realization_tkm' => 0,
            ];
        }

        if (!$this->db->table_exists('tb_rfs_myrep_monthly_target')) {
            return array_values($trend);
        }

        $rows = $this->MMonitoring_RFS_MyRep->getTargetOptions($year, 1, 12, '');
        foreach ($rows as $row) {
            $month = (int) ($row['month_num'] ?? 0);
            if (!isset($trend[$month])) {
                continue;
            }
            $trend[$month]['target_myrep'] += (float) ($row['target_myrep'] ?? 0);
            $trend[$month]['realization_myrep'] += (float) ($row['realization_myrep'] ?? 0);
            $trend[$month]['target_tkm'] += (float) ($row['target_rkap'] ?? 0);
        }

        if ($this->db->table_exists('tb_rfs_myrep_claim')) {
            $claimRows = $this->db
                ->select('claim_month, COALESCE(SUM(claim_qty), 0) AS realization_tkm', false)
                ->from('tb_rfs_myrep_claim')
                ->where('claim_year', $year)
                ->where('claim_month >=', 1)
                ->where('claim_month <=', 12)
                ->where('status_claim', 'APPROVED')
                ->group_by('claim_month')
                ->get()
                ->result_array();

            foreach ($claimRows as $claimRow) {
                $month = (int) ($claimRow['claim_month'] ?? 0);
                if (!isset($trend[$month])) {
                    continue;
                }
                $trend[$month]['realization_tkm'] += (float) ($claimRow['realization_tkm'] ?? 0);
            }
        }

        return array_values($trend);
    }

    private function buildAnnualTargetSummary($year)
    {
        $summary = [
            'target_myrep' => 0,
            'realization_myrep' => 0,
            'target_tkm' => 0,
            'realization_tkm' => 0,
            'pct_myrep' => 0,
            'pct_tkm' => 0,
            'myrep_vs_tkm' => 0,
        ];

        if (
            !$this->db->table_exists('tb_rfs_myrep_monthly_target') ||
            !$this->db->table_exists('tb_rfs_myrep_cluster') ||
            !$this->db->table_exists('tb_rfs_myrep_claim')
        ) {
            return $summary;
        }

        return $this->MMonitoring_RFS_MyRep->getAnnualSummary($year, 1, 12, '');
    }

    private function buildBatchSummary()
    {
        $summary = [
            'clusters' => 0,
            'nominal_emr' => 0,
            'nominal_release' => 0,
            'hp_donasi' => 0,
            'waiting' => 0,
            'released' => 0,
            'release_ratio' => 0,
        ];

        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
            return $summary;
        }

        $rows = $this->MBatch_Approval_MyRep->getBatchRows('', '');
        foreach ($rows as $row) {
            $summary['clusters']++;
            $summary['nominal_emr'] += (float) ($row['nominal_nego_emr'] ?? 0);
            $summary['nominal_release'] += (float) ($row['nominal_release_finance'] ?? 0);
            $summary['hp_donasi'] += (float) ($row['hp_donasi'] ?? 0);

            $status = strtoupper(trim((string) ($row['staging_status'] ?? $row['status_current'] ?? '')));
            if (in_array($status, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE'], true)) {
                $summary['waiting']++;
            }
            if (in_array($status, ['RELEASED', 'DONE BATCH APPROVAL'], true)) {
                $summary['released']++;
            }
        }

        $summary['release_ratio'] = $summary['nominal_emr'] > 0
            ? min(($summary['nominal_release'] / $summary['nominal_emr']) * 100, 100)
            : 0;

        return $summary;
    }

    private function buildChartPayload(array $data)
    {
        $statusCards = (array) ($data['statusCards'] ?? []);
        $stageSummary = (array) ($data['stageSummary'] ?? []);
        $monthlyTrend = (array) ($data['monthlyTrend'] ?? []);
        $topCities = (array) ($data['topCities'] ?? []);
        $batchSummary = (array) ($data['batchSummary'] ?? []);

        return [
            'status' => [
                'labels' => array_map(static function ($row) {
                    return (string) ($row['status'] ?? '-');
                }, $statusCards),
                'clusters' => array_map(static function ($row) {
                    return (int) ($row['cluster_count'] ?? 0);
                }, $statusCards),
                'hp' => array_map(static function ($row) {
                    return (float) ($row['metric_total'] ?? 0);
                }, $statusCards),
            ],
            'stage' => [
                'labels' => ['BAK', 'VALSAL', 'Batch', 'DRM', 'Implementasi', 'RFS', 'ATP', 'Dokument'],
                'hp' => [
                    (float) ($stageSummary['bak'] ?? 0),
                    (float) ($stageSummary['valsal'] ?? 0),
                    (float) ($stageSummary['batch'] ?? 0),
                    (float) ($stageSummary['drm'] ?? 0),
                    (float) ($stageSummary['implementasi'] ?? 0),
                    (float) ($stageSummary['rfs'] ?? 0),
                    (float) ($stageSummary['atp'] ?? 0),
                    (float) ($stageSummary['dokument'] ?? 0),
                ],
            ],
            'monthly' => [
                'labels' => array_map(static function ($row) {
                    return (string) ($row['label'] ?? '-');
                }, $monthlyTrend),
                'target_myrep' => array_map(static function ($row) {
                    return (float) ($row['target_myrep'] ?? 0);
                }, $monthlyTrend),
                'realization_myrep' => array_map(static function ($row) {
                    return (float) ($row['realization_myrep'] ?? 0);
                }, $monthlyTrend),
                'target_tkm' => array_map(static function ($row) {
                    return (float) ($row['target_tkm'] ?? 0);
                }, $monthlyTrend),
                'realization_tkm' => array_map(static function ($row) {
                    return (float) ($row['realization_tkm'] ?? 0);
                }, $monthlyTrend),
            ],
            'city' => [
                'labels' => array_map(static function ($row) {
                    return (string) ($row['city_name'] ?? '-');
                }, $topCities),
                'hp' => array_map(static function ($row) {
                    return (float) ($row['hp_rfs_total'] ?? 0);
                }, $topCities),
                'clusters' => array_map(static function ($row) {
                    return (int) ($row['cluster_count'] ?? 0);
                }, $topCities),
            ],
            'batch' => [
                'labels' => ['Released', 'Waiting'],
                'clusters' => [
                    (int) ($batchSummary['released'] ?? 0),
                    (int) ($batchSummary['waiting'] ?? 0),
                ],
            ],
        ];
    }

    private function resolveStageKey(array $row)
    {
        $statusCurrent = strtoupper(trim((string) ($row['status_current'] ?? '')));
        $statusDisplay = strtoupper(trim((string) ($row['status_current_display'] ?? $statusCurrent)));
        $statusDrm = strtoupper(trim((string) ($row['status_drm'] ?? '')));

        if (in_array($statusCurrent, ['DRAFT', 'BA OPEN', 'BAK'], true)) {
            return 'bak';
        }
        if ($statusCurrent === 'VALSAL') {
            return 'valsal';
        }
        if (in_array($statusCurrent, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'], true)) {
            return 'batch';
        }
        if ($statusCurrent === 'DRM') {
            return 'drm';
        }
        if ($statusDisplay === 'IMPLEMENTASI' || ($statusCurrent === 'DONE' && strpos($statusDrm, 'IMPLEMENTASI') !== false)) {
            return 'implementasi';
        }
        if ($statusCurrent === 'RFS') {
            return 'rfs';
        }
        if ($statusCurrent === 'ATP') {
            return 'atp';
        }
        if (in_array($statusCurrent, ['CHECKLIST DOKUMENT', 'DONE'], true)) {
            return 'dokument';
        }

        return 'bak';
    }

    private function resolveClusterHomepassValue(array $row)
    {
        $status = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
        $hpPlan = (float) ($row['hp_plan'] ?? 0);
        $hpBak = (float) ($row['homepass_bak'] ?? 0);
        $hpValsal = (float) ($row['homepass_valsal'] ?? 0);
        $hpDonasi = (float) ($row['hp_donasi'] ?? 0);
        $hpDrm = (float) ($row['homepass_drm'] ?? 0);
        $hpRfs = (float) ($row['homepass_rfs'] ?? 0);

        if (in_array($status, ['DRAFT', 'BA OPEN', 'BAK'], true)) {
            return $hpBak > 0 ? $hpBak : $hpPlan;
        }
        if ($status === 'VALSAL') {
            return $hpValsal > 0 ? $hpValsal : ($hpBak > 0 ? $hpBak : $hpPlan);
        }
        if (in_array($status, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'], true)) {
            return $hpDonasi > 0 ? $hpDonasi : ($hpValsal > 0 ? $hpValsal : ($hpBak > 0 ? $hpBak : $hpPlan));
        }
        if ($status === 'DRM') {
            return $hpDrm > 0 ? $hpDrm : ($hpDonasi > 0 ? $hpDonasi : ($hpValsal > 0 ? $hpValsal : ($hpBak > 0 ? $hpBak : $hpPlan)));
        }
        if (in_array($status, ['RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'], true)) {
            return $hpRfs > 0 ? $hpRfs : ($hpDrm > 0 ? $hpDrm : ($hpDonasi > 0 ? $hpDonasi : ($hpValsal > 0 ? $hpValsal : ($hpBak > 0 ? $hpBak : $hpPlan))));
        }

        return $hpPlan;
    }
}
