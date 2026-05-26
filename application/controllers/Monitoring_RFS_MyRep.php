<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Monitoring_RFS_MyRep extends CI_Controller
{
    public function __construct()
    {

    error_reporting(0);
        ini_set('display_errors', 0);
        
        parent::__construct();
        $this->load->model('MMonitoring_RFS_MyRep');
        $this->load->library('upload');
        $this->load->library('Myrep_notification_service', null, 'myrepNotifier');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('Monitoring_RFS_MyRep');
            $this->myrepAccess->enforceByMethod('Monitoring_RFS_MyRep', (string) $this->router->fetch_method(), [
                'submitClaim' => 'TAMBAH',
                'updateClaimStatus' => 'APPROVAL',
                'previewClusterImport' => 'TAMBAH',
            ]);
        }
    }

    public function index()
    {

    error_reporting(0);
        ini_set('display_errors', 0);
    
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedYear = (int) $this->input->get('year');
        $selectedStartMonth = (int) $this->input->get('start_month');
        $selectedEndMonth = (int) $this->input->get('end_month');
        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedClaimStartDate = $this->normalizeDateString($this->input->get('claim_start_date'));
        $selectedClaimEndDate = $this->normalizeDateString($this->input->get('claim_end_date'));

        if ($selectedYear <= 0) {
            $selectedYear = (int) date('Y');
        }

        if ($selectedStartMonth < 1 || $selectedStartMonth > 12) {
            $selectedStartMonth = (int) date('n');
        }

        if ($selectedEndMonth < 1 || $selectedEndMonth > 12) {
            $selectedEndMonth = (int) date('n');
        }

        if ($selectedStartMonth > $selectedEndMonth) {
            $temp = $selectedStartMonth;
            $selectedStartMonth = $selectedEndMonth;
            $selectedEndMonth = $temp;
        }

        if ($selectedClaimStartDate !== '' && $selectedClaimEndDate === '') {
            $selectedClaimEndDate = $selectedClaimStartDate;
        }
        if ($selectedClaimEndDate !== '' && $selectedClaimStartDate === '') {
            $selectedClaimStartDate = $selectedClaimEndDate;
        }
        if ($selectedClaimStartDate !== '' && $selectedClaimEndDate !== '' && strtotime($selectedClaimStartDate) > strtotime($selectedClaimEndDate)) {
            $tempDate = $selectedClaimStartDate;
            $selectedClaimStartDate = $selectedClaimEndDate;
            $selectedClaimEndDate = $tempDate;
        }

        $data['title'] = 'Monitoring RFS MYREP';
        $data['selectedYear'] = $selectedYear;
        $data['selectedStartMonth'] = $selectedStartMonth;
        $data['selectedEndMonth'] = $selectedEndMonth;
        $data['selectedMonth'] = $selectedEndMonth;
        $data['selectedCity'] = $selectedCity;
        $data['selectedClaimStartDate'] = $selectedClaimStartDate;
        $data['selectedClaimEndDate'] = $selectedClaimEndDate;
        $data['monthLabels'] = $this->getMonthLabels();
        $data['selectedPeriodLabel'] = $this->buildPeriodLabel($selectedStartMonth, $selectedEndMonth);

        $this->MMonitoring_RFS_MyRep->syncMyrepCompatibilityBridge($selectedYear, $selectedEndMonth, $selectedCity);

        $data['monthColumns'] = $this->MMonitoring_RFS_MyRep->getMonthColumnsInRange($selectedYear, $selectedStartMonth, $selectedEndMonth);
        $data['annualSummary'] = $this->MMonitoring_RFS_MyRep->getAnnualSummary($selectedYear, 1, 12, '');
        $data['annualCitySummary'] = $this->MMonitoring_RFS_MyRep->getAnnualCitySummary($selectedYear, 1, 12, '');
        $data['monthlySummary'] = $this->MMonitoring_RFS_MyRep->getMonthlySummary($selectedYear, $selectedStartMonth, $selectedEndMonth, $selectedCity);
        $data['regionalSummary'] = $this->MMonitoring_RFS_MyRep->getGroupedKpiSummary($selectedYear, $selectedStartMonth, $selectedEndMonth, 'regional_name', $selectedCity);
        $data['smSummary'] = $this->MMonitoring_RFS_MyRep->getGroupedKpiSummary($selectedYear, $selectedStartMonth, $selectedEndMonth, 'sm', $selectedCity);
        $data['teamSummary'] = $this->MMonitoring_RFS_MyRep->getGroupedKpiSummary($selectedYear, $selectedStartMonth, $selectedEndMonth, 'team_name', $selectedCity);
        $data['threeMonthSummary'] = $this->MMonitoring_RFS_MyRep->getThreeMonthSummary($selectedYear, $selectedStartMonth, $selectedEndMonth, $selectedCity);
        $data['clusterList'] = $this->MMonitoring_RFS_MyRep->getClustersWithPlan($selectedYear, $selectedStartMonth, $selectedEndMonth, $selectedCity);
        $data['claimList'] = $this->MMonitoring_RFS_MyRep->getClaims($selectedYear, $selectedStartMonth, $selectedEndMonth, $selectedCity);
        $data['claimApprovalList'] = $this->MMonitoring_RFS_MyRep->getClaims($selectedYear, $selectedStartMonth, $selectedEndMonth, $selectedCity, $selectedClaimStartDate, $selectedClaimEndDate);
        $data['cityOptions'] = $this->MMonitoring_RFS_MyRep->getCityOptions();
        $data['targetOptions'] = $this->MMonitoring_RFS_MyRep->getTargetOptions($selectedYear, $selectedStartMonth, $selectedEndMonth, $selectedCity);
        $data['allTargetOptions'] = $this->MMonitoring_RFS_MyRep->getTargetOptions($selectedYear, 1, 12, '');
        $data['flashMessage'] = $this->session->flashdata('monitoring_rfs_myrep_message');
        $data['flashError'] = $this->session->flashdata('monitoring_rfs_myrep_error');

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Monitoring_RFS_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveMonthlyTarget()
    {

        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $year = (int) $this->input->post('year');
        $month = (int) $this->input->post('month');
        $cities = $this->input->post('city');
        $targetMyrepRows = $this->input->post('target_myrep');
        $targetRkapRows = $this->input->post('target_rkap');
        $realizationRows = $this->input->post('realization_myrep');
        $realizationAdditionalRows = $this->input->post('realization_myrep_additional');
        $filterCity = strtoupper(trim((string) $this->input->post('filter_city')));
        $filterStartMonth = (int) $this->input->post('filter_start_month');
        $filterEndMonth = (int) $this->input->post('filter_end_month');

        if ($year <= 0 || $month < 1 || $month > 12 || !is_array($cities)) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Data target bulanan batch belum lengkap.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity));
            return;
        }

        $savedCount = 0;
        $totalRows = max(
            count($cities),
            is_array($targetMyrepRows) ? count($targetMyrepRows) : 0,
            is_array($targetRkapRows) ? count($targetRkapRows) : 0,
            is_array($realizationRows) ? count($realizationRows) : 0,
            is_array($realizationAdditionalRows) ? count($realizationAdditionalRows) : 0
        );

        for ($i = 0; $i < $totalRows; $i++) {
            $city = strtoupper(trim((string) ($cities[$i] ?? '')));
            $targetMyrep = $this->normalizeNumber($targetMyrepRows[$i] ?? 0);
            $targetRkap = $this->normalizeNumber($targetRkapRows[$i] ?? 0);
            $realization = $this->normalizeNumber($realizationRows[$i] ?? 0);
            $realizationAdditional = $this->normalizeNumber($realizationAdditionalRows[$i] ?? 0);

            if ($city === '' && $targetMyrep <= 0 && $targetRkap <= 0 && $realization <= 0 && $realizationAdditional <= 0) {
                continue;
            }

            if ($city === '') {
                continue;
            }

            $payload = [
                'year_num' => $year,
                'month_num' => $month,
                'city_name' => $city,
                'target_myrep' => $targetMyrep,
                'target_rkap' => $targetRkap,
                'realization_myrep' => $realization,
                'realization_myrep_additional' => $realizationAdditional,
                'updated_by' => (int) $this->session->userdata('id_user')
            ];

            $this->MMonitoring_RFS_MyRep->upsertMonthlyTarget($payload);
            $savedCount++;
        }

        if ($savedCount <= 0) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Tidak ada target yang berhasil disimpan.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity));
            return;
        }

        $this->session->set_flashdata('monitoring_rfs_myrep_message', $savedCount . ' data target dan realisasi MyRep berhasil disimpan.');

        redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity));
    }

    public function saveCityMaster()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $year = (int) $this->input->post('year');
        $month = (int) $this->input->post('month');
        $cities = $this->input->post('city');
        $regionalRows = $this->input->post('regional_name');
        $provinceRows = $this->input->post('province_name');
        $teamRows = $this->input->post('team_name');
        $chiefRows = $this->input->post('chief');
        $rpmRows = $this->input->post('rpm');
        $smRows = $this->input->post('sm');
        $spvRows = $this->input->post('spv');
        $filterCity = strtoupper(trim((string) $this->input->post('filter_city')));
        $filterStartMonth = (int) $this->input->post('filter_start_month');
        $filterEndMonth = (int) $this->input->post('filter_end_month');

        if ($year <= 0 || $month < 1 || $month > 12 || !is_array($cities)) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Data kota batch tidak valid.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity));
            return;
        }

        $savedCount = 0;
        $totalRows = max(
            count($cities),
            is_array($regionalRows) ? count($regionalRows) : 0,
            is_array($provinceRows) ? count($provinceRows) : 0,
            is_array($teamRows) ? count($teamRows) : 0,
            is_array($chiefRows) ? count($chiefRows) : 0,
            is_array($rpmRows) ? count($rpmRows) : 0,
            is_array($smRows) ? count($smRows) : 0,
            is_array($spvRows) ? count($spvRows) : 0
        );

        for ($i = 0; $i < $totalRows; $i++) {
            $city = strtoupper(trim((string) ($cities[$i] ?? '')));

            if ($city === '') {
                continue;
            }

            $payload = [
                'year_num' => $year,
                'month_num' => $month,
                'city_name' => $city,
                'regional_name' => trim((string) ($regionalRows[$i] ?? '')),
                'province_name' => trim((string) ($provinceRows[$i] ?? '')),
                'team_name' => trim((string) ($teamRows[$i] ?? '')),
                'chief' => trim((string) ($chiefRows[$i] ?? '')),
                'rpm' => trim((string) ($rpmRows[$i] ?? '')),
                'sm' => trim((string) ($smRows[$i] ?? '')),
                'spv' => trim((string) ($spvRows[$i] ?? '')),
                'updated_by' => (int) $this->session->userdata('id_user')
            ];

            $this->MMonitoring_RFS_MyRep->upsertMonthlyTarget($payload);
            $savedCount++;
        }

        if ($savedCount <= 0) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Tidak ada data kota yang berhasil disimpan.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity));
            return;
        }

        $this->session->set_flashdata('monitoring_rfs_myrep_message', $savedCount . ' data kota berhasil disimpan.');
        redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity));
    }

    public function saveCluster()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $year = (int) $this->input->post('year');
        $month = (int) $this->input->post('month');
        $idTargets = $this->input->post('id_target');
        $clusterNames = $this->input->post('cluster_name');
        $homepasses = $this->input->post('homepass');
        $filterCity = strtoupper(trim((string) $this->input->post('filter_city')));
        $filterStartMonth = (int) $this->input->post('filter_start_month');
        $filterEndMonth = (int) $this->input->post('filter_end_month');

        if (!is_array($idTargets) || !is_array($clusterNames) || !is_array($homepasses)) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Data cluster batch tidak valid.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity));
            return;
        }

        $createdCount = 0;
        $duplicateClusters = [];
        $totalRows = max(count($idTargets), count($clusterNames), count($homepasses));

        for ($i = 0; $i < $totalRows; $i++) {
            $idTarget = (int) ($idTargets[$i] ?? 0);
            $clusterName = trim((string) ($clusterNames[$i] ?? ''));
            $homepass = (int) $this->normalizeNumber($homepasses[$i] ?? 0);

            if ($idTarget <= 0 && $clusterName === '' && $homepass <= 0) {
                continue;
            }

            if ($idTarget <= 0 || $clusterName === '') {
                continue;
            }

            $selectedTarget = $this->MMonitoring_RFS_MyRep->getTargetById($idTarget);
            if (!$selectedTarget) {
                continue;
            }

            if ($this->MMonitoring_RFS_MyRep->clusterExistsForTarget($idTarget, $clusterName)) {
                $duplicateClusters[] = strtoupper(trim(($selectedTarget['city_name'] ?? '') . ' - ' . $clusterName));
                continue;
            }

            $payload = [
                'id_target' => $idTarget,
                'cluster_name' => $clusterName,
                'status_rfs' => 'NY RFS',
                'homepass' => $homepass,
                'created_by' => (int) $this->session->userdata('id_user')
            ];

            $clusterId = $this->MMonitoring_RFS_MyRep->createCluster($payload);
            if ($clusterId) {
                $createdCount++;
            }
        }

        if ($createdCount <= 0) {
            $message = 'Tidak ada cluster yang berhasil disimpan. Pastikan kota, nama cluster, dan homepass sudah terisi.';
            if (!empty($duplicateClusters)) {
                $message .= ' Duplicate: ' . implode(', ', array_unique($duplicateClusters));
            }
            $this->session->set_flashdata('monitoring_rfs_myrep_error', $message);
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity));
            return;
        }

        $message = $createdCount . ' cluster berhasil ditambahkan.';
        if (!empty($duplicateClusters)) {
            $message .= ' Duplicate dilewati: ' . implode(', ', array_unique($duplicateClusters));
        }
        $this->session->set_flashdata('monitoring_rfs_myrep_message', $message);
        redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity));
    }

    public function previewClusterImport()
    {
        if (empty($this->session->userdata('id_user'))) {
            echo json_encode([
                'status' => false,
                'message' => 'Session login tidak ditemukan'
            ]);
            return;
        }

        $year = (int) $this->input->post('year');
        $month = (int) $this->input->post('month');

        if ($year <= 0 || $month < 1 || $month > 12) {
            echo json_encode([
                'status' => false,
                'message' => 'Periode import cluster tidak valid'
            ]);
            return;
        }

        $config['upload_path'] = './uploads/monitoring_rfs_myrep/imports/';
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size'] = 4096;
        $config['encrypt_name'] = true;

        if (!is_dir($config['upload_path'])) {
            @mkdir($config['upload_path'], 0777, true);
        }

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_excel')) {
            echo json_encode([
                'status' => false,
                'message' => strip_tags($this->upload->display_errors('', ''))
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
                'message' => 'File import cluster tidak bisa dibaca'
            ]);
            return;
        }

        @unlink($filePath);

        if (count($sheetData) < 2) {
            echo json_encode([
                'status' => false,
                'message' => 'File import cluster tidak memiliki data'
            ]);
            return;
        }

        $headerRow = reset($sheetData);
        $headerMap = [];
        foreach ($headerRow as $column => $header) {
            $mappedField = $this->parseClusterExcelHeader($header);
            if ($mappedField) {
                $headerMap[$column] = $mappedField;
            }
        }

        foreach (['city_name', 'cluster_name', 'homepass'] as $requiredField) {
            if (!in_array($requiredField, $headerMap, true)) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Header file wajib memuat ' . $requiredField
                ]);
                return;
            }
        }

        $previewRows = [];
        $validRows = [];
        $errorRows = [];

        foreach ($sheetData as $rowIndex => $excelRow) {
            if ($rowIndex === 1) {
                continue;
            }

            $row = [];
            foreach ($headerMap as $column => $field) {
                $row[$field] = isset($excelRow[$column]) ? trim((string) $excelRow[$column]) : '';
            }

            $isBlank = true;
            foreach ($row as $value) {
                if (trim((string) $value) !== '') {
                    $isBlank = false;
                    break;
                }
            }

            if ($isBlank) {
                continue;
            }

            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            $clusterName = trim((string) ($row['cluster_name'] ?? ''));
            $homepass = (int) $this->normalizeNumber($row['homepass'] ?? 0);
            $errors = [];

            if ($cityName === '') {
                $errors[] = 'Kota wajib diisi';
            }

            if ($clusterName === '') {
                $errors[] = 'Nama cluster wajib diisi';
            }

            if ($homepass < 0) {
                $errors[] = 'Homepass tidak valid';
            }

            $target = null;
            if ($cityName !== '') {
                $target = $this->MMonitoring_RFS_MyRep->getTargetByPeriodCity($year, $month, $cityName);
                if (!$target) {
                    $errors[] = 'Target bulanan kota belum tersedia untuk periode ini';
                }
            }

            if ($target && $this->MMonitoring_RFS_MyRep->clusterExistsForTarget((int) $target['id_target'], $clusterName)) {
                $errors[] = 'Duplicate cluster pada target bulan ini';
            }

            $prepared = [
                'city_name' => $cityName,
                'cluster_name' => $clusterName,
                'homepass' => $homepass,
                'id_target' => $target['id_target'] ?? 0
            ];

            $previewRows[] = [
                'row_number' => $rowIndex,
                'city_name' => $cityName,
                'cluster_name' => $clusterName,
                'homepass' => $homepass,
                'status' => empty($errors) ? 'valid' : 'invalid',
                'message' => empty($errors) ? 'Siap diimport' : implode(', ', array_unique($errors))
            ];

            if (empty($errors)) {
                $validRows[] = $prepared;
            } else {
                $errorRows[] = [
                    'row_number' => $rowIndex,
                    'errors' => array_values(array_unique($errors))
                ];
            }
        }

        echo json_encode([
            'status' => true,
            'message' => count($validRows) . ' data valid dari ' . count($previewRows) . ' baris',
            'rows' => $previewRows,
            'valid_rows' => $validRows,
            'error_rows' => $errorRows
        ]);
    }

    public function saveImportedClusters()
    {
        if (empty($this->session->userdata('id_user'))) {
            echo json_encode([
                'status' => false,
                'message' => 'Session login tidak ditemukan'
            ]);
            return;
        }

        $rowsJson = $this->input->post('rows_json');
        $rows = json_decode($rowsJson, true);

        if (empty($rows) || !is_array($rows)) {
            echo json_encode([
                'status' => false,
                'message' => 'Tidak ada data import cluster yang siap disimpan'
            ]);
            return;
        }

        $inserted = 0;

        foreach ($rows as $row) {
            $idTarget = (int) ($row['id_target'] ?? 0);
            $clusterName = trim((string) ($row['cluster_name'] ?? ''));
            $homepass = (int) $this->normalizeNumber($row['homepass'] ?? 0);

            if ($idTarget <= 0 || $clusterName === '') {
                continue;
            }

            $clusterId = $this->MMonitoring_RFS_MyRep->createCluster([
                'id_target' => $idTarget,
                'cluster_name' => $clusterName,
                'status_rfs' => 'NY RFS',
                'homepass' => $homepass,
                'created_by' => (int) $this->session->userdata('id_user')
            ]);

            if ($clusterId) {
                $inserted++;
            }
        }

        if ($inserted <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menyimpan hasil import cluster'
            ]);
            return;
        }

        echo json_encode([
            'status' => true,
            'message' => $inserted . ' cluster berhasil diimport'
        ]);
    }

    public function downloadClusterImportTemplate()
    {
        $filename = 'format_import_cluster_myrep_' . date('Ymd_His') . '.csv';
        $headers = ['city_name', 'cluster_name', 'homepass'];
        $exampleRow = ['MALANG', 'Cluster A', '1000'];

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $headers);
        fputcsv($output, $exampleRow);
        fclose($output);
        exit;
    }

    public function saveClusterPlan()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $year = (int) $this->input->post('year');
        $month = (int) $this->input->post('month');
        $clusterId = (int) $this->input->post('cluster_id');
        $filterCity = strtoupper(trim((string) $this->input->post('filter_city')));
        $filterStartMonth = (int) $this->input->post('filter_start_month');
        $filterEndMonth = (int) $this->input->post('filter_end_month');

        if ($clusterId <= 0 || $year <= 0 || $month < 1 || $month > 12) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Data target realistis cluster tidak valid.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity));
            return;
        }

        $this->MMonitoring_RFS_MyRep->upsertClusterPlan([
            'cluster_id' => $clusterId,
            'year_num' => $year,
            'month_num' => $month,
            'optimistic_target' => $this->normalizeNumber($this->input->post('optimistic_target')),
            'updated_by' => (int) $this->session->userdata('id_user')
        ]);

        $this->session->set_flashdata('monitoring_rfs_myrep_message', 'Target realistis cluster berhasil disimpan.');
        redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity));
    }

    public function submitClaim()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $year = (int) $this->input->post('year');
        $month = (int) $this->input->post('month');
        $clusterId = (int) $this->input->post('cluster_id');
        $claimQty = (int) $this->normalizeNumber($this->input->post('claim_qty'));
        $claimDate = trim((string) $this->input->post('claim_date'));
        $statusRfs = strtoupper(trim((string) $this->input->post('status_rfs')));
        $filterCity = strtoupper(trim((string) $this->input->post('filter_city')));
        $filterStartMonth = (int) $this->input->post('filter_start_month');
        $filterEndMonth = (int) $this->input->post('filter_end_month');
        $filterClaimStartDate = $this->normalizeDateString($this->input->post('filter_claim_start_date'));
        $filterClaimEndDate = $this->normalizeDateString($this->input->post('filter_claim_end_date'));

        if ($clusterId <= 0 || $year <= 0 || $month < 1 || $month > 12 || $claimQty <= 0 || $claimDate === '') {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Data claim RFS belum lengkap.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        if (!in_array($statusRfs, ['PARTIAL', 'FULL RFS'], true)) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Status RFS claim wajib dipilih.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        if (!$this->MMonitoring_RFS_MyRep->claimSupportsStatusRfs()) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Database belum support status RFS claim. Jalankan update kolom `status_rfs` pada tabel `tb_rfs_myrep_claim` terlebih dahulu.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        if (!$this->MMonitoring_RFS_MyRep->claimSupportsRpmApproval()) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Database belum support approval RPM. Jalankan update kolom approval RPM pada tabel `tb_rfs_myrep_claim` terlebih dahulu.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        $claimDateTs = strtotime($claimDate);
        if ($claimDateTs === false) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Tanggal RFS tidak valid.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        $claimYear = (int) date('Y', $claimDateTs);
        $claimMonth = (int) date('n', $claimDateTs);

        if (empty($_FILES['claim_photo']['name'])) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Foto claim RFS wajib dilampirkan.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        $cluster = $this->MMonitoring_RFS_MyRep->getClusterById($clusterId);
        if (!$cluster) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Cluster tidak ditemukan.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        $clusterHomepass = (int) ($cluster['homepass_drm_effective'] ?? $cluster['homepass'] ?? 0);
        $claimedQty = $this->MMonitoring_RFS_MyRep->getClusterClaimedQty($clusterId);
        if (($claimedQty + $claimQty) > $clusterHomepass) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Total claim melebihi homepass cluster.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        $uploadResult = $this->uploadClaimPhoto();
        if (!$uploadResult['status']) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', $uploadResult['message']);
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        $payload = [
            'cluster_id' => $clusterId,
            'claim_year' => $claimYear,
            'claim_month' => $claimMonth,
            'claim_date' => date('Y-m-d', $claimDateTs),
            'claim_qty' => $claimQty,
            'status_rfs' => $statusRfs,
            'photo_path' => $uploadResult['file_path'],
            'claim_note' => trim((string) $this->input->post('claim_note')),
            'status_claim' => $this->hasRpmApprover($cluster) ? 'WAITING APPROVAL RPM' : 'WAITING APPROVAL HO',
            'rpm_approval_status' => $this->hasRpmApprover($cluster) ? 'WAITING APPROVAL RPM' : 'SKIPPED',
            'submitted_by' => (int) $this->session->userdata('id_user')
        ];

        $this->MMonitoring_RFS_MyRep->createClaim($payload);

        $this->session->set_flashdata(
            'monitoring_rfs_myrep_message',
            $this->hasRpmApprover($cluster)
                ? 'Claim RFS berhasil dikirim dan menunggu approval RPM.'
                : 'Claim RFS berhasil dikirim dan menunggu approval HO.'
        );

        redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
    }

    public function updateClaimStatus()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $year = (int) $this->input->post('year');
        $month = (int) $this->input->post('month');
        $claimId = (int) $this->input->post('claim_id');
        $status = strtoupper(trim((string) $this->input->post('status_claim')));
        $approverStage = strtoupper(trim((string) $this->input->post('approver_stage')));
        $filterCity = strtoupper(trim((string) $this->input->post('filter_city')));
        $filterStartMonth = (int) $this->input->post('filter_start_month');
        $filterEndMonth = (int) $this->input->post('filter_end_month');
        $filterClaimStartDate = $this->normalizeDateString($this->input->post('filter_claim_start_date'));
        $filterClaimEndDate = $this->normalizeDateString($this->input->post('filter_claim_end_date'));

        if (!in_array($status, ['APPROVED', 'REJECTED'], true) || $claimId <= 0) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Status approval tidak valid.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        $claim = $this->MMonitoring_RFS_MyRep->getClaimById($claimId);
        if (empty($claim)) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Data claim tidak ditemukan.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        if ($approverStage === 'RPM') {
            if (!$this->isRpmApprover($claim['rpm'] ?? '')) {
                $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Approval RPM hanya bisa dilakukan oleh RPM terkait.');
                redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
                return;
            }

            if (($claim['status_claim'] ?? '') !== 'WAITING APPROVAL RPM') {
                $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Claim ini tidak sedang menunggu approval RPM.');
                redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
                return;
            }

            $nextStatus = $status === 'APPROVED' ? 'WAITING APPROVAL HO' : 'REJECTED';
            $this->MMonitoring_RFS_MyRep->updateClaimRpmApproval($claimId, [
                'status_claim' => $nextStatus,
                'rpm_approval_status' => $status,
                'rpm_approval_note' => trim((string) $this->input->post('rpm_approval_note')),
                'rpm_approved_by' => (int) $this->session->userdata('id_user')
            ]);

            if ($status === 'REJECTED') {
                $this->MMonitoring_RFS_MyRep->updateClusterStatusRfs((int) $claim['cluster_id'], 'REJECTED');
            }

            $this->session->set_flashdata(
                'monitoring_rfs_myrep_message',
                $status === 'APPROVED'
                    ? 'Approval RPM berhasil. Claim diteruskan ke HO.'
                    : 'Claim berhasil direject oleh RPM.'
            );
            if ($status === 'APPROVED') {
                $this->sendClaimRfsNotification($claim);
            }
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        if (!$this->isHoApprover()) {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Approval claim hanya bisa dilakukan PIC HO.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        $rpmApprovalStatus = strtoupper(trim((string) ($claim['rpm_approval_status'] ?? '')));
        if ($rpmApprovalStatus === 'REJECTED') {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Claim sudah direject RPM, approval HO tidak dapat dilakukan.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        if (($claim['status_claim'] ?? '') !== 'WAITING APPROVAL HO') {
            $this->session->set_flashdata('monitoring_rfs_myrep_error', 'Claim ini belum siap untuk approval HO.');
            redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
            return;
        }

        $this->MMonitoring_RFS_MyRep->updateClaimStatus($claimId, [
            'status_claim' => $status,
            'approval_note' => trim((string) $this->input->post('approval_note')),
            'approved_by' => (int) $this->session->userdata('id_user')
        ]);

        $claim = $this->MMonitoring_RFS_MyRep->getClaimById($claimId);
        if ($status === 'APPROVED' && !empty($claim['status_rfs'])) {
            $this->MMonitoring_RFS_MyRep->updateClusterStatusRfs(
                (int) $claim['cluster_id'],
                strtoupper((string) $claim['status_rfs'])
            );
        }

        if ($status === 'REJECTED') {
            $this->MMonitoring_RFS_MyRep->updateClusterStatusRfs(
                (int) $claim['cluster_id'],
                'REJECTED'
            );
        }

        if ($status === 'APPROVED') {
            $cluster = $this->MMonitoring_RFS_MyRep->getClusterById((int) $claim['cluster_id']);
            if (!empty($cluster) && strtoupper((string) ($claim['status_rfs'] ?? '')) === 'FULL RFS') {
                $this->MMonitoring_RFS_MyRep->ensureChecklistPackagesForCluster(
                    (int) $claim['cluster_id'],
                    !empty($claim['claim_date']) ? $claim['claim_date'] : null,
                    (int) $this->session->userdata('id_user')
                );
            }
        }

        $this->session->set_flashdata('monitoring_rfs_myrep_message', 'Status claim berhasil diperbarui.');
        redirect($this->buildRedirectUrl($year, $filterStartMonth, $filterEndMonth, $filterCity, $filterClaimStartDate, $filterClaimEndDate));
    }

    private function uploadClaimPhoto()
    {
        $targetDir = FCPATH . 'uploads/monitoring_rfs_myrep/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        $config = [
            'upload_path' => $targetDir,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size' => 4096,
            'encrypt_name' => true
        ];

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('claim_photo')) {
            return [
                'status' => false,
                'message' => strip_tags($this->upload->display_errors('', ''))
            ];
        }

        $fileData = $this->upload->data();

        return [
            'status' => true,
            'file_path' => 'uploads/monitoring_rfs_myrep/' . $fileData['file_name']
        ];
    }

    private function sendClaimRfsNotification(array $claim)
    {
        $cluster = $this->MMonitoring_RFS_MyRep->getClusterById((int) ($claim['cluster_id'] ?? 0));
        if (empty($cluster)) {
            return;
        }

        $this->myrepNotifier->notify('Monitoring_RFS_MyRep', 'claim_rfs_approved', [
            'module_label' => 'RFS',
            'document_label' => 'RFS',
            'regional_name' => (string) ($cluster['regional_name'] ?? ''),
            'city_name' => (string) ($cluster['city_name'] ?? ''),
            'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
            'homepass' => (int) ($claim['claim_qty'] ?? 0),
            'sender_name' => (string) $this->session->userdata('nama_user'),
            'detail_url' => base_url('Monitoring_RFS_MyRep'),
        ]);
    }

    private function parseClusterExcelHeader($header)
    {
        $header = strtolower(trim((string) $header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        $header = trim($header, '_');

        $aliases = [
            'city_name' => ['city_name', 'city', 'kota', 'nama_kota'],
            'cluster_name' => ['cluster_name', 'nama_cluster', 'cluster'],
            'homepass' => ['homepass', 'jumlah_homepass', 'hp']
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

    private function normalizeNumber($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^\d,.\-]/', '', (string) $value);
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }

    private function isHoApprover()
    {
        return $this->session->userdata('lokasi_user') === 'HO' || $this->session->userdata('nama_level') === 'Super Admin';
    }

    private function hasRpmApprover($cluster)
    {
        return trim((string) ($cluster['rpm'] ?? '')) !== '';
    }

    private function isRpmApprover($rpmName)
    {
        if ($this->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        return strtoupper(trim((string) $this->session->userdata('nama_user'))) === strtoupper(trim((string) $rpmName));
    }

    private function getMonthLabels()
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
            12 => 'Desember'
        ];
    }

    private function buildPeriodLabel($startMonth, $endMonth)
    {
        $monthLabels = $this->getMonthLabels();
        if ($startMonth === $endMonth) {
            return $monthLabels[$startMonth];
        }

        return $monthLabels[$startMonth] . ' - ' . $monthLabels[$endMonth];
    }

    private function normalizeDateString($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            return '';
        }

        return $value;
    }

    private function buildRedirectUrl($year, $startMonth, $endMonth, $city = '', $claimStartDate = '', $claimEndDate = '')
    {
        if ($startMonth < 1 || $startMonth > 12) {
            $startMonth = 1;
        }
        if ($endMonth < 1 || $endMonth > 12) {
            $endMonth = $startMonth;
        }
        if ($startMonth > $endMonth) {
            $tmp = $startMonth;
            $startMonth = $endMonth;
            $endMonth = $tmp;
        }

        $url = 'Monitoring_RFS_MyRep?year=' . (int) $year . '&start_month=' . (int) $startMonth . '&end_month=' . (int) $endMonth;

        if ($city !== '') {
            $url .= '&city=' . urlencode($city);
        }

        $claimStartDate = $this->normalizeDateString($claimStartDate);
        $claimEndDate = $this->normalizeDateString($claimEndDate);
        if ($claimStartDate !== '' && $claimEndDate === '') {
            $claimEndDate = $claimStartDate;
        }
        if ($claimEndDate !== '' && $claimStartDate === '') {
            $claimStartDate = $claimEndDate;
        }
        if ($claimStartDate !== '' && $claimEndDate !== '' && strtotime($claimStartDate) > strtotime($claimEndDate)) {
            $tempDate = $claimStartDate;
            $claimStartDate = $claimEndDate;
            $claimEndDate = $tempDate;
        }
        if ($claimStartDate !== '' && $claimEndDate !== '') {
            $url .= '&claim_start_date=' . urlencode($claimStartDate) . '&claim_end_date=' . urlencode($claimEndDate);
        }

        return $url;
    }
}
