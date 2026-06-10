<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MyRepublik_Project extends CI_Controller
{
    private $importAllowedStatuses = [
        'DRAFT',
        'BA OPEN',
        'BAK',
        'VALSAL',
        'WAITING HO',
        'WAITING MYREP',
        'WAITING FINANCE',
        'RELEASED',
        'DONE BATCH APPROVAL',
        'DRM',
        'RFS',
        'ATP',
        'CHECKLIST DOKUMENT',
        'DONE',
        'REJECTED',
        'HOLD',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('MMyRepublik_Project');
        $this->load->model('MMyRep_Cleanup');
        $this->load->model('MPO_MyRep');
        $this->load->model('MChecklist_Dokument_MyRep');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('MyRepublik_Project');
            $this->myrepAccess->enforceByMethod('MyRepublik_Project', (string) $this->router->fetch_method(), [
                'previewCutoffImport' => 'TAMBAH',
            ]);
        }
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedStatus = strtoupper(trim((string) $this->input->get('status')));
        $metricMode = strtoupper(trim((string) $this->input->get('metric')));
        if (!in_array($metricMode, ['HP', 'PO'], true)) {
            $metricMode = 'HP';
        }

        $data['title'] = 'Dashboard MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['metricMode'] = $metricMode;
        $data['isReady'] = $this->MMyRepublik_Project->tablesReady();
        $data['cityOptions'] = $this->MMyRepublik_Project->getCityOptions();
        $data['statusOptions'] = $this->MMyRepublik_Project->getStatusOptions();
        $data['clusterRows'] = [];
        $data['deleteClusterRows'] = ($data['isReady'] && (string) $this->session->userdata('nama_level') === 'Super Admin')
            ? $this->MMyRepublik_Project->getClusterRows('', '', $metricMode)
            : [];
        $data['clusterStageSummaryRows'] = [];
        $data['overview'] = $this->emptyOverview();
        $data['statusCards'] = [];
        $data['renderClusterRows'] = false;

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('MyRepublik_Project/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function dashboardData()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session login tidak ditemukan.', [
                'overview' => $this->emptyOverview(),
                'statusCards' => [],
                'clusterStageSummaryRows' => [],
            ]);
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->post('city')));
        $selectedStatus = strtoupper(trim((string) $this->input->post('status')));
        $metricMode = strtoupper(trim((string) $this->input->post('metric')));
        if (!in_array($metricMode, ['HP', 'PO'], true)) {
            $metricMode = 'HP';
        }

        try {
            $rows = $this->MMyRepublik_Project->tablesReady()
                ? $this->MMyRepublik_Project->getClusterRows($selectedCity, $selectedStatus, $metricMode)
                : [];

            $this->jsonResponse(true, 'Dashboard berhasil dimuat.', [
                'overview' => $this->MMyRepublik_Project->getOverview($rows),
                'statusCards' => $this->MMyRepublik_Project->getStatusCards($rows, $metricMode),
                'clusterStageSummaryRows' => $this->buildClusterStageSummaryRows($rows),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'MyRepublik dashboardData failed: ' . $e->getMessage());
            $this->jsonResponse(false, 'Dashboard gagal dimuat.', [
                'overview' => $this->emptyOverview(),
                'statusCards' => [],
                'clusterStageSummaryRows' => [],
            ]);
        }
    }

    public function clusterTableData()
    {
        if (empty($this->session->userdata('id_user')) || !$this->MMyRepublik_Project->tablesReady()) {
            $this->jsonDataTableResponse(0, 0, []);
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->post('city')));
        $selectedStatus = strtoupper(trim((string) $this->input->post('status')));
        $metricMode = strtoupper(trim((string) $this->input->post('metric')));
        if (!in_array($metricMode, ['HP', 'PO'], true)) {
            $metricMode = 'HP';
        }

        $searchPayload = $this->input->post('search');
        $searchValue = is_array($searchPayload) ? (string) ($searchPayload['value'] ?? '') : '';
        $orderPayload = $this->input->post('order');
        $order = [];
        if (is_array($orderPayload) && isset($orderPayload[0]) && is_array($orderPayload[0])) {
            $order = [
                'column' => $orderPayload[0]['column'] ?? null,
                'dir' => $orderPayload[0]['dir'] ?? 'asc',
            ];
        }

        $start = max(0, (int) $this->input->post('start'));
        $length = (int) $this->input->post('length');
        if ($length <= 0) {
            $length = 10;
        }

        try {
            $page = $this->MMyRepublik_Project->getClusterRowsPage(
                $selectedCity,
                $selectedStatus,
                $metricMode,
                $start,
                $length,
                $searchValue,
                $order
            );

            $isSuperAdmin = (string) $this->session->userdata('nama_level') === 'Super Admin';
            $data = [];
            $no = $start + 1;
            foreach (($page['rows'] ?? []) as $row) {
                $data[] = $this->buildClusterTableRow($row, $no++, $metricMode, $isSuperAdmin);
            }

            $this->jsonDataTableResponse((int) ($page['recordsTotal'] ?? 0), (int) ($page['recordsFiltered'] ?? 0), $data);
        } catch (\Throwable $e) {
            log_message('error', 'MyRepublik clusterTableData failed: ' . $e->getMessage());
            $this->jsonDataTableResponse(0, 0, []);
        }
    }

    private function jsonDataTableResponse($recordsTotal, $recordsFiltered, array $data)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'draw' => (int) $this->input->post('draw'),
                'recordsTotal' => (int) $recordsTotal,
                'recordsFiltered' => (int) $recordsFiltered,
                'data' => $data,
            ]));
    }

    private function buildClusterTableRow(array $row, $no, $metricMode, $isSuperAdmin)
    {
        $detailUrl = $this->clusterDetailUrl($row);
        $clusterName = (string) ($row['cluster_name'] ?? '-');
        $clusterHtml = '<strong>';
        if ($detailUrl !== '#') {
            $clusterHtml .= '<a href="' . $this->attr($detailUrl) . '" class="text-primary">' . $this->html($clusterName) . '</a>';
        } else {
            $clusterHtml .= $this->html($clusterName);
        }
        $clusterHtml .= '</strong><div class="small text-muted">' . $this->html($row['team_name'] ?? '-') . '</div>';

        $metricValue = (float) ($row['metric_value'] ?? 0);
        $metricHtml = $this->formatNumber($metricValue) . (strtoupper((string) $metricMode) === 'PO' ? '' : ' HP');

        $actionHtml = $detailUrl !== '#'
            ? '<a href="' . $this->attr($detailUrl) . '" class="btn btn-sm btn-primary">Detail</a>'
            : '<span class="text-muted">-</span>';

        if ($isSuperAdmin && (int) ($row['id_myrep_cluster'] ?? 0) > 0) {
            $actionHtml .= '<form method="post" action="' . base_url('MyRepublik_Project/deleteCluster') . '" class="d-inline" onsubmit="return confirm(\'Hapus cluster ini? Seluruh flow MyRep dari BAK sampai Checklist Dokument akan ikut terhapus.\');">'
                . '<input type="hidden" name="cluster_id" value="' . (int) $row['id_myrep_cluster'] . '">'
                . '<button type="submit" class="btn btn-sm btn-danger">Hapus Cluster</button>'
                . '</form>';
        }

        return [
            (int) $no,
            $clusterHtml,
            $this->html($row['city_name'] ?? '-'),
            $this->html($row['regional_name'] ?? '-'),
            '<span class="badge badge-info">' . $this->html($row['status_current_display'] ?? $row['status_current'] ?? '-') . '</span>',
            $metricHtml,
            (int) ($row['po_count'] ?? 0),
            $this->html($row['rpm'] ?? '-') . ' / ' . $this->html($row['spv'] ?? '-'),
            !empty($row['drm_date']) ? $this->html($row['drm_date']) : '-',
            $actionHtml,
        ];
    }

    private function clusterDetailUrl(array $row)
    {
        $myrepClusterId = (int) ($row['id_myrep_cluster'] ?? 0);
        if ($myrepClusterId > 0) {
            return base_url('MyRepublik_Project/detail/' . $myrepClusterId);
        }

        $legacyClusterId = (int) ($row['legacy_rfs_cluster_id'] ?? $row['rfs_cluster_id'] ?? 0);
        if ($legacyClusterId > 0) {
            return base_url('MyRepublik_Project/detailLegacy/' . $legacyClusterId);
        }

        return '#';
    }

    private function emptyOverview()
    {
        return [
            'total_cluster' => 0,
            'total_hp' => 0,
            'total_po' => 0,
            'total_released' => 0,
            'total_rfs' => 0,
            'total_atp' => 0,
        ];
    }

    private function formatNumber($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }

    private function html($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private function attr($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public function detail($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            redirect('MyRepublik_Project');
            return;
        }

        $cluster = $this->MMyRepublik_Project->getClusterDetail($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Detail cluster MyRep tidak ditemukan.');
            redirect('MyRepublik_Project');
            return;
        }

        $data = $this->buildDetailPayload($cluster, false);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('MyRepublik_Project/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function detailLegacy($rfsClusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $rfsClusterId = (int) $rfsClusterId;
        if ($rfsClusterId <= 0) {
            redirect('MyRepublik_Project');
            return;
        }

        $cluster = $this->MMyRepublik_Project->getLegacyClusterDetail($rfsClusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Detail cluster legacy RFS tidak ditemukan.');
            redirect('MyRepublik_Project');
            return;
        }

        $data = $this->buildDetailPayload($cluster, true);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('MyRepublik_Project/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function updateQuick($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Cluster MyRep tidak valid.');
            redirect('MyRepublik_Project');
            return;
        }

        $this->ensurePoTerminCertificateColumn();
        $cluster = $this->MMyRepublik_Project->getClusterDetail($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Detail cluster MyRep tidak ditemukan.');
            redirect('MyRepublik_Project');
            return;
        }

        $row = $this->collectQuickUpdateRow($cluster);
        $errors = $this->validateQuickUpdateRow($row);
        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode(' | ', $errors));
            redirect('MyRepublik_Project/detail/' . $clusterId);
            return;
        }

        $result = $this->applyQuickUpdateToCluster($cluster, $row, (int) $this->session->userdata('id_user'));
        $this->session->set_flashdata(
            !empty($result['status']) ? 'success' : 'error',
            (string) ($result['message'] ?? 'Update quick cluster selesai.')
        );
        redirect('MyRepublik_Project/detail/' . $clusterId);
    }

    public function deleteCluster()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if ((string) $this->session->userdata('nama_level') !== 'Super Admin') {
            $this->session->set_flashdata('error', 'Menu hapus cluster MyRep hanya untuk Super Admin.');
            redirect('MyRepublik_Project');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Cluster MyRep tidak valid.');
            redirect('MyRepublik_Project');
            return;
        }

        $deleted = $this->MMyRep_Cleanup->deleteWholeCluster($clusterId);
        $this->session->set_flashdata(
            $deleted ? 'success' : 'error',
            $deleted
                ? 'Cluster berhasil dihapus. Flow MyRep dari BAK sampai Checklist Dokument ikut terhapus.'
                : 'Gagal menghapus cluster MyRep.'
        );
        redirect('MyRepublik_Project');
    }

    public function deleteAllClusters()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if ((string) $this->session->userdata('nama_level') !== 'Super Admin') {
            $this->session->set_flashdata('error', 'Menu hapus all cluster MyRep hanya untuk Super Admin.');
            redirect('MyRepublik_Project');
            return;
        }

        $clusterIds = $this->input->post('cluster_ids');
        if (!is_array($clusterIds)) {
            $clusterIds = [];
        }

        $clusterIds = array_values(array_unique(array_filter(array_map('intval', $clusterIds), static function ($id) {
            return $id > 0;
        })));

        if (empty($clusterIds)) {
            $this->session->set_flashdata('error', 'Pilih minimal satu cluster MyRep yang akan dihapus.');
            redirect('MyRepublik_Project');
            return;
        }

        $deletedCount = 0;
        foreach ($clusterIds as $clusterId) {
            if ($this->MMyRep_Cleanup->deleteWholeCluster($clusterId)) {
                $deletedCount++;
            }
        }
        $this->session->set_flashdata(
            $deletedCount > 0 ? 'success' : 'error',
            $deletedCount > 0
                ? ('Berhasil menghapus ' . $deletedCount . ' dari ' . count($clusterIds) . ' cluster MyRep yang dipilih. Flow dari BAK sampai Checklist Dokument ikut terhapus.')
                : 'Tidak ada data cluster MyRep yang berhasil dihapus.'
        );
        redirect('MyRepublik_Project');
    }

    public function previewCutoffImport()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session login tidak ditemukan.');
            return;
        }

        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size'] = 10240;
        $config['encrypt_name'] = true;

        if (!is_dir($config['upload_path'])) {
            @mkdir($config['upload_path'], 0777, true);
        }

        $this->load->library('upload');
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('file_excel')) {
            $this->jsonResponse(false, strip_tags($this->upload->display_errors('', '')));
            return;
        }

        $fileData = $this->upload->data();
        $filePath = $fileData['full_path'];

        try {
            $extension = strtolower(pathinfo($fileData['file_name'], PATHINFO_EXTENSION));
            if ($extension === 'csv') {
                $sheetData = $this->readCsvSheetData($filePath);
            } else {
                $this->loadPHPExcel();
                $objPHPExcel = PHPExcel_IOFactory::load($filePath);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            }
        } catch (Exception $e) {
            @unlink($filePath);
            $this->jsonResponse(false, 'File import tidak bisa dibaca.');
            return;
        }
        @unlink($filePath);

        if (count($sheetData) < 2) {
            $this->jsonResponse(false, 'File import tidak memiliki data.');
            return;
        }

        $headerRow = reset($sheetData);
        $headers = [];
        foreach ($headerRow as $col => $headerText) {
            $headerText = trim((string) $headerText);
            if ($headerText === '') {
                continue;
            }
            $headers[$col] = $headerText;
        }

        $required = ['cluster_name', 'city_name'];
        $normalizedHeaderMap = [];
        foreach ($headers as $col => $headerText) {
            $normalizedHeaderMap[$col] = $this->normalizeHeaderName($headerText);
        }

        foreach ($required as $requiredName) {
            if (!in_array($requiredName, $normalizedHeaderMap, true)) {
                $this->jsonResponse(false, 'Header wajib memuat: ' . implode(', ', $required));
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

            $rowRaw = [];
            $rowNormalized = [];
            foreach ($headers as $col => $headerText) {
                $value = isset($excelRow[$col]) ? trim((string) $excelRow[$col]) : '';
                $rowRaw[$headerText] = $value;
                $rowNormalized[$this->normalizeHeaderName($headerText)] = $value;
            }

            $isBlank = true;
            foreach ($rowNormalized as $value) {
                if (trim((string) $value) !== '') {
                    $isBlank = false;
                    break;
                }
            }
            if ($isBlank) {
                continue;
            }

            $errors = $this->validateCutoffImportRow($rowNormalized);
            $previewRows[] = [
                'row_number' => $rowIndex,
                'status' => empty($errors) ? 'valid' : 'invalid',
                'message' => empty($errors) ? 'Siap diimport' : implode(', ', $errors),
                'raw' => $rowRaw,
            ];

            if (empty($errors)) {
                $validRows[] = $rowNormalized;
            } else {
                $errorRows[] = ['row_number' => $rowIndex, 'errors' => $errors];
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'message' => count($validRows) . ' data valid dari ' . count($previewRows) . ' baris',
                'headers' => array_values($headers),
                'rows' => $previewRows,
                'valid_rows' => $validRows,
                'error_rows' => $errorRows,
            ]));
    }

    public function saveCutoffImport()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->jsonResponse(false, 'Session login tidak ditemukan.');
            return;
        }

        $rows = json_decode((string) $this->input->post('rows_json'), true);
        if (empty($rows) || !is_array($rows)) {
            $this->jsonResponse(false, 'Tidak ada data valid untuk disimpan.');
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        $username = (string) $this->session->userdata('nama_user');
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errorDetails = [];

        foreach ($rows as $index => $row) {
            try {
                $errors = $this->validateCutoffImportRow($row);
                if (!empty($errors)) {
                    $skipped++;
                    $errorDetails[] = [
                        'row_number' => $index + 1,
                        'cluster_name' => (string) ($row['cluster_name'] ?? ''),
                        'message' => implode(', ', $errors),
                    ];
                    continue;
                }

                $saveResult = $this->saveOneImportedCluster($row, $userId);
                if (!empty($saveResult['inserted'])) {
                    $inserted++;
                } elseif (!empty($saveResult['updated'])) {
                    $updated++;
                } else {
                    $skipped++;
                    $errorDetails[] = [
                        'row_number' => $index + 1,
                        'cluster_name' => (string) ($row['cluster_name'] ?? ''),
                        'message' => (string) ($saveResult['message'] ?? 'Dilewati (kemungkinan data sudah ada / tidak valid untuk insert).'),
                    ];
                }
            } catch (Throwable $e) {
                $skipped++;
                log_message('error', 'Cutoff import row failed: row=' . ($index + 1) . ' cluster=' . (string) ($row['cluster_name'] ?? '-') . ' error=' . $e->getMessage());
                $errorDetails[] = [
                    'row_number' => $index + 1,
                    'cluster_name' => (string) ($row['cluster_name'] ?? ''),
                    'message' => $e->getMessage(),
                ];
            }
        }

        if ($inserted <= 0 && $updated <= 0) {
            $this->logCutoffImportSummary($userId, $username, count($rows), $inserted, $skipped, $errorDetails, $updated);
            $this->jsonResponse(false, 'Tidak ada data yang berhasil disimpan atau diupdate.', [
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped,
                'error_rows' => $errorDetails,
            ]);
            return;
        }

        $this->logCutoffImportSummary($userId, $username, count($rows), $inserted, $skipped, $errorDetails, $updated);
        $this->jsonResponse(true, $inserted . ' cluster berhasil diimport. ' . $updated . ' cluster existing diupdate. ' . $skipped . ' baris dilewati.', [
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
            'error_rows' => $errorDetails,
        ]);
    }

    private function logCutoffImportSummary($userId, $username, $totalRows, $inserted, $skipped, array $errorDetails, $updated = 0)
    {
        $summary = [
            'user_id' => (int) $userId,
            'user_name' => $username,
            'total_rows' => (int) $totalRows,
            'inserted' => (int) $inserted,
            'updated' => (int) $updated,
            'skipped' => (int) $skipped,
            'error_count' => count($errorDetails),
        ];
        log_message('error', 'CUTOFF_IMPORT_SUMMARY ' . json_encode($summary));

        if (!empty($errorDetails)) {
            $topErrors = array_slice($errorDetails, 0, 50);
            log_message('error', 'CUTOFF_IMPORT_ERROR_ROWS ' . json_encode($topErrors));
        }
    }

    public function downloadCutoffImportTemplate()
    {
        $filename = 'template_import_myrep_project_full_' . date('Ymd_His') . '.csv';
        $headers = [
            'status_current',
            'city_name',
            'district_name',
            'village_name',
            'cluster_name',
            'cluster_code',
            'hp_plan',
            'homepass_bak',
            'ba_open_date',
            'bak_date',
            'nomor_ntp',
            'tanggal_ntp',
            'homepass_valsal',
            'valsal_date',
            'remark_valsal',
            'hp_donasi',
            'submission_date',
            'nominal_pengajuan_area',
            'nominal_nego_emr',
            'nominal_release_finance',
            'nominal_per_homepass',
            'bank_name',
            'bank_account_number',
            'recipient_name',
            'recipient_phone',
            'recipient_position',
            'recipient_period',
            'free_wifi_qty',
            'free_wifi_period_month',
            'astri_batch_number',
            'staging_status',
            'released_at',
            'remark_batch_approval',
            'homepass_drm',
            'drm_date',
            'nama_olt',
            'remark_drm',
            'rfs_date',
            'status_rfs',
            'email_atp_date',
            'actual_atp_date',
            'status_atp',
            'cluster_cwatp',
            'cluster_fullopm',
            'cluster_rfs',
            'cluster_rfs_nro_flow',
            'subfeeder_cwatp',
            'subfeeder_fullopm',
            'subfeeder_rfs',
            'po_cluster_category',
            'po_cluster_status',
            'po_cluster_on_target',
            'po_cluster_number',
            'po_cluster_date',
            'po_cluster_value',
            'po_cluster_version_label',
            'po_cluster_remark',
            'po_subfeeder_category',
            'po_subfeeder_status',
            'po_subfeeder_on_target',
            'po_subfeeder_number',
            'po_subfeeder_date',
            'po_subfeeder_value',
            'po_subfeeder_version_label',
            'po_subfeeder_remark',
            'remark_general',
        ];
        $headers = array_merge($headers, $this->buildRfsClaimImportHeaders());
        $headers = array_merge($headers, $this->buildPoTerminImportHeaders());

        $exampleRowMaps = [
            ['status_current' => 'BAK', 'city_name' => 'MALANG', 'district_name' => 'KLOJEN', 'village_name' => 'KAUMAN', 'cluster_name' => 'Cluster A', 'cluster_code' => 'CL-A', 'hp_plan' => '120', 'homepass_bak' => '120', 'ba_open_date' => '2026-05-01', 'bak_date' => '2026-05-03', 'nomor_ntp' => 'NTP-MLG-001', 'tanggal_ntp' => '2026-05-04', 'remark_general' => 'Contoh cluster BAK 1'],
            ['status_current' => 'BAK', 'city_name' => 'MALANG', 'district_name' => 'BLIMBING', 'village_name' => 'POLOWIJEN', 'cluster_name' => 'Cluster B', 'cluster_code' => 'CL-B', 'hp_plan' => '95', 'homepass_bak' => '95', 'ba_open_date' => '2026-05-02', 'bak_date' => '2026-05-04', 'remark_general' => 'Contoh cluster BAK 2'],
            ['status_current' => 'BAK', 'city_name' => 'MALANG', 'district_name' => 'LOWOKWARU', 'village_name' => 'MOJOLANGU', 'cluster_name' => 'Cluster C', 'cluster_code' => 'CL-C', 'hp_plan' => '140', 'homepass_bak' => '140', 'ba_open_date' => '2026-05-03', 'bak_date' => '2026-05-05', 'remark_general' => 'Contoh cluster BAK 3'],
            ['status_current' => 'VALSAL', 'city_name' => 'MALANG', 'district_name' => 'SUKUN', 'village_name' => 'BANDULAN', 'cluster_name' => 'Cluster D', 'cluster_code' => 'CL-D', 'hp_plan' => '110', 'homepass_bak' => '110', 'ba_open_date' => '2026-05-04', 'bak_date' => '2026-05-06', 'homepass_valsal' => '108', 'valsal_date' => '2026-05-08', 'remark_valsal' => 'Contoh remark VALSAL 1', 'remark_general' => 'Contoh cluster VALSAL 1'],
            ['status_current' => 'WAITING HO', 'city_name' => 'MALANG', 'district_name' => 'KLOJEN', 'village_name' => 'BARAN', 'cluster_name' => 'Cluster G', 'cluster_code' => 'CL-G', 'hp_plan' => '115', 'homepass_bak' => '115', 'ba_open_date' => '2026-05-07', 'bak_date' => '2026-05-09', 'homepass_valsal' => '112', 'valsal_date' => '2026-05-11', 'remark_valsal' => 'Contoh remark VALSAL 4', 'hp_donasi' => '112', 'submission_date' => '2026-05-13', 'nominal_pengajuan_area' => '56000000', 'nominal_nego_emr' => '0', 'nominal_release_finance' => '0', 'bank_name' => 'BCA', 'bank_account_number' => '1234567890', 'recipient_name' => 'Budi Santoso', 'recipient_phone' => '081234567890', 'recipient_position' => 'Ketua RT', 'recipient_period' => '2025-2028', 'free_wifi_qty' => '10', 'free_wifi_period_month' => '12', 'astri_batch_number' => 'BATCH-001', 'staging_status' => 'WAITING HO', 'remark_batch_approval' => 'Contoh remark batch 1', 'remark_general' => 'Contoh cluster BATCH 1'],
            ['status_current' => 'WAITING MYREP', 'city_name' => 'MALANG', 'district_name' => 'BLIMBING', 'village_name' => 'JODIPAN', 'cluster_name' => 'Cluster H', 'cluster_code' => 'CL-H', 'hp_plan' => '125', 'homepass_bak' => '125', 'ba_open_date' => '2026-05-08', 'bak_date' => '2026-05-10', 'homepass_valsal' => '121', 'valsal_date' => '2026-05-12', 'remark_valsal' => 'Contoh remark VALSAL 5', 'hp_donasi' => '121', 'submission_date' => '2026-05-14', 'nominal_pengajuan_area' => '66550000', 'nominal_release_finance' => '0', 'bank_name' => 'MANDIRI', 'bank_account_number' => '9876543210', 'recipient_name' => 'Rina Permata', 'recipient_phone' => '081298765432', 'recipient_position' => 'Sekretaris RW', 'recipient_period' => '2024-2027', 'astri_batch_number' => 'BATCH-002', 'staging_status' => 'WAITING MYREP', 'remark_batch_approval' => 'Contoh remark batch 2', 'remark_general' => 'Contoh cluster BATCH 2'],
            ['status_current' => 'WAITING FINANCE', 'city_name' => 'MALANG', 'district_name' => 'KEDUNGKANDANG', 'village_name' => 'MADYOPURO', 'cluster_name' => 'Cluster J', 'cluster_code' => 'CL-J', 'hp_plan' => '145', 'homepass_bak' => '145', 'ba_open_date' => '2026-05-10', 'bak_date' => '2026-05-12', 'homepass_valsal' => '140', 'valsal_date' => '2026-05-14', 'remark_valsal' => 'Contoh remark VALSAL 7', 'hp_donasi' => '140', 'submission_date' => '2026-05-16', 'nominal_pengajuan_area' => '77000000', 'nominal_nego_emr' => '75500000', 'bank_name' => 'BNI', 'bank_account_number' => '8899001122', 'recipient_name' => 'Dewi Anggraini', 'recipient_phone' => '081355577799', 'recipient_position' => 'Ketua RW', 'recipient_period' => '2025-2029', 'free_wifi_qty' => '8', 'free_wifi_period_month' => '12', 'astri_batch_number' => 'BATCH-004', 'staging_status' => 'WAITING FINANCE', 'remark_batch_approval' => 'Contoh remark batch waiting finance', 'remark_general' => 'Contoh cluster BATCH 3'],
            ['status_current' => 'DRM', 'city_name' => 'MALANG', 'district_name' => 'LOWOKWARU', 'village_name' => 'MERJOSARI', 'cluster_name' => 'Cluster K', 'cluster_code' => 'CL-K', 'hp_plan' => '135', 'homepass_bak' => '135', 'ba_open_date' => '2026-05-09', 'bak_date' => '2026-05-11', 'homepass_valsal' => '130', 'valsal_date' => '2026-05-13', 'remark_valsal' => 'Contoh remark VALSAL 6', 'hp_donasi' => '130', 'submission_date' => '2026-05-14', 'nominal_pengajuan_area' => '71500000', 'nominal_nego_emr' => '70000000', 'nominal_release_finance' => '69000000', 'bank_name' => 'BRI', 'bank_account_number' => '55500112233', 'recipient_name' => 'Agus Pratama', 'recipient_phone' => '081277766655', 'recipient_position' => 'Ketua Paguyuban', 'recipient_period' => '2023-2026', 'free_wifi_qty' => '5', 'free_wifi_period_month' => '6', 'astri_batch_number' => 'BATCH-005', 'staging_status' => 'RELEASED', 'released_at' => '2026-05-18 10:00:00', 'remark_batch_approval' => 'Contoh remark batch DRM 1', 'homepass_drm' => '130', 'drm_date' => '2026-05-15', 'nama_olt' => 'OLT-MLG-01', 'remark_drm' => 'Contoh remark DRM 1', 'remark_general' => 'Contoh cluster DRM 1'],
            ['status_current' => 'DRM', 'city_name' => 'MALANG', 'district_name' => 'BLIMBING', 'village_name' => 'ARJOSARI', 'cluster_name' => 'Cluster L', 'cluster_code' => 'CL-L', 'hp_plan' => '118', 'homepass_bak' => '118', 'ba_open_date' => '2026-05-11', 'bak_date' => '2026-05-13', 'homepass_valsal' => '116', 'valsal_date' => '2026-05-15', 'remark_valsal' => 'Contoh remark VALSAL 8', 'hp_donasi' => '116', 'submission_date' => '2026-05-16', 'nominal_pengajuan_area' => '63800000', 'nominal_nego_emr' => '63000000', 'nominal_release_finance' => '62500000', 'bank_name' => 'MANDIRI', 'bank_account_number' => '9988776655', 'recipient_name' => 'Rina Lestari', 'recipient_phone' => '081266611122', 'recipient_position' => 'Bendahara RW', 'recipient_period' => '2024-2027', 'astri_batch_number' => 'BATCH-006', 'staging_status' => 'RELEASED', 'released_at' => '2026-05-19 09:45:00', 'remark_batch_approval' => 'Contoh remark batch DRM 2', 'homepass_drm' => '116', 'drm_date' => '2026-05-17', 'nama_olt' => 'OLT-MLG-02', 'remark_drm' => 'Contoh remark DRM 2', 'remark_general' => 'Contoh cluster DRM 2'],
        ];
        $remappedRows = [];
        foreach ($exampleRowMaps as $map) {
            $ordered = [];
            foreach ($headers as $header) {
                $ordered[] = isset($map[$header]) ? (string) $map[$header] : '';
            }
            $remappedRows[] = $ordered;
        }
        $poExampleMaps = [
            [
                'city_name' => 'MALANG', 'district_name' => 'KLOJEN', 'village_name' => 'ORO ORO DOWO', 'cluster_name' => 'Cluster N', 'cluster_code' => 'CL-N',
                'hp_plan' => '120', 'homepass_bak' => '120', 'ba_open_date' => '2026-05-13', 'bak_date' => '2026-05-15',
                'homepass_valsal' => '118', 'valsal_date' => '2026-05-17', 'remark_valsal' => 'Contoh remark VALSAL 10',
                'hp_donasi' => '118', 'submission_date' => '2026-05-19', 'nominal_pengajuan_area' => '73000000', 'nominal_nego_emr' => '72000000', 'nominal_release_finance' => '71500000',
                'bank_name' => 'BCA', 'bank_account_number' => '100200300', 'recipient_name' => 'Budi Hartono', 'recipient_phone' => '081300011122', 'recipient_position' => 'Ketua RT', 'recipient_period' => '2025-2028',
                'free_wifi_qty' => '6', 'free_wifi_period_month' => '12', 'astri_batch_number' => 'BATCH-011', 'staging_status' => 'RELEASED', 'released_at' => '2026-05-21 09:00:00', 'remark_batch_approval' => 'Contoh remark batch PO 1',
                'homepass_drm' => '118', 'drm_date' => '2026-05-20', 'nama_olt' => 'OLT-MLG-04', 'remark_drm' => 'Contoh remark DRM 4',
                'po_cluster_category' => 'INITIAL', 'po_cluster_status' => 'ISSUED', 'po_cluster_number' => 'PO-MLG-0001', 'po_cluster_date' => '2026-05-22', 'po_cluster_value' => '73000000', 'po_cluster_version_label' => 'FINAL 01', 'po_cluster_remark' => 'Contoh remark PO Cluster 1',
                'po_subfeeder_category' => 'AMANDMENT', 'po_subfeeder_status' => 'ISSUED', 'po_subfeeder_number' => 'PO-SF-MLG-0001', 'po_subfeeder_date' => '2026-05-23', 'po_subfeeder_value' => '8500000', 'po_subfeeder_version_label' => 'AMANDMENT 01', 'po_subfeeder_remark' => 'Contoh remark PO Subfeeder 1',
                'po_cluster_termin1_plan_invoice' => '0', 'po_cluster_termin1_submit_invoice' => '2026-05-24', 'po_cluster_termin1_nilai_invoice' => '14600000',
                'po_cluster_termin2_plan_invoice' => '18250000', 'po_cluster_termin2_submit_invoice' => '', 'po_cluster_termin2_sertifikat_invoice' => '2026-06-01', 'po_cluster_termin2_nilai_invoice' => '0',
                'po_cluster_termin3_plan_invoice' => '0', 'po_cluster_termin3_submit_invoice' => '2026-06-24', 'po_cluster_termin3_sertifikat_invoice' => '2026-06-20', 'po_cluster_termin3_nilai_invoice' => '-1095000',
                'po_cluster_termin4_plan_invoice' => '21900000', 'po_cluster_termin4_submit_invoice' => '', 'po_cluster_termin4_sertifikat_invoice' => '', 'po_cluster_termin4_nilai_invoice' => '0',
                'po_cluster_termin5_plan_invoice' => '7300000', 'po_cluster_termin5_submit_invoice' => '', 'po_cluster_termin5_sertifikat_invoice' => '', 'po_cluster_termin5_nilai_invoice' => '0',
                'po_subfeeder_termin1_plan_invoice' => '0', 'po_subfeeder_termin1_submit_invoice' => '2026-05-25', 'po_subfeeder_termin1_nilai_invoice' => '1700000',
                'po_subfeeder_termin2_plan_invoice' => '2125000', 'po_subfeeder_termin2_submit_invoice' => '', 'po_subfeeder_termin2_sertifikat_invoice' => '2026-06-02', 'po_subfeeder_termin2_nilai_invoice' => '0',
                'po_subfeeder_termin3_plan_invoice' => '1275000', 'po_subfeeder_termin3_submit_invoice' => '', 'po_subfeeder_termin3_sertifikat_invoice' => '', 'po_subfeeder_termin3_nilai_invoice' => '0',
                'po_subfeeder_termin4_plan_invoice' => '0', 'po_subfeeder_termin4_submit_invoice' => '2026-06-30', 'po_subfeeder_termin4_sertifikat_invoice' => '2026-06-28', 'po_subfeeder_termin4_nilai_invoice' => '2550000',
                'po_subfeeder_termin5_plan_invoice' => '850000', 'po_subfeeder_termin5_submit_invoice' => '', 'po_subfeeder_termin5_sertifikat_invoice' => '', 'po_subfeeder_termin5_nilai_invoice' => '0',
                'remark_general' => 'Contoh cluster PO 1'
            ],
            [
                'city_name' => 'MALANG', 'district_name' => 'BLIMBING', 'village_name' => 'PURWANTORO', 'cluster_name' => 'Cluster O', 'cluster_code' => 'CL-O',
                'hp_plan' => '132', 'homepass_bak' => '132', 'ba_open_date' => '2026-05-14', 'bak_date' => '2026-05-16',
                'homepass_valsal' => '128', 'valsal_date' => '2026-05-18', 'remark_valsal' => 'Contoh remark VALSAL 11',
                'hp_donasi' => '128', 'submission_date' => '2026-05-20', 'nominal_pengajuan_area' => '81200000', 'nominal_nego_emr' => '80000000', 'nominal_release_finance' => '79800000',
                'bank_name' => 'MANDIRI', 'bank_account_number' => '200300400', 'recipient_name' => 'Rina Oktavia', 'recipient_phone' => '081311122233', 'recipient_position' => 'Ketua RW', 'recipient_period' => '2024-2027',
                'free_wifi_qty' => '0', 'free_wifi_period_month' => '0', 'astri_batch_number' => 'BATCH-012', 'staging_status' => 'RELEASED', 'released_at' => '2026-05-22 10:30:00', 'remark_batch_approval' => 'Contoh remark batch PO 2',
                'homepass_drm' => '128', 'drm_date' => '2026-05-21', 'nama_olt' => 'OLT-MLG-05', 'remark_drm' => 'Contoh remark DRM 5',
                'po_cluster_category' => 'FINAL', 'po_cluster_status' => 'ISSUED', 'po_cluster_number' => 'PO-MLG-0002', 'po_cluster_date' => '2026-05-23', 'po_cluster_value' => '81200000', 'po_cluster_version_label' => 'FINAL 02', 'po_cluster_remark' => 'Contoh remark PO Cluster 2',
                'remark_general' => 'Contoh cluster PO 2'
            ],
            [
                'city_name' => 'MALANG', 'district_name' => 'LOWOKWARU', 'village_name' => 'DINOYO', 'cluster_name' => 'Cluster P', 'cluster_code' => 'CL-P',
                'hp_plan' => '148', 'homepass_bak' => '148', 'ba_open_date' => '2026-05-15', 'bak_date' => '2026-05-17',
                'homepass_valsal' => '142', 'valsal_date' => '2026-05-19', 'remark_valsal' => 'Contoh remark VALSAL 12',
                'hp_donasi' => '142', 'submission_date' => '2026-05-21', 'nominal_pengajuan_area' => '90500000', 'nominal_nego_emr' => '89000000', 'nominal_release_finance' => '88500000',
                'bank_name' => 'BRI', 'bank_account_number' => '300400500', 'recipient_name' => 'Agus Setiawan', 'recipient_phone' => '081322233344', 'recipient_position' => 'Ketua Paguyuban', 'recipient_period' => '2023-2026',
                'free_wifi_qty' => '4', 'free_wifi_period_month' => '6', 'astri_batch_number' => 'BATCH-013', 'staging_status' => 'RELEASED', 'released_at' => '2026-05-23 14:00:00', 'remark_batch_approval' => 'Contoh remark batch PO 3',
                'homepass_drm' => '142', 'drm_date' => '2026-05-22', 'nama_olt' => 'OLT-MLG-06', 'remark_drm' => 'Contoh remark DRM 6',
                'po_subfeeder_category' => 'AMANDMENT', 'po_subfeeder_status' => 'ISSUED', 'po_subfeeder_number' => 'PO-SF-MLG-0003', 'po_subfeeder_date' => '2026-05-24', 'po_subfeeder_value' => '90500000', 'po_subfeeder_version_label' => 'AMANDMENT 01', 'po_subfeeder_remark' => 'Contoh remark PO Subfeeder 3',
                'remark_general' => 'Contoh cluster PO 3'
            ],
            [
                'city_name' => 'MALANG', 'district_name' => 'KLOJEN', 'village_name' => 'SUKOHARJO', 'cluster_name' => 'Cluster RFS 1', 'cluster_code' => 'CL-RFS-1',
                'hp_plan' => '120', 'homepass_bak' => '120', 'ba_open_date' => '2026-05-05', 'bak_date' => '2026-05-07',
                'homepass_valsal' => '118', 'valsal_date' => '2026-05-09',
                'hp_donasi' => '118', 'submission_date' => '2026-05-11', 'nominal_pengajuan_area' => '73000000', 'nominal_nego_emr' => '72000000', 'nominal_release_finance' => '71500000',
                'bank_name' => 'BCA', 'bank_account_number' => '111222333', 'recipient_name' => 'Ketua RW Sukoharjo', 'recipient_phone' => '081212120001', 'recipient_position' => 'Ketua RW', 'recipient_period' => '2025-2028',
                'free_wifi_qty' => '5', 'free_wifi_period_month' => '12', 'astri_batch_number' => 'BATCH-RFS-001',
                'staging_status' => 'RELEASED', 'released_at' => '2026-05-15 08:30:00', 'remark_batch_approval' => 'Batch lengkap untuk proses RFS 1',
                'homepass_drm' => '118', 'drm_date' => '2026-05-16', 'nama_olt' => 'OLT-MLG-RFS1',
                'rfs_date' => '2026-05-20', 'status_rfs' => 'FULL RFS', 'email_atp_date' => '2026-05-26', 'status_atp' => '', 'status_current' => 'RFS',
                'rfs_1_date' => '2026-04-20', 'rfs_1_qty' => '200',
                'rfs_2_date' => '2026-04-22', 'rfs_2_qty' => '200',
                'rfs_3_date' => '2026-04-24', 'rfs_3_qty' => '200',
                'rfs_4_date' => '2026-04-26', 'rfs_4_qty' => '200',
                'rfs_5_date' => '2026-04-28', 'rfs_5_qty' => '200',
                'po_cluster_category' => 'INITIAL', 'po_cluster_status' => 'ISSUED', 'po_cluster_number' => 'PO-MLG-RFS1', 'po_cluster_date' => '2026-05-14', 'po_cluster_value' => '73000000',
                'po_cluster_termin1_plan_invoice' => '0', 'po_cluster_termin1_submit_invoice' => '2026-05-17', 'po_cluster_termin1_nilai_invoice' => '14600000',
                'po_cluster_termin2_plan_invoice' => '18250000', 'po_cluster_termin2_submit_invoice' => '', 'po_cluster_termin2_sertifikat_invoice' => '2026-05-28', 'po_cluster_termin2_nilai_invoice' => '0',
                'remark_general' => 'Contoh cluster RFS 1'
            ],
            [
                'city_name' => 'MALANG', 'district_name' => 'BLIMBING', 'village_name' => 'PURWODADI', 'cluster_name' => 'Cluster RFS 2', 'cluster_code' => 'CL-RFS-2',
                'hp_plan' => '132', 'homepass_bak' => '132', 'ba_open_date' => '2026-05-06', 'bak_date' => '2026-05-08',
                'homepass_valsal' => '130', 'valsal_date' => '2026-05-10',
                'hp_donasi' => '130', 'submission_date' => '2026-05-12', 'nominal_pengajuan_area' => '81200000', 'nominal_nego_emr' => '80000000', 'nominal_release_finance' => '79800000',
                'bank_name' => 'MANDIRI', 'bank_account_number' => '444555666', 'recipient_name' => 'Ketua RT Purwodadi', 'recipient_phone' => '081212120002', 'recipient_position' => 'Ketua RT', 'recipient_period' => '2024-2027',
                'free_wifi_qty' => '6', 'free_wifi_period_month' => '12', 'astri_batch_number' => 'BATCH-RFS-002',
                'staging_status' => 'RELEASED', 'released_at' => '2026-05-16 09:20:00', 'remark_batch_approval' => 'Batch lengkap untuk proses RFS 2',
                'homepass_drm' => '130', 'drm_date' => '2026-05-17', 'nama_olt' => 'OLT-MLG-RFS2',
                'rfs_date' => '2026-05-21', 'email_atp_date' => '2026-05-24', 'actual_atp_date' => '2026-05-27', 'status_rfs' => 'PARTIAL', 'status_atp' => 'PUNCLIST', 'status_current' => 'ATP',
                'po_cluster_category' => 'FINAL', 'po_cluster_status' => 'ISSUED', 'po_cluster_number' => 'PO-MLG-RFS2', 'po_cluster_date' => '2026-05-15', 'po_cluster_value' => '81200000',
                'po_subfeeder_category' => 'AMANDMENT', 'po_subfeeder_status' => 'ISSUED', 'po_subfeeder_number' => 'PO-SF-MLG-RFS2', 'po_subfeeder_date' => '2026-05-15', 'po_subfeeder_value' => '9000000',
                'po_cluster_termin1_plan_invoice' => '0', 'po_cluster_termin1_submit_invoice' => '2026-05-18', 'po_cluster_termin1_nilai_invoice' => '16240000',
                'po_subfeeder_termin1_plan_invoice' => '0', 'po_subfeeder_termin1_submit_invoice' => '2026-05-18', 'po_subfeeder_termin1_nilai_invoice' => '1800000',
                'remark_general' => 'Contoh cluster RFS 2'
            ],
            [
                'city_name' => 'MALANG', 'district_name' => 'LOWOKWARU', 'village_name' => 'TULUSREJO', 'cluster_name' => 'Cluster RFS 3', 'cluster_code' => 'CL-RFS-3',
                'hp_plan' => '148', 'homepass_bak' => '148', 'ba_open_date' => '2026-05-07', 'bak_date' => '2026-05-09',
                'homepass_valsal' => '145', 'valsal_date' => '2026-05-11',
                'hp_donasi' => '145', 'submission_date' => '2026-05-13', 'nominal_pengajuan_area' => '90500000', 'nominal_nego_emr' => '89000000', 'nominal_release_finance' => '88500000',
                'bank_name' => 'BRI', 'bank_account_number' => '777888999', 'recipient_name' => 'Ketua Paguyuban Tulusrejo', 'recipient_phone' => '081212120003', 'recipient_position' => 'Ketua Paguyuban', 'recipient_period' => '2023-2026',
                'free_wifi_qty' => '4', 'free_wifi_period_month' => '6', 'astri_batch_number' => 'BATCH-RFS-003',
                'staging_status' => 'RELEASED', 'released_at' => '2026-05-17 13:00:00', 'remark_batch_approval' => 'Batch lengkap untuk proses RFS 3',
                'homepass_drm' => '145', 'drm_date' => '2026-05-18', 'nama_olt' => 'OLT-MLG-RFS3',
                'rfs_date' => '2026-05-22', 'email_atp_date' => '2026-05-25', 'actual_atp_date' => '2026-05-29', 'status_rfs' => 'FULL RFS', 'status_atp' => 'DONE', 'status_current' => 'DONE',
                'po_subfeeder_category' => 'INITIAL', 'po_subfeeder_status' => 'ISSUED', 'po_subfeeder_number' => 'PO-SF-MLG-RFS3', 'po_subfeeder_date' => '2026-05-16', 'po_subfeeder_value' => '9050000',
                'po_subfeeder_termin1_plan_invoice' => '0', 'po_subfeeder_termin1_submit_invoice' => '2026-05-19', 'po_subfeeder_termin1_nilai_invoice' => '1810000',
                'po_subfeeder_termin2_plan_invoice' => '2262500', 'po_subfeeder_termin2_submit_invoice' => '', 'po_subfeeder_termin2_sertifikat_invoice' => '2026-05-30', 'po_subfeeder_termin2_nilai_invoice' => '0',
                'remark_general' => 'Contoh cluster RFS 3'
            ],
            [
                'city_name' => 'MALANG', 'district_name' => 'KLOJEN', 'village_name' => 'KASIN', 'cluster_name' => 'Cluster ATP 1', 'cluster_code' => 'CL-ATP-1',
                'hp_plan' => '100', 'homepass_bak' => '100', 'ba_open_date' => '2026-05-08', 'bak_date' => '2026-05-10',
                'homepass_valsal' => '98', 'valsal_date' => '2026-05-12',
                'hp_donasi' => '98', 'submission_date' => '2026-05-14', 'nominal_pengajuan_area' => '61000000', 'nominal_nego_emr' => '60200000', 'nominal_release_finance' => '60000000',
                'bank_name' => 'BCA', 'bank_account_number' => '121212121', 'recipient_name' => 'Ketua RW Kasin', 'recipient_phone' => '081233330001', 'recipient_position' => 'Ketua RW', 'recipient_period' => '2025-2028',
                'free_wifi_qty' => '3', 'free_wifi_period_month' => '12', 'astri_batch_number' => 'BATCH-ATP-001',
                'staging_status' => 'RELEASED', 'released_at' => '2026-05-18 09:10:00', 'remark_batch_approval' => 'Batch lengkap ATP 1',
                'homepass_drm' => '98', 'drm_date' => '2026-05-19', 'nama_olt' => 'OLT-MLG-ATP1',
                'rfs_date' => '2026-05-24', 'email_atp_date' => '2026-05-27', 'actual_atp_date' => '2026-05-29', 'status_rfs' => 'FULL RFS', 'status_atp' => 'PUNCLIST', 'status_current' => 'ATP',
                'rfs_1_date' => '2026-05-24', 'rfs_1_qty' => '98',
                'po_cluster_category' => 'INITIAL', 'po_cluster_status' => 'ISSUED', 'po_cluster_number' => 'PO-MLG-ATP1', 'po_cluster_date' => '2026-05-17', 'po_cluster_value' => '61000000',
                'po_cluster_termin1_plan_invoice' => '0', 'po_cluster_termin1_submit_invoice' => '2026-05-20', 'po_cluster_termin1_nilai_invoice' => '12200000',
                'remark_general' => 'Contoh cluster ATP 1'
            ],
            [
                'city_name' => 'MALANG', 'district_name' => 'BLIMBING', 'village_name' => 'POLAMAN', 'cluster_name' => 'Cluster ATP 2', 'cluster_code' => 'CL-ATP-2',
                'hp_plan' => '115', 'homepass_bak' => '115', 'ba_open_date' => '2026-05-09', 'bak_date' => '2026-05-11',
                'homepass_valsal' => '110', 'valsal_date' => '2026-05-13',
                'hp_donasi' => '110', 'submission_date' => '2026-05-15', 'nominal_pengajuan_area' => '70000000', 'nominal_nego_emr' => '69000000', 'nominal_release_finance' => '68500000',
                'bank_name' => 'MANDIRI', 'bank_account_number' => '343434343', 'recipient_name' => 'Ketua RT Polaman', 'recipient_phone' => '081233330002', 'recipient_position' => 'Ketua RT', 'recipient_period' => '2024-2027',
                'free_wifi_qty' => '4', 'free_wifi_period_month' => '12', 'astri_batch_number' => 'BATCH-ATP-002',
                'staging_status' => 'RELEASED', 'released_at' => '2026-05-19 10:05:00', 'remark_batch_approval' => 'Batch lengkap ATP 2',
                'homepass_drm' => '110', 'drm_date' => '2026-05-20', 'nama_olt' => 'OLT-MLG-ATP2',
                'rfs_date' => '2026-05-25', 'email_atp_date' => '2026-05-28', 'actual_atp_date' => '2026-05-30', 'status_rfs' => 'PARTIAL', 'status_atp' => 'PUNCLIST', 'status_current' => 'ATP',
                'rfs_1_date' => '2026-05-25', 'rfs_1_qty' => '90',
                'rfs_2_date' => '2026-05-27', 'rfs_2_qty' => '15',
                'po_subfeeder_category' => 'INITIAL', 'po_subfeeder_status' => 'ISSUED', 'po_subfeeder_number' => 'PO-SF-MLG-ATP2', 'po_subfeeder_date' => '2026-05-18', 'po_subfeeder_value' => '7000000',
                'po_subfeeder_termin1_plan_invoice' => '0', 'po_subfeeder_termin1_submit_invoice' => '2026-05-22', 'po_subfeeder_termin1_nilai_invoice' => '1400000',
                'remark_general' => 'Contoh cluster ATP 2'
            ],
            [
                'city_name' => 'MALANG', 'district_name' => 'LOWOKWARU', 'village_name' => 'MOJOLANGU', 'cluster_name' => 'Cluster ATP 3', 'cluster_code' => 'CL-ATP-3',
                'hp_plan' => '125', 'homepass_bak' => '125', 'ba_open_date' => '2026-05-10', 'bak_date' => '2026-05-12',
                'homepass_valsal' => '122', 'valsal_date' => '2026-05-14',
                'hp_donasi' => '122', 'submission_date' => '2026-05-16', 'nominal_pengajuan_area' => '76000000', 'nominal_nego_emr' => '74800000', 'nominal_release_finance' => '74200000',
                'bank_name' => 'BRI', 'bank_account_number' => '565656565', 'recipient_name' => 'Ketua RW Mojolangu', 'recipient_phone' => '081233330003', 'recipient_position' => 'Ketua RW', 'recipient_period' => '2023-2026',
                'free_wifi_qty' => '6', 'free_wifi_period_month' => '6', 'astri_batch_number' => 'BATCH-ATP-003',
                'staging_status' => 'RELEASED', 'released_at' => '2026-05-20 11:25:00', 'remark_batch_approval' => 'Batch lengkap ATP 3',
                'homepass_drm' => '122', 'drm_date' => '2026-05-21', 'nama_olt' => 'OLT-MLG-ATP3',
                'rfs_date' => '2026-05-26', 'email_atp_date' => '2026-05-29', 'actual_atp_date' => '2026-06-01', 'status_rfs' => 'FULL RFS', 'status_atp' => 'DONE', 'status_current' => 'CHECKLIST DOKUMENT',
                'rfs_1_date' => '2026-05-26', 'rfs_1_qty' => '122',
                'po_cluster_category' => 'FINAL', 'po_cluster_status' => 'ISSUED', 'po_cluster_number' => 'PO-MLG-ATP3', 'po_cluster_date' => '2026-05-19', 'po_cluster_value' => '76000000',
                'po_cluster_termin1_plan_invoice' => '0', 'po_cluster_termin1_submit_invoice' => '2026-05-23', 'po_cluster_termin1_nilai_invoice' => '15200000',
                'remark_general' => 'Contoh cluster ATP 3 - CHECKLIST DOKUMENT'
            ],
        ];
        foreach ($poExampleMaps as $poMap) {
            $poRow = [];
            foreach ($headers as $header) {
                $poRow[] = isset($poMap[$header]) ? (string) $poMap[$header] : '';
            }
            $remappedRows[] = $poRow;
        }
        $exampleRows = $remappedRows;

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
        foreach ($exampleRows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    public function downloadCutoffCurrentSnapshot()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedStatus = strtoupper(trim((string) $this->input->get('status')));
        $headers = $this->getCutoffImportHeaders();
        $rows = $this->getCutoffCurrentSnapshotRows($selectedCity, $selectedStatus);
        $filename = 'update_import_myrep_project_current_' . date('Ymd_His') . '.csv';

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
        foreach ($rows as $rowMap) {
            $line = [];
            foreach ($headers as $header) {
                $line[] = isset($rowMap[$header]) ? (string) $rowMap[$header] : '';
            }
            fputcsv($output, $line);
        }
        fclose($output);
        exit;
    }

    private function buildDetailPayload($cluster, $isLegacy)
    {
        $myrepClusterId = (int) ($cluster['id_myrep_cluster'] ?? 0);
        $rfsClusterId = (int) ($cluster['rfs_cluster_id'] ?? $cluster['legacy_rfs_cluster_id'] ?? 0);

        $data['title'] = 'Detail Project MyRep';
        $data['cluster'] = $cluster;
        $data['isLegacy'] = (bool) $isLegacy;
        $data['stageTimeline'] = $this->MMyRepublik_Project->buildStageTimeline($cluster, $isLegacy);
        $data['flowSummaries'] = $myrepClusterId > 0
            ? $this->MMyRepublik_Project->getFlowDocumentSummaries($myrepClusterId)
            : [];
        $data['flowDocuments'] = $myrepClusterId > 0
            ? $this->MMyRepublik_Project->getAllFlowDocuments($myrepClusterId)
            : [];
        $data['batchPics'] = $myrepClusterId > 0
            ? $this->MMyRepublik_Project->getBatchPics($myrepClusterId)
            : [];
        $data['poHeaders'] = $myrepClusterId > 0
            ? $this->MMyRepublik_Project->getPoHeadersWithTermins($myrepClusterId)
            : [];
        $data['claimRows'] = $rfsClusterId > 0
            ? $this->MMyRepublik_Project->getRfsClaims($rfsClusterId)
            : [];
        $data['packageRows'] = $rfsClusterId > 0
            ? $this->MMyRepublik_Project->getRfsPackages($rfsClusterId)
            : [];
        $data['canQuickUpdate'] = !$isLegacy
            && $myrepClusterId > 0
            && $this->myrepAccess->hasPermission('MyRepublik_Project', 'EDIT');
        $data['quickUpdateData'] = $data['canQuickUpdate']
            ? $this->buildQuickUpdateData($cluster, $data['claimRows'])
            : [];

        return $data;
    }

    private function buildQuickUpdateData(array $cluster, array $claimRows = [])
    {
        $row = [
            'status_current' => (string) ($cluster['status_current'] ?? ''),
            'city_name' => (string) ($cluster['city_name'] ?? ''),
            'district_name' => (string) ($cluster['district_name'] ?? ''),
            'village_name' => (string) ($cluster['village_name'] ?? ''),
            'cluster_name' => (string) ($cluster['cluster_name'] ?? ''),
            'cluster_code' => (string) ($cluster['cluster_code'] ?? ''),
            'hp_plan' => $this->formatQuickIntegerValue($cluster['hp_plan'] ?? ''),
            'homepass_bak' => $this->formatQuickIntegerValue($cluster['homepass_bak'] ?? ''),
            'ba_open_date' => (string) ($cluster['ba_open_date'] ?? ''),
            'bak_date' => (string) ($cluster['bak_date'] ?? ''),
            'nomor_ntp' => (string) ($cluster['ntp_name'] ?? ''),
            'tanggal_ntp' => (string) ($cluster['ntp_date'] ?? ''),
            'homepass_valsal' => $this->formatQuickIntegerValue($cluster['homepass_valsal'] ?? ''),
            'valsal_date' => (string) ($cluster['valsal_date'] ?? ''),
            'remark_valsal' => (string) ($cluster['remark_valsal'] ?? ''),
            'hp_donasi' => $this->formatQuickIntegerValue($cluster['hp_donasi'] ?? ''),
            'submission_date' => (string) ($cluster['submission_date'] ?? ''),
            'nominal_pengajuan_area' => $this->formatQuickIntegerValue($cluster['nominal_pengajuan_area'] ?? ''),
            'nominal_nego_emr' => $this->formatQuickIntegerValue($cluster['nominal_nego_emr'] ?? ''),
            'nominal_release_finance' => $this->formatQuickIntegerValue($cluster['nominal_release_finance'] ?? ''),
            'nominal_per_homepass' => $this->formatQuickIntegerValue($cluster['nominal_per_homepass'] ?? ''),
            'bank_name' => (string) ($cluster['bank_name'] ?? ''),
            'bank_account_number' => $this->formatQuickTextNumber($cluster['bank_account_number'] ?? ''),
            'recipient_name' => (string) ($cluster['recipient_name'] ?? ''),
            'recipient_phone' => $this->formatQuickTextNumber($cluster['recipient_phone'] ?? ''),
            'recipient_position' => (string) ($cluster['recipient_position'] ?? ''),
            'recipient_period' => (string) ($cluster['recipient_period'] ?? ''),
            'free_wifi_qty' => $this->formatQuickIntegerValue($cluster['free_wifi_qty'] ?? ''),
            'free_wifi_period_month' => $this->formatQuickIntegerValue($cluster['free_wifi_period_month'] ?? ''),
            'astri_batch_number' => (string) ($cluster['astri_batch_number'] ?? ''),
            'staging_status' => (string) ($cluster['staging_status'] ?? ''),
            'released_at' => (string) ($cluster['released_at'] ?? ''),
            'remark_batch_approval' => (string) ($cluster['remark_batch_approval'] ?? ''),
            'homepass_drm' => $this->formatQuickIntegerValue($cluster['homepass_drm'] ?? ''),
            'drm_date' => (string) ($cluster['drm_date'] ?? ''),
            'nama_olt' => (string) ($cluster['nama_olt'] ?? ''),
            'remark_drm' => (string) ($cluster['remark_drm'] ?? ''),
            'rfs_date' => (string) ($cluster['latest_claim_date'] ?? $cluster['tanggal_rfs'] ?? ''),
            'status_rfs' => (string) ($cluster['status_rfs'] ?? ''),
            'email_atp_date' => (string) ($cluster['email_atp_date'] ?? ''),
            'actual_atp_date' => (string) ($cluster['actual_atp_date'] ?? ''),
            'status_atp' => (string) ($cluster['status_atp'] ?? ''),
            'remark_general' => (string) ($cluster['remark_general'] ?? ''),
        ];

        $claims = array_values($claimRows);
        usort($claims, static function ($a, $b) {
            return strcmp((string) ($a['claim_date'] ?? ''), (string) ($b['claim_date'] ?? ''));
        });
        for ($i = 1; $i <= 5; $i++) {
            $claim = $claims[$i - 1] ?? [];
            $row['rfs_' . $i . '_date'] = (string) ($claim['claim_date'] ?? '');
            $row['rfs_' . $i . '_qty'] = $this->formatQuickIntegerValue($claim['claim_qty'] ?? '');
        }
        $row['status_rfs'] = $this->resolveQuickRfsStatus($row['status_rfs'] ?? '', $claims, $cluster);

        return $row;
    }

    private function getCutoffImportHeaders()
    {
        $headers = [
            'status_current',
            'city_name',
            'district_name',
            'village_name',
            'cluster_name',
            'cluster_code',
            'hp_plan',
            'homepass_bak',
            'ba_open_date',
            'bak_date',
            'nomor_ntp',
            'tanggal_ntp',
            'homepass_valsal',
            'valsal_date',
            'remark_valsal',
            'hp_donasi',
            'submission_date',
            'nominal_pengajuan_area',
            'nominal_nego_emr',
            'nominal_release_finance',
            'nominal_per_homepass',
            'bank_name',
            'bank_account_number',
            'recipient_name',
            'recipient_phone',
            'recipient_position',
            'recipient_period',
            'free_wifi_qty',
            'free_wifi_period_month',
            'astri_batch_number',
            'staging_status',
            'released_at',
            'remark_batch_approval',
            'homepass_drm',
            'drm_date',
            'nama_olt',
            'remark_drm',
            'rfs_date',
            'status_rfs',
            'email_atp_date',
            'actual_atp_date',
            'status_atp',
            'cluster_cwatp',
            'cluster_fullopm',
            'cluster_rfs',
            'cluster_rfs_nro_flow',
            'subfeeder_cwatp',
            'subfeeder_fullopm',
            'subfeeder_rfs',
            'po_cluster_category',
            'po_cluster_status',
            'po_cluster_on_target',
            'po_cluster_number',
            'po_cluster_date',
            'po_cluster_value',
            'po_cluster_version_label',
            'po_cluster_remark',
            'po_subfeeder_category',
            'po_subfeeder_status',
            'po_subfeeder_on_target',
            'po_subfeeder_number',
            'po_subfeeder_date',
            'po_subfeeder_value',
            'po_subfeeder_version_label',
            'po_subfeeder_remark',
            'remark_general',
        ];

        $headers = array_merge($headers, $this->buildRfsClaimImportHeaders());
        return array_merge($headers, $this->buildPoTerminImportHeaders());
    }

    private function getCutoffCurrentSnapshotRows($selectedCity = '', $selectedStatus = '')
    {
        if (!$this->db->table_exists('tb_myrep_cluster')) {
            return [];
        }

        $query = $this->db
            ->select("
                c.id_myrep_cluster,
                c.rfs_cluster_id,
                c.status_current,
                c.city_name,
                c.district_name,
                c.village_name,
                c.cluster_name,
                c.cluster_code,
                c.hp_plan,
                c.ntp_name AS nomor_ntp,
                c.ntp_date AS tanggal_ntp,
                c.remark_general,
                b.homepass_bak,
                b.ba_open_date,
                b.bak_date,
                v.homepass_valsal,
                v.valsal_date,
                v.remark_valsal,
                ba.hp_donasi,
                ba.submission_date,
                ba.nominal_pengajuan_area,
                ba.nominal_nego_emr,
                ba.nominal_release_finance,
                ba.nominal_per_homepass,
                ba.bank_name,
                ba.bank_account_number,
                ba.recipient_name,
                ba.recipient_phone,
                ba.recipient_position,
                ba.recipient_period,
                ba.free_wifi_qty,
                ba.free_wifi_period_month,
                ba.astri_batch_number,
                ba.staging_status,
                ba.released_at,
                ba.remark_batch_approval,
                d.homepass_drm,
                d.drm_date,
                d.nama_olt,
                d.remark_drm,
                r.status_rfs,
                r.email_atp_date,
                r.status_atp,
                latest_claim.rfs_date,
                atp_summary.actual_atp_date
            ", false)
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_rfs_myrep_cluster r', 'r.id_cluster = c.rfs_cluster_id', 'left')
            ->join('(
                SELECT cluster_id, MAX(claim_date) AS rfs_date
                FROM tb_rfs_myrep_claim
                WHERE status_claim = "APPROVED"
                GROUP BY cluster_id
            ) latest_claim', 'latest_claim.cluster_id = c.rfs_cluster_id', 'left')
            ->join('(
                SELECT cluster_id, MAX(actual_atp_date) AS actual_atp_date
                FROM tb_rfs_myrep_doc_package
                GROUP BY cluster_id
            ) atp_summary', 'atp_summary.cluster_id = c.rfs_cluster_id', 'left');

        if ($selectedCity !== '') {
            $query->where('UPPER(c.city_name)', strtoupper($selectedCity));
        }
        if ($selectedStatus !== '') {
            $query->where('UPPER(c.status_current)', strtoupper($selectedStatus));
        }

        $rows = $query
            ->order_by('c.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();
        if (empty($rows)) {
            return [];
        }

        $rfsClusterIds = [];
        foreach ($rows as $row) {
            if (!empty($row['rfs_cluster_id'])) {
                $rfsClusterIds[] = (int) $row['rfs_cluster_id'];
            }
        }
        $claimMap = $this->getCurrentRfsClaimsForSnapshot($rfsClusterIds);
        $myrepClusterIds = array_values(array_filter(array_map(static function ($row) {
            return (int) ($row['id_myrep_cluster'] ?? 0);
        }, $rows)));
        $poMap = $this->getCurrentPoHeadersForSnapshot($myrepClusterIds);
        $poHeaderIds = [];
        foreach ($poMap as $poByType) {
            foreach ($poByType as $poRow) {
                if (!empty($poRow['id_po_header'])) {
                    $poHeaderIds[] = (int) $poRow['id_po_header'];
                }
            }
        }
        $poTerminMap = $this->getCurrentPoTerminRowsForSnapshot($poHeaderIds);

        $result = [];
        foreach ($rows as $row) {
            $rfsClusterId = (int) ($row['rfs_cluster_id'] ?? 0);
            $myrepClusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $checklistStatuses = $rfsClusterId > 0 ? $this->getCurrentChecklistImportStatuses($rfsClusterId) : [];
            $clusterPo = $poMap[$myrepClusterId]['CLUSTER'] ?? [];
            $subfeederPo = $poMap[$myrepClusterId]['SUBFEEDER'] ?? [];
            $rowMap = [
                'status_current' => (string) ($row['status_current'] ?? ''),
                'city_name' => (string) ($row['city_name'] ?? ''),
                'district_name' => (string) ($row['district_name'] ?? ''),
                'village_name' => (string) ($row['village_name'] ?? ''),
                'cluster_name' => (string) ($row['cluster_name'] ?? ''),
                'cluster_code' => (string) ($row['cluster_code'] ?? ''),
                'hp_plan' => (string) ($row['hp_plan'] ?? ''),
                'homepass_bak' => (string) ($row['homepass_bak'] ?? ''),
                'ba_open_date' => (string) ($row['ba_open_date'] ?? ''),
                'bak_date' => (string) ($row['bak_date'] ?? ''),
                'nomor_ntp' => (string) ($row['nomor_ntp'] ?? ''),
                'tanggal_ntp' => (string) ($row['tanggal_ntp'] ?? ''),
                'homepass_valsal' => (string) ($row['homepass_valsal'] ?? ''),
                'valsal_date' => (string) ($row['valsal_date'] ?? ''),
                'remark_valsal' => (string) ($row['remark_valsal'] ?? ''),
                'hp_donasi' => (string) ($row['hp_donasi'] ?? ''),
                'submission_date' => (string) ($row['submission_date'] ?? ''),
                'nominal_pengajuan_area' => (string) ($row['nominal_pengajuan_area'] ?? ''),
                'nominal_nego_emr' => (string) ($row['nominal_nego_emr'] ?? ''),
                'nominal_release_finance' => (string) ($row['nominal_release_finance'] ?? ''),
                'nominal_per_homepass' => (string) ($row['nominal_per_homepass'] ?? ''),
                'bank_name' => (string) ($row['bank_name'] ?? ''),
                'bank_account_number' => (string) ($row['bank_account_number'] ?? ''),
                'recipient_name' => (string) ($row['recipient_name'] ?? ''),
                'recipient_phone' => (string) ($row['recipient_phone'] ?? ''),
                'recipient_position' => (string) ($row['recipient_position'] ?? ''),
                'recipient_period' => (string) ($row['recipient_period'] ?? ''),
                'free_wifi_qty' => (string) ($row['free_wifi_qty'] ?? ''),
                'free_wifi_period_month' => (string) ($row['free_wifi_period_month'] ?? ''),
                'astri_batch_number' => (string) ($row['astri_batch_number'] ?? ''),
                'staging_status' => (string) ($row['staging_status'] ?? ''),
                'released_at' => (string) ($row['released_at'] ?? ''),
                'remark_batch_approval' => (string) ($row['remark_batch_approval'] ?? ''),
                'homepass_drm' => (string) ($row['homepass_drm'] ?? ''),
                'drm_date' => (string) ($row['drm_date'] ?? ''),
                'nama_olt' => (string) ($row['nama_olt'] ?? ''),
                'remark_drm' => (string) ($row['remark_drm'] ?? ''),
                'rfs_date' => (string) ($row['rfs_date'] ?? ''),
                'status_rfs' => (string) ($row['status_rfs'] ?? ''),
                'email_atp_date' => (string) ($row['email_atp_date'] ?? ''),
                'actual_atp_date' => (string) ($row['actual_atp_date'] ?? ''),
                'status_atp' => (string) ($row['status_atp'] ?? ''),
                'remark_general' => (string) ($row['remark_general'] ?? ''),
                'po_cluster_category' => (string) ($clusterPo['po_category'] ?? ''),
                'po_cluster_status' => (string) ($clusterPo['status_po'] ?? ''),
                'po_cluster_on_target' => $this->formatImportBoolean($clusterPo['on_target'] ?? ''),
                'po_cluster_number' => (string) ($clusterPo['po_number'] ?? ''),
                'po_cluster_date' => (string) ($clusterPo['po_date'] ?? ''),
                'po_cluster_value' => (string) ($clusterPo['po_value'] ?? ''),
                'po_cluster_version_label' => (string) ($clusterPo['po_version_label'] ?? ''),
                'po_cluster_remark' => (string) ($clusterPo['remark_po'] ?? ''),
                'po_subfeeder_category' => (string) ($subfeederPo['po_category'] ?? ''),
                'po_subfeeder_status' => (string) ($subfeederPo['status_po'] ?? ''),
                'po_subfeeder_on_target' => $this->formatImportBoolean($subfeederPo['on_target'] ?? ''),
                'po_subfeeder_number' => (string) ($subfeederPo['po_number'] ?? ''),
                'po_subfeeder_date' => (string) ($subfeederPo['po_date'] ?? ''),
                'po_subfeeder_value' => (string) ($subfeederPo['po_value'] ?? ''),
                'po_subfeeder_version_label' => (string) ($subfeederPo['po_version_label'] ?? ''),
                'po_subfeeder_remark' => (string) ($subfeederPo['remark_po'] ?? ''),
            ];
            $this->appendPoTerminSnapshotColumns($rowMap, 'po_cluster', $clusterPo, $poTerminMap);
            $this->appendPoTerminSnapshotColumns($rowMap, 'po_subfeeder', $subfeederPo, $poTerminMap);

            foreach (array_keys($this->getChecklistImportColumnMap()) as $column) {
                $rowMap[$column] = (string) ($checklistStatuses[$column] ?? '');
            }
            foreach (array_keys($this->getChecklistNroFlowImportColumnMap()) as $column) {
                $rowMap[$column] = (string) ($checklistStatuses[$column] ?? '');
            }

            $claims = $claimMap[$rfsClusterId] ?? [];
            for ($i = 1; $i <= 5; $i++) {
                $claim = $claims[$i - 1] ?? [];
                $rowMap['rfs_' . $i . '_date'] = (string) ($claim['claim_date'] ?? '');
                $rowMap['rfs_' . $i . '_qty'] = (string) ($claim['claim_qty'] ?? '');
            }

            $result[] = $this->normalizeCutoffSnapshotRowForExport($rowMap);
        }

        return $result;
    }

    private function normalizeCutoffSnapshotRowForExport(array $rowMap)
    {
        $normalized = [];
        foreach ($this->getCutoffImportHeaders() as $header) {
            $normalized[$header] = isset($rowMap[$header]) ? (string) $rowMap[$header] : '';
        }

        return $normalized;
    }

    private function getCurrentRfsClaimsForSnapshot(array $rfsClusterIds)
    {
        $rfsClusterIds = array_values(array_unique(array_filter(array_map('intval', $rfsClusterIds))));
        if (empty($rfsClusterIds) || !$this->db->table_exists('tb_rfs_myrep_claim')) {
            return [];
        }

        $rows = $this->db
            ->select('cluster_id, claim_date, claim_qty')
            ->from('tb_rfs_myrep_claim')
            ->where_in('cluster_id', $rfsClusterIds)
            ->where('status_claim', 'APPROVED')
            ->order_by('claim_date', 'ASC')
            ->order_by('id_claim', 'ASC')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $clusterId = (int) ($row['cluster_id'] ?? 0);
            if ($clusterId <= 0 || count($map[$clusterId] ?? []) >= 5) {
                continue;
            }
            $map[$clusterId][] = $row;
        }

        return $map;
    }

    private function buildClusterStageSummaryRows(array $clusterRows)
    {
        $rows = [];

        foreach ($clusterRows as $clusterRow) {
            $cityName = strtoupper(trim((string) ($clusterRow['city_name'] ?? '-')));
            if ($cityName === '') {
                $cityName = '-';
            }
            $homepassValue = $this->resolveClusterHomepassValue($clusterRow);

            if (!isset($rows[$cityName])) {
                $rows[$cityName] = [
                    'city_name' => $cityName,
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
            }

            $statusCurrent = strtoupper(trim((string) ($clusterRow['status_current'] ?? '')));
            $statusDisplay = strtoupper(trim((string) ($clusterRow['status_current_display'] ?? $statusCurrent)));
            $statusDrm = strtoupper(trim((string) ($clusterRow['status_drm'] ?? '')));

            if (in_array($statusCurrent, ['DRAFT', 'BA OPEN', 'BAK'], true)) {
                $rows[$cityName]['bak'] += $homepassValue;
            } elseif ($statusCurrent === 'VALSAL') {
                $rows[$cityName]['valsal'] += $homepassValue;
            } elseif (in_array($statusCurrent, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'], true)) {
                $rows[$cityName]['batch'] += $homepassValue;
            } elseif ($statusCurrent === 'DRM') {
                $rows[$cityName]['drm'] += $homepassValue;
            } elseif ($statusDisplay === 'IMPLEMENTASI' || ($statusCurrent === 'DONE' && strpos($statusDrm, 'IMPLEMENTASI') !== false)) {
                $rows[$cityName]['implementasi'] += $homepassValue;
            } elseif ($statusCurrent === 'RFS') {
                $rows[$cityName]['rfs'] += $homepassValue;
            } elseif ($statusCurrent === 'ATP') {
                $rows[$cityName]['atp'] += $homepassValue;
            } elseif (in_array($statusCurrent, ['CHECKLIST DOKUMENT', 'DONE'], true)) {
                $rows[$cityName]['dokument'] += $homepassValue;
            }

            $rows[$cityName]['total'] += $homepassValue;
        }

        if (!empty($rows)) {
            ksort($rows);
        }

        $result = array_values($rows);
        if (empty($result)) {
            return $result;
        }

        $totalRow = [
            'city_name' => 'TOTAL',
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

        foreach ($result as $row) {
            $totalRow['bak'] += (int) ($row['bak'] ?? 0);
            $totalRow['valsal'] += (int) ($row['valsal'] ?? 0);
            $totalRow['batch'] += (int) ($row['batch'] ?? 0);
            $totalRow['drm'] += (int) ($row['drm'] ?? 0);
            $totalRow['implementasi'] += (int) ($row['implementasi'] ?? 0);
            $totalRow['rfs'] += (int) ($row['rfs'] ?? 0);
            $totalRow['atp'] += (int) ($row['atp'] ?? 0);
            $totalRow['dokument'] += (int) ($row['dokument'] ?? 0);
            $totalRow['total'] += (int) ($row['total'] ?? 0);
        }

        $result[] = $totalRow;

        return $result;
    }

    private function resolveClusterHomepassValue(array $clusterRow)
    {
        $status = strtoupper(trim((string) ($clusterRow['status_current'] ?? 'DRAFT')));
        $hpPlan = (float) ($clusterRow['hp_plan'] ?? 0);
        $hpBak = (float) ($clusterRow['homepass_bak'] ?? 0);
        $hpValsal = (float) ($clusterRow['homepass_valsal'] ?? 0);
        $hpDonasi = (float) ($clusterRow['hp_donasi'] ?? 0);
        $hpDrm = (float) ($clusterRow['homepass_drm'] ?? 0);
        $hpRfs = (float) ($clusterRow['homepass_rfs'] ?? 0);

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

    private function saveOneImportedCluster(array $row, $userId)
    {
        $clusterName = trim((string) ($row['cluster_name'] ?? ''));
        $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
        $statusCurrent = $this->resolveImportStatusCurrent($row);
        $target = $this->resolveTargetByCity($cityName);
        if (empty($target) || $clusterName === '') {
            return ['inserted' => false, 'message' => 'Target/city atau cluster_name tidak valid.'];
        }
        $targetId = (int) ($target['id_target'] ?? 0);
        if ($targetId <= 0) {
            return ['inserted' => false, 'message' => 'Target ID tidak valid.'];
        }

        // Hindari duplicate insert untuk cluster + target yang sama.
        $existingCluster = $this->db
            ->from('tb_myrep_cluster')
            ->where('id_target', $targetId)
            ->where('UPPER(cluster_name)', strtoupper($clusterName))
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($existingCluster['id_myrep_cluster'])) {
            return $this->syncExistingImportedCluster($existingCluster, $row, $userId, $target, $statusCurrent);
        }

        $homepassPlan = (int) $this->normalizeNumber($row['hp_plan'] ?? 0);
        if ($homepassPlan <= 0) {
            $homepassPlan = (int) $this->normalizeNumber($row['homepass_bak'] ?? 0);
        }
        if ($homepassPlan <= 0) {
            $homepassPlan = (int) $this->normalizeNumber($row['homepass_valsal'] ?? 0);
        }
        if ($homepassPlan <= 0) {
            $homepassPlan = (int) $this->normalizeNumber($row['hp_donasi'] ?? 0);
        }
        if ($homepassPlan <= 0) {
            $homepassPlan = (int) $this->normalizeNumber($row['homepass_drm'] ?? 0);
        }

        if ($homepassPlan <= 0) {
            $homepassPlan = 1;
        }

        $this->db->trans_start();
        $ntpDate = $this->normalizeDate((string) ($row['tanggal_ntp'] ?? ''));
        $this->db->insert('tb_myrep_cluster', [
            'id_target' => $targetId,
            'cluster_name' => $clusterName,
            'cluster_code' => trim((string) ($row['cluster_code'] ?? '')) !== '' ? trim((string) ($row['cluster_code'] ?? '')) : null,
            'regional_name' => trim((string) ($row['regional_name'] ?? '')) !== '' ? trim((string) ($row['regional_name'] ?? '')) : ($target['regional_name'] ?? null),
            'province_name' => trim((string) ($row['province_name'] ?? '')) !== '' ? trim((string) ($row['province_name'] ?? '')) : ($target['province_name'] ?? null),
            'city_name' => $cityName !== '' ? $cityName : ($target['city_name'] ?? null),
            'team_name' => trim((string) ($row['team_name'] ?? '')) !== '' ? trim((string) ($row['team_name'] ?? '')) : ($target['team_name'] ?? null),
            'chief' => trim((string) ($row['chief'] ?? '')) !== '' ? trim((string) ($row['chief'] ?? '')) : ($target['chief'] ?? null),
            'rpm' => trim((string) ($row['rpm'] ?? '')) !== '' ? trim((string) ($row['rpm'] ?? '')) : ($target['rpm'] ?? null),
            'sm' => trim((string) ($row['sm'] ?? '')) !== '' ? trim((string) ($row['sm'] ?? '')) : ($target['sm'] ?? null),
            'spv' => trim((string) ($row['spv'] ?? '')) !== '' ? trim((string) ($row['spv'] ?? '')) : ($target['spv'] ?? null),
            'hp_plan' => $homepassPlan,
            'ntp_name' => trim((string) ($row['nomor_ntp'] ?? '')) !== '' ? trim((string) ($row['nomor_ntp'] ?? '')) : null,
            'ntp_date' => $ntpDate,
            'ntp_year' => $ntpDate ? (int) date('Y', strtotime($ntpDate)) : null,
            'status_current' => $statusCurrent,
            'remark_general' => trim((string) ($row['remark_general'] ?? '')) !== '' ? trim((string) ($row['remark_general'] ?? '')) : null,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
        $clusterId = (int) $this->db->insert_id();
        if ($clusterId <= 0) {
            $this->db->trans_rollback();
            return ['inserted' => false, 'message' => 'Gagal insert ke tb_myrep_cluster.'];
        }

        $this->upsertImportedBak($clusterId, $row, $userId);
        $this->upsertImportedValsal($clusterId, $row, $userId);
        $this->upsertImportedBatch($clusterId, $row, $userId, $statusCurrent);
        $this->upsertImportedDrm($clusterId, $row, $userId);
        $this->upsertImportedPo($clusterId, $row, $userId);
        $rfsClusterId = $this->upsertImportedRfsAtp($clusterId, $row, $userId, $target, $statusCurrent);
        $this->applyImportedChecklistStatuses($rfsClusterId, $row, $userId);

        $this->db->trans_complete();
        if (!$this->db->trans_status()) {
            return ['inserted' => false, 'message' => 'Transaksi gagal (rollback).'];
        }

        return [
            'inserted' => true,
            'cluster_id' => $clusterId,
            'message' => 'OK',
        ];
    }

    private function upsertImportedBak($clusterId, array $row, $userId)
    {
        if (!$this->db->table_exists('tb_myrep_bak')) {
            return;
        }

        $homepassBak = (int) $this->normalizeNumber($row['homepass_bak'] ?? 0);
        if ($homepassBak <= 0) {
            $homepassBak = (int) $this->normalizeNumber($row['hp_plan'] ?? 0);
        }

        $payload = [
            'ba_open_date' => $this->normalizeDate((string) ($row['ba_open_date'] ?? '')) ?: date('Y-m-d'),
            'bak_date' => $this->normalizeDate((string) ($row['bak_date'] ?? '')) ?: date('Y-m-d'),
            'homepass_bak' => max(0, $homepassBak),
            'status_bak' => 'DONE',
            'remark_bak' => trim((string) ($row['remark_general'] ?? '')) ?: null,
            'updated_by' => $userId,
        ];
        $this->upsertSingleByWhere('tb_myrep_bak', ['id_myrep_cluster' => (int) $clusterId], $payload, [
            'id_myrep_cluster' => (int) $clusterId,
            'created_by' => (int) $userId,
        ]);
    }

    private function upsertImportedValsal($clusterId, array $row, $userId)
    {
        if (!$this->db->table_exists('tb_myrep_valsal')) {
            return;
        }

        $homepassValsal = (int) $this->normalizeNumber($row['homepass_valsal'] ?? 0);
        $valsalDate = $this->normalizeDate((string) ($row['valsal_date'] ?? ''));
        $remarkValsal = trim((string) ($row['remark_valsal'] ?? ''));

        // Tahap BAK-only: jangan buat record VALSAL kalau kolom VALSAL tidak diisi.
        if ($homepassValsal <= 0 && !$valsalDate && $remarkValsal === '') {
            return;
        }

        if ($homepassValsal <= 0) {
            $homepassValsal = (int) $this->normalizeNumber($row['homepass_bak'] ?? 0);
        }

        $payload = [
            'valsal_date' => $valsalDate ?: date('Y-m-d'),
            'homepass_valsal' => max(0, $homepassValsal),
            'status_valsal' => 'DONE',
            'remark_valsal' => $remarkValsal !== '' ? $remarkValsal : (trim((string) ($row['remark_general'] ?? '')) ?: null),
            'updated_by' => $userId,
        ];
        $this->upsertSingleByWhere('tb_myrep_valsal', ['id_myrep_cluster' => (int) $clusterId], $payload, [
            'id_myrep_cluster' => (int) $clusterId,
            'created_by' => (int) $userId,
        ]);
    }

    private function upsertImportedBatch($clusterId, array $row, $userId, $statusCurrent)
    {
        if (!$this->db->table_exists('tb_myrep_batch_approval')) {
            return;
        }

        $hpDonasiFromRow = (int) $this->normalizeNumber($row['hp_donasi'] ?? 0);
        $submissionDate = $this->normalizeDate((string) ($row['submission_date'] ?? ''));
        $releasedAt = $this->normalizeDateTime((string) ($row['released_at'] ?? ''));
        $hasBatchPayload = $hpDonasiFromRow > 0 || $submissionDate || $releasedAt;
        $statusNeedsBatch = in_array($statusCurrent, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'], true);

        // Tahap BAK/VALSAL awal: jangan buat batch jika belum ada payload batch
        // dan status import tidak menuntut batch.
        if (!$hasBatchPayload && !$statusNeedsBatch) {
            return;
        }

        $stagingStatusFromRow = strtoupper(trim((string) ($row['staging_status'] ?? '')));
        $allowedStaging = ['DRAFT', 'WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED', 'REJECTED'];

        $stagingStatus = 'WAITING HO';
        if (in_array($statusCurrent, ['WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'], true)) {
            $stagingStatus = $statusCurrent === 'DONE BATCH APPROVAL' ? 'DONE' : $statusCurrent;
            if (in_array($statusCurrent, ['DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'], true)) {
                $stagingStatus = 'RELEASED';
            }
        }
        if (in_array($stagingStatusFromRow, $allowedStaging, true)) {
            $stagingStatus = $stagingStatusFromRow;
        }

        $hasDrmPayload = (int) $this->normalizeNumber($row['homepass_drm'] ?? 0) > 0
            || $this->normalizeDate((string) ($row['drm_date'] ?? '')) !== null
            || trim((string) ($row['nama_olt'] ?? '')) !== ''
            || trim((string) ($row['status_drm'] ?? '')) !== ''
            || trim((string) ($row['remark_drm'] ?? '')) !== '';
        if ($hasDrmPayload && !in_array($stagingStatus, ['WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED'], true)) {
            $stagingStatus = 'WAITING FINANCE';
        }

        $hpDonasi = $hpDonasiFromRow;
        if ($hpDonasi <= 0) {
            $hpDonasi = (int) $this->normalizeNumber($row['homepass_valsal'] ?? 0);
        }

        $nominalPengajuanArea = (float) $this->normalizeNumber($row['nominal_pengajuan_area'] ?? 0);
        $nominalNegoEmr = $this->normalizeNullableNumber($row['nominal_nego_emr'] ?? null);
        $nominalReleaseFinance = $this->normalizeNullableNumber($row['nominal_release_finance'] ?? null);
        $nominalPerHomepass = $this->normalizeNullableNumber($row['nominal_per_homepass'] ?? null);
        if ($nominalPerHomepass === null && $hpDonasi > 0 && $nominalPengajuanArea > 0) {
            $nominalPerHomepass = round($nominalPengajuanArea / $hpDonasi, 2);
        }
        // Sebagian schema production mewajibkan nominal_per_homepass NOT NULL.
        if ($nominalPerHomepass === null) {
            $nominalPerHomepass = 0;
        }

        $freeWifiQty = $this->normalizeNullableInt($row['free_wifi_qty'] ?? null);
        $freeWifiPeriodMonth = $this->normalizeNullableInt($row['free_wifi_period_month'] ?? null);
        $batchRemark = trim((string) ($row['remark_batch_approval'] ?? ''));
        $bankName = trim((string) ($row['bank_name'] ?? ''));
        $bankAccountNumber = trim((string) ($row['bank_account_number'] ?? ''));
        $recipientName = trim((string) ($row['recipient_name'] ?? ''));
        $recipientPhone = trim((string) ($row['recipient_phone'] ?? ''));
        $recipientPosition = trim((string) ($row['recipient_position'] ?? ''));
        $recipientPeriod = trim((string) ($row['recipient_period'] ?? ''));
        $astriBatchNumber = trim((string) ($row['astri_batch_number'] ?? ''));

        $existingBatch = $this->db
            ->from('tb_myrep_batch_approval')
            ->where('id_myrep_cluster', (int) $clusterId)
            ->limit(1)
            ->get()
            ->row_array();

        $submittedToHoAt = in_array($stagingStatus, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED'], true)
            ? (!empty($existingBatch['submitted_to_ho_at']) ? $existingBatch['submitted_to_ho_at'] : date('Y-m-d H:i:s'))
            : null;
        $submittedToMyrepAt = in_array($stagingStatus, ['WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED'], true)
            ? (!empty($existingBatch['submitted_to_astri_at']) ? $existingBatch['submitted_to_astri_at'] : date('Y-m-d H:i:s'))
            : null;
        $submittedToFinanceAt = in_array($stagingStatus, ['WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED'], true)
            ? (!empty($existingBatch['submitted_to_finance_at']) ? $existingBatch['submitted_to_finance_at'] : date('Y-m-d H:i:s'))
            : null;

        $payload = [
            'submission_date' => $submissionDate ?: date('Y-m-d'),
            'hp_donasi' => max(0, $hpDonasi),
            'nominal_pengajuan_area' => $nominalPengajuanArea,
            'nominal_nego_emr' => $nominalNegoEmr,
            'nominal_release_finance' => $nominalReleaseFinance,
            'nominal_per_homepass' => $nominalPerHomepass,
            'bank_name' => $bankName !== '' ? $bankName : '-',
            'bank_account_number' => $bankAccountNumber !== '' ? $bankAccountNumber : '-',
            'recipient_name' => $recipientName !== '' ? $recipientName : 'IMPORT CUTOFF',
            'recipient_phone' => $recipientPhone !== '' ? $recipientPhone : null,
            'recipient_position' => $recipientPosition !== '' ? $recipientPosition : null,
            'recipient_period' => $recipientPeriod !== '' ? $recipientPeriod : null,
            'free_wifi_qty' => $freeWifiQty,
            'free_wifi_period_month' => $freeWifiPeriodMonth,
            'astri_batch_number' => $astriBatchNumber !== '' ? $astriBatchNumber : null,
            'staging_status' => $stagingStatus,
            'submitted_to_ho_at' => $submittedToHoAt,
            'submitted_to_astri_at' => $submittedToMyrepAt,
            'submitted_to_finance_at' => $submittedToFinanceAt,
            'released_at' => $releasedAt,
            'remark_batch_approval' => $batchRemark !== '' ? $batchRemark : (trim((string) ($row['remark_general'] ?? '')) ?: null),
            'updated_by' => $userId,
        ];
        $this->upsertSingleByWhere('tb_myrep_batch_approval', ['id_myrep_cluster' => (int) $clusterId], $payload, [
            'id_myrep_cluster' => (int) $clusterId,
            'created_by' => (int) $userId,
        ]);
    }

    private function upsertImportedDrm($clusterId, array $row, $userId)
    {
        if (!$this->db->table_exists('tb_myrep_drm')) {
            return;
        }

        $hpDrm = (int) $this->normalizeNumber($row['homepass_drm'] ?? 0);
        $drmDate = $this->normalizeDate((string) ($row['drm_date'] ?? ''));

        // Tahap awal: jangan buat DRM kalau belum ada payload DRM.
        if ($hpDrm <= 0 && !$drmDate) {
            return;
        }

        if ($hpDrm <= 0) {
            $hpDrm = (int) $this->normalizeNumber($row['hp_donasi'] ?? 0);
        }

        $statusDrm = 'COMPLETE';
        $remarkDrm = trim((string) ($row['remark_drm'] ?? ''));

        $payload = [
            'drm_date' => $drmDate ?: date('Y-m-d'),
            'homepass_drm' => max(0, $hpDrm),
            'nama_olt' => trim((string) ($row['nama_olt'] ?? '')) ?: null,
            'status_drm' => $statusDrm,
            'remark_drm' => $remarkDrm !== '' ? $remarkDrm : (trim((string) ($row['remark_general'] ?? '')) ?: null),
            'updated_by' => $userId,
        ];
        $this->upsertSingleByWhere('tb_myrep_drm', ['id_myrep_cluster' => (int) $clusterId], $payload, [
            'id_myrep_cluster' => (int) $clusterId,
            'created_by' => (int) $userId,
        ]);
    }

    private function upsertImportedRfsAtp($clusterId, array $row, $userId, array $target, $statusCurrent)
    {
        if (!$this->db->table_exists('tb_rfs_myrep_cluster')) {
            return 0;
        }

        $rfsDate = $this->normalizeDate((string) ($row['rfs_date'] ?? $row['tanggal_rfs'] ?? ''));
        $emailAtpDate = $this->normalizeDate((string) ($row['email_atp_date'] ?? ''));
        $atpDate = $this->normalizeDate((string) ($row['actual_atp_date'] ?? ''));
        $statusAtpInput = strtoupper(trim((string) ($row['status_atp'] ?? '')));
        $rfsClaims = $this->extractRfsClaimsFromRow($row);
        $statusNeedsRfs = in_array($statusCurrent, ['RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'], true)
            || $this->hasChecklistImportPayload($row);

        if (!$statusNeedsRfs && !$rfsDate && !$atpDate && empty($rfsClaims)) {
            return 0;
        }

        $homepass = (int) $this->normalizeNumber($row['homepass_drm'] ?? 0);
        if ($homepass <= 0) {
            $homepass = (int) $this->normalizeNumber($row['hp_plan'] ?? 0);
        }
        $homepass = max(0, $homepass);

        $totalClaimQty = 0;
        foreach ($rfsClaims as $claim) {
            $totalClaimQty += (int) ($claim['claim_qty'] ?? 0);
        }

        if ($totalClaimQty <= 0 && $rfsDate) {
            $totalClaimQty = $homepass;
            $rfsClaims[] = [
                'claim_date' => $rfsDate,
                'claim_qty' => $homepass,
            ];
        }
        $homepassRfs = $totalClaimQty > 0 ? $totalClaimQty : $homepass;

        $statusRfsInput = strtoupper(trim((string) ($row['status_rfs'] ?? '')));
        $statusRfs = ($homepassRfs > 0 && $totalClaimQty >= $homepassRfs) ? 'FULL RFS' : 'NY RFS';
        if (in_array($statusCurrent, ['CHECKLIST DOKUMENT', 'DONE'], true) || in_array($statusRfsInput, ['FULL RFS', 'FULL'], true)) {
            // Explicit override from CSV: force full even if claim qty is still below DRM.
            $statusRfs = 'FULL RFS';
        } elseif (in_array($statusRfsInput, ['PARTIAL', 'PARTIAL RFS', 'NY RFS'], true)) {
            $statusRfs = 'NY RFS';
        }
        $statusAtp = 'NOT STARTED';
        if (in_array($statusAtpInput, ['DONE', 'PUNCLIST', 'REJECT'], true)) {
            $statusAtp = $statusAtpInput;
        } elseif (in_array($statusCurrent, ['ATP', 'CHECKLIST DOKUMENT', 'DONE'], true)) {
            $statusAtp = 'DONE';
        } elseif ($emailAtpDate !== null) {
            $statusAtp = 'WAITING';
        }
        $rfsClusterId = 0;
        $linkedCluster = $this->db
            ->select('rfs_cluster_id')
            ->from('tb_myrep_cluster')
            ->where('id_myrep_cluster', (int) $clusterId)
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($linkedCluster['rfs_cluster_id'])) {
            $rfsClusterId = (int) $linkedCluster['rfs_cluster_id'];
        }

        $payload = [
            'id_target' => (int) ($target['id_target'] ?? 0),
            'cluster_name' => trim((string) ($row['cluster_name'] ?? '')),
            'status_rfs' => $statusRfs,
            'homepass' => $homepassRfs,
            'status_atp' => $statusAtp,
            'email_atp_date' => $emailAtpDate,
        ];
        if ($this->db->field_exists('updated_by', 'tb_rfs_myrep_cluster')) {
            $payload['updated_by'] = (int) $userId;
        }

        if ($rfsClusterId > 0) {
            $this->db
                ->where('id_cluster', $rfsClusterId)
                ->update('tb_rfs_myrep_cluster', $this->filterPayloadByTableFields('tb_rfs_myrep_cluster', $payload));
        } else {
            $this->db->insert('tb_rfs_myrep_cluster', $this->filterPayloadByTableFields('tb_rfs_myrep_cluster', array_merge($payload, [
                'created_by' => (int) $userId,
            ])));
            $rfsClusterId = (int) $this->db->insert_id();
        }

        if ($rfsClusterId > 0) {
            $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_cluster', [
                'rfs_cluster_id' => $rfsClusterId,
                'updated_by' => $userId,
            ]);

            if (isset($this->MChecklist_Dokument_MyRep)) {
                $this->MChecklist_Dokument_MyRep->ensureClusterPackages($rfsClusterId, $rfsDate);
            }
        }

        if ($rfsClusterId > 0 && !empty($rfsClaims) && $this->db->table_exists('tb_rfs_myrep_claim')) {
            $claimHasYearNum = $this->db->field_exists('year_num', 'tb_rfs_myrep_claim');
            $claimHasMonthNum = $this->db->field_exists('month_num', 'tb_rfs_myrep_claim');
            $claimHasStatusRfs = $this->db->field_exists('status_rfs', 'tb_rfs_myrep_claim');
            $claimHasStatusClaim = $this->db->field_exists('status_claim', 'tb_rfs_myrep_claim');
            $claimHasClaimYear = $this->db->field_exists('claim_year', 'tb_rfs_myrep_claim');
            $claimHasClaimMonth = $this->db->field_exists('claim_month', 'tb_rfs_myrep_claim');
            $claimHasApprovalStatus = $this->db->field_exists('approval_status', 'tb_rfs_myrep_claim');
            $claimHasRpmApprovalStatus = $this->db->field_exists('rpm_approval_status', 'tb_rfs_myrep_claim');
            $claimHasRpmApprovalNote = $this->db->field_exists('rpm_approval_note', 'tb_rfs_myrep_claim');
            $claimHasRemark = $this->db->field_exists('remark', 'tb_rfs_myrep_claim');
            $claimHasCreatedBy = $this->db->field_exists('created_by', 'tb_rfs_myrep_claim');

            foreach ($rfsClaims as $claim) {
                $claimDate = (string) ($claim['claim_date'] ?? '');
                $claimQty = (int) ($claim['claim_qty'] ?? 0);
                if ($claimDate === '' || $claimQty <= 0) {
                    continue;
                }

                $payload = [
                    'cluster_id' => $rfsClusterId,
                    'claim_date' => $claimDate,
                    'claim_qty' => $claimQty,
                ];

                if ($claimHasYearNum) {
                    $payload['year_num'] = (int) date('Y', strtotime($claimDate));
                }
                if ($claimHasMonthNum) {
                    $payload['month_num'] = (int) date('n', strtotime($claimDate));
                }
                if ($claimHasStatusRfs) {
                    $payload['status_rfs'] = $statusRfs;
                }
                if ($claimHasClaimYear) {
                    $payload['claim_year'] = (int) date('Y', strtotime($claimDate));
                }
                if ($claimHasClaimMonth) {
                    $payload['claim_month'] = (int) date('n', strtotime($claimDate));
                }
                if ($claimHasStatusClaim) {
                    $payload['status_claim'] = 'APPROVED';
                }
                if ($claimHasApprovalStatus) {
                    $payload['approval_status'] = 'APPROVED';
                }
                if ($claimHasRemark) {
                    $payload['remark'] = 'Auto claim from cutoff import';
                }
                if ($claimHasRpmApprovalStatus) {
                    $payload['rpm_approval_status'] = 'APPROVED';
                }
                if ($claimHasRpmApprovalNote) {
                    $payload['rpm_approval_note'] = 'Auto approve from cutoff import';
                }
                $existingClaim = $this->db
                    ->from('tb_rfs_myrep_claim')
                    ->where('cluster_id', (int) $rfsClusterId)
                    ->where('claim_date', $claimDate)
                    ->limit(1)
                    ->get()
                    ->row_array();

                if (!empty($existingClaim['id_claim'])) {
                    $this->db
                        ->where('id_claim', (int) $existingClaim['id_claim'])
                        ->update('tb_rfs_myrep_claim', $this->filterPayloadByTableFields('tb_rfs_myrep_claim', $payload));
                } else {
                    if ($claimHasCreatedBy) {
                        $payload['created_by'] = $userId;
                    }
                    $this->db->insert('tb_rfs_myrep_claim', $this->filterPayloadByTableFields('tb_rfs_myrep_claim', $payload));
                }
            }
        }

        $syncAtpDate = $atpDate;
        if ($syncAtpDate === null && $statusAtp === 'DONE') {
            $syncAtpDate = $emailAtpDate ?: $rfsDate;
        }
        $this->syncChecklistActualAtpDate($rfsClusterId, $syncAtpDate, $userId);

        return $rfsClusterId;
    }

    private function getChecklistImportColumnMap()
    {
        return [
            'cluster_cwatp' => ['scope_type' => 'CLUSTER', 'sow_type' => 'CW ATP'],
            'cluster_fullopm' => ['scope_type' => 'CLUSTER', 'sow_type' => 'FULL OPM'],
            'cluster_rfs' => ['scope_type' => 'CLUSTER', 'sow_type' => 'RFS'],
            'subfeeder_cwatp' => ['scope_type' => 'SUBFEEDER', 'sow_type' => 'CW ATP'],
            'subfeeder_fullopm' => ['scope_type' => 'SUBFEEDER', 'sow_type' => 'FULL OPM'],
            'subfeeder_rfs' => ['scope_type' => 'SUBFEEDER', 'sow_type' => 'RFS'],
        ];
    }

    private function getChecklistNroFlowImportColumnMap()
    {
        return [
            'cluster_rfs_nro_flow' => ['status_column' => 'cluster_rfs'],
        ];
    }

    private function getProjectOpnameFlowStatusMap()
    {
        return [
            'WASPANG' => 'WAITING WASPANG',
            'WASPAN' => 'WAITING WASPANG',
            'WAITING WASPANG' => 'WAITING WASPANG',
            'WAITING WASPAN' => 'WAITING WASPANG',
            'PLANNING' => 'WAITING PLANNING',
            'WAITING PLANNING' => 'WAITING PLANNING',
            'TEAMLEADER' => 'WAITING TL',
            'TEAM LEADER' => 'WAITING TL',
            'TL' => 'WAITING TL',
            'WAITING TL' => 'WAITING TL',
            'LOGISTIK' => 'WAITING LOGISTIK',
            'LOGISTIC' => 'WAITING LOGISTIK',
            'WAITING LOGISTIK' => 'WAITING LOGISTIK',
            'WAITING LOGISTIC' => 'WAITING LOGISTIK',
        ];
    }

    private function normalizeProjectOpnameFlowImportStatus($status)
    {
        $status = strtoupper(trim((string) $status));
        $status = preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', $status));
        if ($status === '') {
            return '';
        }

        $map = $this->getProjectOpnameFlowStatusMap();
        return $map[$status] ?? '';
    }

    private function getProjectOpnameFlowImportLabel($status)
    {
        $status = $this->normalizeProjectOpnameFlowImportStatus($status);
        switch ($status) {
            case 'WAITING PLANNING':
                return 'PLANNING';
            case 'WAITING TL':
                return 'TEAMLEADER';
            case 'WAITING LOGISTIK':
                return 'LOGISTIK';
            case 'WAITING WASPANG':
                return 'WASPANG';
            default:
                return '';
        }
    }

    private function syncExistingImportedCluster(array $existingCluster, array $row, $userId, array $target, $statusCurrent)
    {
        $clusterId = (int) ($existingCluster['id_myrep_cluster'] ?? 0);
        if ($clusterId <= 0) {
            return ['inserted' => false, 'message' => 'Cluster existing tidak valid.'];
        }

        $this->db->trans_start();
        $this->overwriteImportedClusterHeader($clusterId, $row, $userId, $target, $statusCurrent);
        $this->upsertImportedBak($clusterId, $row, $userId);
        $this->upsertImportedValsal($clusterId, $row, $userId);
        $this->upsertImportedBatch($clusterId, $row, $userId, $statusCurrent);
        $this->upsertImportedDrm($clusterId, $row, $userId);
        $this->upsertImportedPo($clusterId, $row, $userId);
        $rfsClusterId = $this->upsertImportedRfsAtp($clusterId, $row, $userId, $target, $statusCurrent);
        if ($rfsClusterId > 0) {
            $this->applyImportedChecklistStatuses($rfsClusterId, $row, $userId);
        }
        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['inserted' => false, 'message' => 'Overwrite cluster existing gagal (rollback).'];
        }

        return [
            'inserted' => false,
            'updated' => true,
            'cluster_id' => $clusterId,
            'message' => 'Cluster existing dioverwrite dari data import.',
        ];
    }

    private function overwriteImportedClusterHeader($clusterId, array $row, $userId, array $target, $statusCurrent)
    {
        $ntpDate = $this->normalizeDate((string) ($row['tanggal_ntp'] ?? ''));
        $payload = [
            'id_target' => (int) ($target['id_target'] ?? 0),
            'cluster_name' => trim((string) ($row['cluster_name'] ?? '')),
            'cluster_code' => trim((string) ($row['cluster_code'] ?? '')) !== '' ? trim((string) ($row['cluster_code'] ?? '')) : null,
            'regional_name' => trim((string) ($row['regional_name'] ?? '')) !== '' ? trim((string) ($row['regional_name'] ?? '')) : ($target['regional_name'] ?? null),
            'province_name' => trim((string) ($row['province_name'] ?? '')) !== '' ? trim((string) ($row['province_name'] ?? '')) : ($target['province_name'] ?? null),
            'city_name' => strtoupper(trim((string) ($row['city_name'] ?? ''))),
            'district_name' => trim((string) ($row['district_name'] ?? '')) ?: null,
            'village_name' => trim((string) ($row['village_name'] ?? '')) ?: null,
            'team_name' => trim((string) ($row['team_name'] ?? '')) !== '' ? trim((string) ($row['team_name'] ?? '')) : ($target['team_name'] ?? null),
            'chief' => trim((string) ($row['chief'] ?? '')) !== '' ? trim((string) ($row['chief'] ?? '')) : ($target['chief'] ?? null),
            'rpm' => trim((string) ($row['rpm'] ?? '')) !== '' ? trim((string) ($row['rpm'] ?? '')) : ($target['rpm'] ?? null),
            'sm' => trim((string) ($row['sm'] ?? '')) !== '' ? trim((string) ($row['sm'] ?? '')) : ($target['sm'] ?? null),
            'spv' => trim((string) ($row['spv'] ?? '')) !== '' ? trim((string) ($row['spv'] ?? '')) : ($target['spv'] ?? null),
            'hp_plan' => max(0, (int) $this->normalizeNumber($row['hp_plan'] ?? 0)),
            'ntp_name' => trim((string) ($row['nomor_ntp'] ?? '')) !== '' ? trim((string) ($row['nomor_ntp'] ?? '')) : null,
            'ntp_date' => $ntpDate,
            'ntp_year' => $ntpDate ? (int) date('Y', strtotime($ntpDate)) : null,
            'status_current' => $statusCurrent,
            'remark_general' => trim((string) ($row['remark_general'] ?? '')) !== '' ? trim((string) ($row['remark_general'] ?? '')) : null,
            'updated_by' => (int) $userId,
        ];

        $this->db
            ->where('id_myrep_cluster', (int) $clusterId)
            ->update('tb_myrep_cluster', $this->filterPayloadByTableFields('tb_myrep_cluster', $payload));
    }

    private function getAllowedChecklistImportStatuses()
    {
        return ['AREA', 'HO', 'EMR', 'CLOSED', 'NRO'];
    }

    private function normalizeChecklistImportStatus($status)
    {
        $status = strtoupper(trim((string) $status));
        return in_array($status, $this->getAllowedChecklistImportStatuses(), true) ? $status : '';
    }

    private function hasChecklistImportPayload(array $row)
    {
        foreach (array_keys($this->getChecklistImportColumnMap()) as $column) {
            if (trim((string) ($row[$column] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private function applyImportedChecklistStatuses($rfsClusterId, array $row, $userId)
    {
        $rfsClusterId = (int) $rfsClusterId;
        if ($rfsClusterId <= 0 || !$this->hasChecklistImportPayload($row)) {
            return;
        }
        if (
            !$this->db->table_exists('tb_rfs_myrep_doc_package')
            || !$this->db->table_exists('tb_rfs_myrep_doc_file')
            || !$this->db->table_exists('md_rfs_myrep_doc_group')
            || !$this->db->table_exists('md_rfs_myrep_doc_item')
        ) {
            return;
        }

        if (isset($this->MChecklist_Dokument_MyRep)) {
            $rfsDate = $this->normalizeDate((string) ($row['rfs_date'] ?? $row['tanggal_rfs'] ?? ''));
            $this->MChecklist_Dokument_MyRep->ensureClusterPackages($rfsClusterId, $rfsDate);
        }

        $packageRows = $this->db
            ->select('p.id_doc_package, p.id_doc_group, g.scope_type, g.sow_type')
            ->from('tb_rfs_myrep_doc_package p')
            ->join('md_rfs_myrep_doc_group g', 'g.id_doc_group = p.id_doc_group', 'inner')
            ->where('p.cluster_id', $rfsClusterId)
            ->where('g.is_active', 1)
            ->get()
            ->result_array();

        if (empty($packageRows)) {
            return;
        }

        $packagesByKey = [];
        $groupIds = [];
        foreach ($packageRows as $package) {
            $key = $this->buildChecklistGroupKey($package['scope_type'] ?? '', $package['sow_type'] ?? '');
            $packagesByKey[$key] = $package;
            $groupIds[] = (int) ($package['id_doc_group'] ?? 0);
        }
        $groupIds = array_values(array_unique(array_filter($groupIds)));
        if (empty($groupIds)) {
            return;
        }

        $itemRows = $this->db
            ->select('id_doc_item, id_doc_group, doc_name')
            ->from('md_rfs_myrep_doc_item')
            ->where_in('id_doc_group', $groupIds)
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->order_by('sort_no', 'ASC')
            ->order_by('id_doc_item', 'ASC')
            ->get()
            ->result_array();

        $itemsByGroup = [];
        foreach ($itemRows as $item) {
            $itemsByGroup[(int) ($item['id_doc_group'] ?? 0)][] = $item;
        }

        foreach ($this->getChecklistImportColumnMap() as $column => $target) {
            $status = $this->normalizeChecklistImportStatus($row[$column] ?? '');
            if ($status === '') {
                continue;
            }
            $nroFlowStatus = '';
            if ($column === 'cluster_rfs' && $status === 'NRO') {
                $nroFlowStatus = $this->normalizeProjectOpnameFlowImportStatus($row['cluster_rfs_nro_flow'] ?? '');
            }

            $key = $this->buildChecklistGroupKey($target['scope_type'], $target['sow_type']);
            if (empty($packagesByKey[$key])) {
                continue;
            }

            $package = $packagesByKey[$key];
            $packageId = (int) ($package['id_doc_package'] ?? 0);
            $groupId = (int) ($package['id_doc_group'] ?? 0);
            if ($packageId <= 0 || $groupId <= 0) {
                continue;
            }

            if ($status === 'AREA') {
                $this->clearImportedChecklistPackage($packageId);
            } else {
                foreach ($itemsByGroup[$groupId] ?? [] as $item) {
                    $this->upsertImportedChecklistFile(
                        $packageId,
                        (int) $item['id_doc_item'],
                        $status,
                        $userId,
                        (string) ($item['doc_name'] ?? ''),
                        $nroFlowStatus
                    );
                }
            }

            $this->refreshImportedChecklistPackageStatus($packageId, $userId);
        }
    }

    private function getCurrentChecklistImportStatuses($rfsClusterId)
    {
        $rfsClusterId = (int) $rfsClusterId;
        $result = array_merge(
            array_fill_keys(array_keys($this->getChecklistImportColumnMap()), ''),
            array_fill_keys(array_keys($this->getChecklistNroFlowImportColumnMap()), '')
        );
        if ($rfsClusterId <= 0 || !$this->db->table_exists('tb_rfs_myrep_doc_package')) {
            return $result;
        }

        $packageRows = $this->db
            ->select('p.id_doc_package, p.id_doc_group, g.scope_type, g.sow_type')
            ->from('tb_rfs_myrep_doc_package p')
            ->join('md_rfs_myrep_doc_group g', 'g.id_doc_group = p.id_doc_group', 'inner')
            ->where('p.cluster_id', $rfsClusterId)
            ->where('g.is_active', 1)
            ->get()
            ->result_array();
        if (empty($packageRows)) {
            return $result;
        }

        $groupIds = [];
        $packageIds = [];
        foreach ($packageRows as $package) {
            $groupIds[] = (int) ($package['id_doc_group'] ?? 0);
            $packageIds[] = (int) ($package['id_doc_package'] ?? 0);
        }
        $groupIds = array_values(array_unique(array_filter($groupIds)));
        $packageIds = array_values(array_unique(array_filter($packageIds)));
        if (empty($groupIds) || empty($packageIds)) {
            return $result;
        }

        $itemRows = $this->db
            ->select('id_doc_item, id_doc_group, doc_name')
            ->from('md_rfs_myrep_doc_item')
            ->where_in('id_doc_group', $groupIds)
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->get()
            ->result_array();
        $itemsByGroup = [];
        foreach ($itemRows as $item) {
            $itemsByGroup[(int) ($item['id_doc_group'] ?? 0)][] = $item;
        }

        $fileRows = $this->db
            ->select('id_doc_package, id_doc_item, file_path, is_document_not_required, status_file, astri_status')
            ->from('tb_rfs_myrep_doc_file')
            ->where_in('id_doc_package', $packageIds)
            ->get()
            ->result_array();
        $filesByPackageItem = [];
        foreach ($fileRows as $file) {
            $filesByPackageItem[(int) ($file['id_doc_package'] ?? 0)][(int) ($file['id_doc_item'] ?? 0)] = $file;
        }

        $columnByGroupKey = [];
        foreach ($this->getChecklistImportColumnMap() as $column => $target) {
            $columnByGroupKey[$this->buildChecklistGroupKey($target['scope_type'], $target['sow_type'])] = $column;
        }

        foreach ($packageRows as $package) {
            $groupKey = $this->buildChecklistGroupKey($package['scope_type'] ?? '', $package['sow_type'] ?? '');
            if (!isset($columnByGroupKey[$groupKey])) {
                continue;
            }

            $packageId = (int) ($package['id_doc_package'] ?? 0);
            $groupId = (int) ($package['id_doc_group'] ?? 0);
            $result[$columnByGroupKey[$groupKey]] = $this->deriveChecklistImportStatus(
                (string) ($package['scope_type'] ?? ''),
                (string) ($package['sow_type'] ?? ''),
                $itemsByGroup[$groupId] ?? [],
                $filesByPackageItem[$packageId] ?? []
            );
            if ($groupKey === $this->buildChecklistGroupKey('CLUSTER', 'RFS')) {
                $result['cluster_rfs_nro_flow'] = $this->deriveProjectOpnameFlowImportStatus(
                    $itemsByGroup[$groupId] ?? [],
                    $filesByPackageItem[$packageId] ?? []
                );
            }
        }

        return $result;
    }

    private function deriveChecklistImportStatus($scopeType, $sowType, array $items, array $filesByItem)
    {
        $required = count($items);
        if ($required <= 0) {
            return '';
        }

        $uploaded = 0;
        $approved = 0;
        $astriApproved = 0;
        $hasProjectOpnameNroFlow = false;
        foreach ($items as $item) {
            $itemId = (int) ($item['id_doc_item'] ?? 0);
            $file = $filesByItem[$itemId] ?? [];
            $statusFile = strtoupper(trim((string) ($file['status_file'] ?? '')));
            $astriStatus = strtoupper(trim((string) ($file['astri_status'] ?? 'NY')));
            $hasDocument = !empty($file)
                && (
                    trim((string) ($file['file_path'] ?? '')) !== ''
                    || (int) ($file['is_document_not_required'] ?? 0) === 1
                );

            if ($hasDocument && in_array($statusFile, ['UPLOADED', 'APPROVED'], true)) {
                $uploaded++;
            }
            if ($statusFile === 'APPROVED') {
                $approved++;
            }
            if ($astriStatus === 'APPROVED') {
                $astriApproved++;
            }
            if (
                strtoupper(trim((string) ($item['doc_name'] ?? ''))) === 'PROJECT OPNAME'
                && $this->normalizeProjectOpnameFlowImportStatus($astriStatus) !== ''
            ) {
                $hasProjectOpnameNroFlow = true;
            }
        }

        if ($uploaded <= 0) {
            return 'AREA';
        }
        if ($uploaded < $required || $approved < $required) {
            return 'HO';
        }
        if ($astriApproved >= $required) {
            return 'CLOSED';
        }
        if (
            strtoupper(trim((string) $scopeType)) === 'CLUSTER'
            && strtoupper(trim((string) $sowType)) === 'RFS'
            && $hasProjectOpnameNroFlow
        ) {
            return 'NRO';
        }

        return 'EMR';
    }

    private function deriveProjectOpnameFlowImportStatus(array $items, array $filesByItem)
    {
        foreach ($items as $item) {
            if (strtoupper(trim((string) ($item['doc_name'] ?? ''))) !== 'PROJECT OPNAME') {
                continue;
            }

            $itemId = (int) ($item['id_doc_item'] ?? 0);
            $file = $filesByItem[$itemId] ?? [];
            $label = $this->getProjectOpnameFlowImportLabel((string) ($file['astri_status'] ?? ''));
            if ($label !== '') {
                return $label;
            }
        }

        return '';
    }

    private function buildChecklistGroupKey($scopeType, $sowType)
    {
        return strtoupper(trim((string) $scopeType)) . '|' . strtoupper(trim((string) $sowType));
    }

    private function upsertImportedChecklistFile($packageId, $itemId, $cutoffStatus, $userId, $docName = '', $nroFlowStatus = '')
    {
        $packageId = (int) $packageId;
        $itemId = (int) $itemId;
        $userId = (int) $userId;
        $cutoffStatus = $this->normalizeChecklistImportStatus($cutoffStatus);
        if ($packageId <= 0 || $itemId <= 0 || $cutoffStatus === '' || $cutoffStatus === 'AREA') {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $isHoReviewDone = in_array($cutoffStatus, ['EMR', 'CLOSED', 'NRO'], true);
        $isAstriDone = $cutoffStatus === 'CLOSED';
        $isEmrAstriReview = $cutoffStatus === 'EMR';
        $isNroProjectOpname = $cutoffStatus === 'NRO'
            && strtoupper(trim((string) $docName)) === 'PROJECT OPNAME';
        $statusFile = $isHoReviewDone ? 'APPROVED' : 'UPLOADED';
        $remark = 'Imported cutoff checklist status: ' . $cutoffStatus . '. File fisik tidak tersedia pada data cutoff.';
        $astriStatus = 'NY';
        $astriSubmittedDate = null;
        $astriUpdatedAt = null;
        $astriRemark = null;
        if ($isAstriDone) {
            $astriStatus = 'APPROVED';
            $astriSubmittedDate = $today;
            $astriUpdatedAt = $now;
            $astriRemark = 'Imported cutoff ASTRI closed.';
        } elseif ($isNroProjectOpname) {
            $astriStatus = $this->normalizeProjectOpnameFlowImportStatus($nroFlowStatus);
            if ($astriStatus === '') {
                $astriStatus = 'WAITING WASPANG';
            }
            $astriSubmittedDate = $today;
            $astriUpdatedAt = $now;
            $astriRemark = 'Imported cutoff NRO - Project Opname ' . $this->getProjectOpnameFlowImportLabel($astriStatus) . '.';
        } elseif ($isEmrAstriReview) {
            $astriStatus = 'ON REVIEW';
            $astriSubmittedDate = $today;
            $astriUpdatedAt = $now;
            $astriRemark = 'Imported cutoff ASTRI on review.';
        }

        $payload = [
            'id_doc_package' => $packageId,
            'id_doc_item' => $itemId,
            'file_name' => null,
            'file_path' => null,
            'is_document_not_required' => 1,
            'status_file' => $statusFile,
            'remark' => $remark,
            'uploaded_by' => $userId,
            'uploaded_at' => $now,
            'reviewed_at' => $isHoReviewDone ? $now : null,
            'approved_by' => $isHoReviewDone ? $userId : null,
            'approved_at' => $isHoReviewDone ? $now : null,
            'astri_submitted_date' => $astriSubmittedDate,
            'astri_status' => $astriStatus,
            'astri_status_updated_at' => $astriUpdatedAt,
            'astri_remark' => $astriRemark,
        ];
        if ($this->db->field_exists('submitted_at', 'tb_rfs_myrep_doc_file')) {
            $payload['submitted_at'] = $now;
        }

        $existing = $this->db
            ->from('tb_rfs_myrep_doc_file')
            ->where('id_doc_package', $packageId)
            ->where('id_doc_item', $itemId)
            ->limit(1)
            ->get()
            ->row_array();

        if (!empty($existing['id_doc_file'])) {
            $this->db
                ->where('id_doc_file', (int) $existing['id_doc_file'])
                ->update('tb_rfs_myrep_doc_file', $payload);
            $fileId = (int) $existing['id_doc_file'];
            $actionType = $statusFile === 'APPROVED' ? 'APPROVED' : 'REUPLOADED';
        } else {
            $this->db->insert('tb_rfs_myrep_doc_file', $payload);
            $fileId = (int) $this->db->insert_id();
            $actionType = $statusFile === 'APPROVED' ? 'APPROVED' : 'UPLOADED';
        }

        $this->createImportedChecklistFileLog($fileId, $packageId, $itemId, $actionType, $statusFile, $remark, $userId);
    }

    private function clearImportedChecklistPackage($packageId)
    {
        $packageId = (int) $packageId;
        if ($packageId <= 0 || !$this->db->table_exists('tb_rfs_myrep_doc_file')) {
            return;
        }

        $this->db
            ->where('id_doc_package', $packageId)
            ->group_start()
                ->where('file_path IS NULL', null, false)
                ->or_where('file_path', '')
            ->group_end()
            ->like('remark', 'Imported cutoff checklist status:', 'after')
            ->delete('tb_rfs_myrep_doc_file');
    }

    private function createImportedChecklistFileLog($fileId, $packageId, $itemId, $actionType, $statusAfter, $remark, $userId)
    {
        if ((int) $fileId <= 0 || !$this->db->table_exists('tb_rfs_myrep_doc_file_log')) {
            return;
        }

        $payload = [
            'id_doc_file' => (int) $fileId,
            'id_doc_package' => (int) $packageId,
            'id_doc_item' => (int) $itemId,
            'action_type' => in_array($actionType, ['UPLOADED', 'REUPLOADED', 'REJECTED', 'APPROVED'], true) ? $actionType : 'UPLOADED',
            'status_after' => (string) $statusAfter,
            'file_name' => '[Imported Cutoff]',
            'remark' => (string) $remark,
            'action_by' => (int) $userId,
            'action_at' => date('Y-m-d H:i:s'),
        ];
        if ($this->db->field_exists('submitted_at', 'tb_rfs_myrep_doc_file_log')) {
            $payload['submitted_at'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('tb_rfs_myrep_doc_file_log', $payload);
    }

    private function refreshImportedChecklistPackageStatus($packageId, $userId)
    {
        $package = $this->db->get_where('tb_rfs_myrep_doc_package', [
            'id_doc_package' => (int) $packageId,
        ])->row_array();
        if (!$package) {
            return;
        }

        $required = (int) $this->db
            ->from('md_rfs_myrep_doc_item')
            ->where('id_doc_group', (int) $package['id_doc_group'])
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->count_all_results();

        $uploadedRow = $this->db->query(
            "SELECT COUNT(*) AS total, MAX(uploaded_at) AS latest_uploaded
             FROM tb_rfs_myrep_doc_file
             WHERE id_doc_package = ?
             AND ((file_path IS NOT NULL AND file_path <> '') OR is_document_not_required = 1)
             AND status_file IN ('UPLOADED','APPROVED')",
            [(int) $packageId]
        )->row_array();

        $uploaded = (int) ($uploadedRow['total'] ?? 0);
        $statusPackage = 'NOT STARTED';
        if ($required > 0 && $uploaded > 0) {
            $statusPackage = $uploaded >= $required ? 'DONE' : 'ON PROGRESS';
        }

        $actualSubmit = null;
        if ($required > 0 && $uploaded >= $required && !empty($uploadedRow['latest_uploaded'])) {
            $actualSubmit = substr((string) $uploadedRow['latest_uploaded'], 0, 10);
        }

        $this->db
            ->where('id_doc_package', (int) $packageId)
            ->update('tb_rfs_myrep_doc_package', [
                'status_package' => $statusPackage,
                'actual_submit_doc_date' => $actualSubmit,
                'updated_by' => (int) $userId,
            ]);
    }

    private function syncChecklistActualAtpDate($rfsClusterId, $actualAtpDate, $userId)
    {
        $rfsClusterId = (int) $rfsClusterId;
        $actualAtpDate = $this->normalizeDate($actualAtpDate);
        if ($rfsClusterId <= 0 || $actualAtpDate === null) {
            return;
        }
        if (!$this->db->table_exists('tb_rfs_myrep_doc_package')) {
            return;
        }

        $payload = ['actual_atp_date' => $actualAtpDate];
        if ($this->db->field_exists('updated_by', 'tb_rfs_myrep_doc_package')) {
            $payload['updated_by'] = (int) $userId;
        }

        $this->db
            ->where('cluster_id', $rfsClusterId)
            ->update('tb_rfs_myrep_doc_package', $payload);
    }

    private function buildRfsClaimImportHeaders()
    {
        $headers = [];
        for ($i = 1; $i <= 5; $i++) {
            $headers[] = 'rfs_' . $i . '_date';
            $headers[] = 'rfs_' . $i . '_qty';
        }
        return $headers;
    }

    private function extractRfsClaimsFromRow(array $row)
    {
        $claims = [];
        for ($i = 1; $i <= 5; $i++) {
            $date = $this->normalizeDate((string) ($row['rfs_' . $i . '_date'] ?? ''));
            $qty = (int) $this->normalizeNumber($row['rfs_' . $i . '_qty'] ?? 0);
            if ($date && $qty > 0) {
                $claims[] = [
                    'claim_date' => $date,
                    'claim_qty' => $qty,
                ];
            }
        }
        return $claims;
    }

    private function getCurrentPoHeadersForSnapshot(array $myrepClusterIds)
    {
        $myrepClusterIds = array_values(array_unique(array_filter(array_map('intval', $myrepClusterIds))));
        if (empty($myrepClusterIds) || !$this->db->table_exists('tb_myrep_po_header')) {
            return [];
        }

        $select = '
            id_po_header,
            id_myrep_cluster,
            po_type,
            po_category,
            po_number,
            po_date,
            po_value,
            status_po,
            po_version_label,
            remark_po
        ';
        if ($this->db->field_exists('on_target', 'tb_myrep_po_header')) {
            $select .= ', on_target';
        }

        $rows = $this->db
            ->select($select, false)
            ->from('tb_myrep_po_header')
            ->where_in('id_myrep_cluster', $myrepClusterIds)
            ->order_by('id_myrep_cluster', 'ASC')
            ->order_by('po_type', 'ASC')
            ->order_by('id_po_header', 'DESC')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $poType = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER')));
            if ($clusterId <= 0 || !in_array($poType, ['CLUSTER', 'SUBFEEDER'], true)) {
                continue;
            }
            if (isset($map[$clusterId][$poType])) {
                continue;
            }

            if (!array_key_exists('on_target', $row)) {
                $row['on_target'] = '';
            }
            $map[$clusterId][$poType] = $row;
        }

        return $map;
    }

    private function getCurrentPoTerminRowsForSnapshot(array $poHeaderIds)
    {
        $poHeaderIds = array_values(array_unique(array_filter(array_map('intval', $poHeaderIds))));
        if (empty($poHeaderIds) || !$this->db->table_exists('tb_myrep_po_termin')) {
            return [];
        }

        $this->ensurePoTerminCertificateColumn();
        $select = 'id_po_header, termin_no, termin_value, status_termin, invoice_date';
        if ($this->db->field_exists('sertifikat_invoice_date', 'tb_myrep_po_termin')) {
            $select .= ', sertifikat_invoice_date';
        }

        $rows = $this->db
            ->select($select)
            ->from('tb_myrep_po_termin')
            ->where_in('id_po_header', $poHeaderIds)
            ->where('termin_no >=', 1)
            ->where('termin_no <=', 5)
            ->order_by('id_po_header', 'ASC')
            ->order_by('termin_no', 'ASC')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $headerId = (int) ($row['id_po_header'] ?? 0);
            $terminNo = (int) ($row['termin_no'] ?? 0);
            if ($headerId <= 0 || $terminNo < 1 || $terminNo > 5) {
                continue;
            }

            $map[$headerId][$terminNo] = $row;
        }

        return $map;
    }

    private function appendPoTerminSnapshotColumns(array &$rowMap, $prefix, array $poRow, array $poTerminMap)
    {
        $poHeaderId = (int) ($poRow['id_po_header'] ?? 0);
        $terminRows = $poHeaderId > 0 ? ($poTerminMap[$poHeaderId] ?? []) : [];

        for ($terminNo = 1; $terminNo <= 5; $terminNo++) {
            $planKey = $prefix . '_termin' . $terminNo . '_plan_invoice';
            $submitKey = $prefix . '_termin' . $terminNo . '_submit_invoice';
            $sertifikatKey = $prefix . '_termin' . $terminNo . '_sertifikat_invoice';
            $nilaiKey = $prefix . '_termin' . $terminNo . '_nilai_invoice';

            $rowMap[$planKey] = '';
            $rowMap[$submitKey] = '';
            if ($terminNo >= 2) {
                $rowMap[$sertifikatKey] = '';
            }
            $rowMap[$nilaiKey] = '';

            if (empty($terminRows[$terminNo])) {
                continue;
            }

            $termin = $terminRows[$terminNo];
            $statusTermin = strtoupper(trim((string) ($termin['status_termin'] ?? 'NOT READY')));
            $terminValue = (string) ($termin['termin_value'] ?? '');
            $invoiceDate = (string) ($termin['invoice_date'] ?? '');
            $sertifikatDate = (string) ($termin['sertifikat_invoice_date'] ?? '');

            if (in_array($statusTermin, ['BILLED', 'PAID'], true)) {
                $rowMap[$submitKey] = $invoiceDate;
                $rowMap[$nilaiKey] = $terminValue;
            } else {
                $rowMap[$planKey] = $terminValue;
            }
            if ($terminNo >= 2) {
                $rowMap[$sertifikatKey] = $sertifikatDate;
            }
        }
    }

    private function buildPoTerminImportHeaders()
    {
        $headers = [];
        foreach (['po_cluster', 'po_subfeeder'] as $prefix) {
            for ($i = 1; $i <= 5; $i++) {
                $headers[] = $prefix . '_termin' . $i . '_plan_invoice';
                $headers[] = $prefix . '_termin' . $i . '_submit_invoice';
                if ($i >= 2) {
                    $headers[] = $prefix . '_termin' . $i . '_sertifikat_invoice';
                }
                $headers[] = $prefix . '_termin' . $i . '_nilai_invoice';
            }
        }
        return $headers;
    }

    private function upsertImportedPo($clusterId, array $row, $userId)
    {
        if (!$this->db->table_exists('tb_myrep_po_header') || !$this->db->table_exists('tb_myrep_po_termin')) {
            return;
        }

        $poDefinitions = [
            [
                'type' => 'CLUSTER',
                'category' => (string) ($row['po_cluster_category'] ?? ''),
                'status' => (string) ($row['po_cluster_status'] ?? ''),
                'number' => (string) ($row['po_cluster_number'] ?? ''),
                'date' => (string) ($row['po_cluster_date'] ?? ''),
                'value' => $row['po_cluster_value'] ?? 0,
                'on_target' => $row['po_cluster_on_target'] ?? '',
                'version' => (string) ($row['po_cluster_version_label'] ?? ''),
                'remark' => (string) ($row['po_cluster_remark'] ?? ''),
                'prefix' => 'po_cluster',
            ],
            [
                'type' => 'SUBFEEDER',
                'category' => (string) ($row['po_subfeeder_category'] ?? ''),
                'status' => (string) ($row['po_subfeeder_status'] ?? ''),
                'number' => (string) ($row['po_subfeeder_number'] ?? ''),
                'date' => (string) ($row['po_subfeeder_date'] ?? ''),
                'value' => $row['po_subfeeder_value'] ?? 0,
                'on_target' => $row['po_subfeeder_on_target'] ?? '',
                'version' => (string) ($row['po_subfeeder_version_label'] ?? ''),
                'remark' => (string) ($row['po_subfeeder_remark'] ?? ''),
                'prefix' => 'po_subfeeder',
            ],
        ];

        // Backward compatibility: masih support kolom PO lama jika ada.
        if (trim((string) ($row['po_number'] ?? '')) !== '') {
            $poDefinitions[] = [
                'type' => strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER'))),
                'category' => (string) ($row['po_category'] ?? ''),
                'status' => (string) ($row['status_po'] ?? ''),
                'number' => (string) ($row['po_number'] ?? ''),
                'date' => (string) ($row['po_date'] ?? ''),
                'value' => $row['po_value'] ?? 0,
                'on_target' => $row['po_on_target'] ?? '',
                'version' => (string) ($row['po_version_label'] ?? ''),
                'remark' => (string) ($row['remark_po'] ?? ''),
                'prefix' => 'po_cluster',
            ];
        }

        foreach ($poDefinitions as $poDef) {
            $poNumber = trim((string) $poDef['number']);
            $poDate = $this->normalizeDate((string) $poDef['date']);
            $poValue = (float) $this->normalizeNumber($poDef['value']);
            if ($poNumber === '' || $poDate === null || $poValue <= 0) {
                continue;
            }

            $poType = strtoupper(trim((string) $poDef['type']));
            if (!in_array($poType, ['CLUSTER', 'SUBFEEDER'], true)) {
                $poType = 'CLUSTER';
            }

            $poCategory = strtoupper(trim((string) $poDef['category']));
            if (!in_array($poCategory, ['INITIAL', 'FINAL', 'AMANDMENT'], true)) {
                $poCategory = 'INITIAL';
            }

            $statusPo = strtoupper(trim((string) $poDef['status']));
            if (!in_array($statusPo, ['NOT ISSUED', 'ISSUED', 'PARTIAL PAYMENT', 'FULLY PAID', 'CLOSED'], true)) {
                $statusPo = 'ISSUED';
            }
            $onTarget = $this->normalizeImportBoolean($poDef['on_target'] ?? '');

            $poPayload = [
                'parent_po_header_id' => null,
                'po_type' => $poType,
                'po_category' => $poCategory,
                'po_number' => $poNumber,
                'po_date' => $poDate,
                'po_value' => $poValue,
                'status_po' => $statusPo,
                'po_version_label' => trim((string) ($poDef['version'] ?? '')),
                'remark_po' => trim((string) ($poDef['remark'] ?? '')),
                'created_by' => $userId,
                'updated_by' => $userId,
            ];
            if ($onTarget !== null) {
                $poPayload['on_target'] = $onTarget;
            }

            $poHeaderId = $this->resolveImportedPoHeaderId($clusterId, $poType, $poNumber);
            if ($poHeaderId > 0) {
                $updatePayload = [
                    'parent_po_header_id' => null,
                    'po_type' => $poType,
                    'po_category' => $poCategory,
                    'po_number' => $poNumber,
                    'po_date' => $poDate,
                    'po_value' => $poValue,
                    'status_po' => $statusPo,
                    'po_version_label' => trim((string) ($poDef['version'] ?? '')) ?: null,
                    'remark_po' => trim((string) ($poDef['remark'] ?? '')) ?: null,
                    'updated_by' => (int) $userId,
                ];
                if ($onTarget !== null) {
                    $updatePayload['on_target'] = $onTarget;
                }
                $this->db
                    ->where('id_po_header', (int) $poHeaderId)
                    ->update('tb_myrep_po_header', $this->filterPayloadByTableFields('tb_myrep_po_header', $updatePayload));
            } else {
                $poHeaderId = (int) $this->MPO_MyRep->createPoHeader($clusterId, $poPayload);
            }

            if ($poHeaderId > 0) {
                $this->applyImportedPoTerminDataFromRow($poHeaderId, $row, (string) ($poDef['prefix'] ?? 'po_cluster'));
            }
        }
    }

    private function resolveImportedPoHeaderId($clusterId, $poType, $poNumber)
    {
        $clusterId = (int) $clusterId;
        $poType = strtoupper(trim((string) $poType));
        $poNumber = trim((string) $poNumber);
        if ($clusterId <= 0 || $poType === '' || !$this->db->table_exists('tb_myrep_po_header')) {
            return 0;
        }

        if ($poNumber !== '') {
            $existingByNumber = $this->db
                ->select('id_po_header')
                ->from('tb_myrep_po_header')
                ->where('id_myrep_cluster', $clusterId)
                ->where('po_type', $poType)
                ->where('po_number', $poNumber)
                ->order_by('id_po_header', 'DESC')
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($existingByNumber['id_po_header'])) {
                return (int) $existingByNumber['id_po_header'];
            }
        }

        $existingByType = $this->db
            ->select('id_po_header')
            ->from('tb_myrep_po_header')
            ->where('id_myrep_cluster', $clusterId)
            ->where('po_type', $poType)
            ->order_by('id_po_header', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($existingByType['id_po_header']) ? (int) $existingByType['id_po_header'] : 0;
    }

    private function applyImportedPoTerminDataFromRow($poHeaderId, array $row, $prefix)
    {
        if ($poHeaderId <= 0) {
            return;
        }

        $this->ensurePoTerminCertificateColumn();
        $terminRows = $this->MPO_MyRep->getTerminRowsByPoId((int) $poHeaderId);
        if (empty($terminRows)) {
            return;
        }

        $userId = (int) $this->session->userdata('id_user');
        foreach ($terminRows as $terminRow) {
            $terminNo = (int) ($terminRow['termin_no'] ?? 0);
            if ($terminNo < 1 || $terminNo > 5) {
                continue;
            }

            $planKey = $prefix . '_termin' . $terminNo . '_plan_invoice';
            $submitKey = $prefix . '_termin' . $terminNo . '_submit_invoice';
            $sertifikatKey = $prefix . '_termin' . $terminNo . '_sertifikat_invoice';
            $nilaiKey = $prefix . '_termin' . $terminNo . '_nilai_invoice';

            $planRaw = trim((string) ($row[$planKey] ?? ''));
            $submitRaw = trim((string) ($row[$submitKey] ?? ''));
            $sertifikatRaw = trim((string) ($row[$sertifikatKey] ?? ''));
            $nilaiRaw = trim((string) ($row[$nilaiKey] ?? ''));

            $submitDate = $this->normalizeDate($submitRaw);
            $sertifikatDate = $this->normalizeDate($sertifikatRaw);
            $planInvoice = $planRaw !== '' ? (float) $this->normalizeNumber($planRaw) : 0.0;
            $nilaiInvoice = $nilaiRaw !== '' ? (float) $this->normalizeNumber($nilaiRaw) : 0.0;
            $hasPlanValue = abs($planInvoice) > 0.000001;
            $hasBilledValue = abs($nilaiInvoice) > 0.000001;

            $statusTermin = 'NOT READY';
            if ($hasBilledValue) {
                $statusTermin = 'BILLED';
            } elseif ($hasPlanValue) {
                $statusTermin = 'READY BILLING';
            } elseif ($submitDate !== null) {
                $statusTermin = 'BILLED';
            }

            $this->MPO_MyRep->updateTermin((int) ($terminRow['id_po_termin'] ?? 0), [
                'status_termin' => $statusTermin,
                'invoice_number' => '',
                'invoice_date' => $submitDate,
                'bast_date' => null,
                'payment_date' => null,
                'remark_termin' => $hasPlanValue ? ('Plan Invoice: ' . $planInvoice) : '',
                'updated_by' => $userId,
            ]);

            if ($nilaiRaw !== '' && $this->db->field_exists('termin_value', 'tb_myrep_po_termin')) {
                $this->db
                    ->where('id_po_termin', (int) ($terminRow['id_po_termin'] ?? 0))
                    ->update('tb_myrep_po_termin', [
                        'termin_value' => $nilaiInvoice,
                        'updated_by' => $userId,
                    ]);
            }

            if ($terminNo >= 2 && array_key_exists($sertifikatKey, $row) && $this->db->field_exists('sertifikat_invoice_date', 'tb_myrep_po_termin')) {
                $this->db
                    ->where('id_po_termin', (int) ($terminRow['id_po_termin'] ?? 0))
                    ->update('tb_myrep_po_termin', [
                        'sertifikat_invoice_date' => $sertifikatDate,
                        'updated_by' => $userId,
                    ]);
            }
        }
    }

    private function collectQuickUpdateRow(array $cluster)
    {
        $keys = [
            'status_current',
            'city_name',
            'district_name',
            'village_name',
            'cluster_name',
            'cluster_code',
            'hp_plan',
            'homepass_bak',
            'ba_open_date',
            'bak_date',
            'nomor_ntp',
            'tanggal_ntp',
            'homepass_valsal',
            'valsal_date',
            'remark_valsal',
            'hp_donasi',
            'submission_date',
            'nominal_pengajuan_area',
            'nominal_nego_emr',
            'nominal_release_finance',
            'nominal_per_homepass',
            'bank_name',
            'bank_account_number',
            'recipient_name',
            'recipient_phone',
            'recipient_position',
            'recipient_period',
            'free_wifi_qty',
            'free_wifi_period_month',
            'astri_batch_number',
            'staging_status',
            'released_at',
            'remark_batch_approval',
            'homepass_drm',
            'drm_date',
            'nama_olt',
            'remark_drm',
            'rfs_date',
            'status_rfs',
            'email_atp_date',
            'actual_atp_date',
            'status_atp',
            'remark_general',
            'cluster_cwatp',
            'cluster_fullopm',
            'cluster_rfs',
            'cluster_rfs_nro_flow',
            'subfeeder_cwatp',
            'subfeeder_fullopm',
            'subfeeder_rfs',
        ];
        for ($i = 1; $i <= 5; $i++) {
            $keys[] = 'rfs_' . $i . '_date';
            $keys[] = 'rfs_' . $i . '_qty';
        }

        $row = [];
        foreach ($keys as $key) {
            $row[$key] = trim((string) $this->input->post($key));
        }

        $fallbacks = [
            'status_current' => 'status_current',
            'city_name' => 'city_name',
            'district_name' => 'district_name',
            'village_name' => 'village_name',
            'cluster_name' => 'cluster_name',
            'cluster_code' => 'cluster_code',
            'hp_plan' => 'hp_plan',
            'nomor_ntp' => 'ntp_name',
            'tanggal_ntp' => 'ntp_date',
            'remark_general' => 'remark_general',
        ];
        foreach ($fallbacks as $rowKey => $clusterKey) {
            if ($row[$rowKey] === '') {
                $row[$rowKey] = (string) ($cluster[$clusterKey] ?? '');
            }
        }

        return $row;
    }

    private function validateQuickUpdateRow(array $row)
    {
        $errors = $this->validateCutoffImportRow($row);
        foreach ([
            'ba_open_date',
            'bak_date',
            'tanggal_ntp',
            'valsal_date',
            'submission_date',
            'drm_date',
            'rfs_date',
            'email_atp_date',
            'actual_atp_date',
        ] as $dateKey) {
            if (trim((string) ($row[$dateKey] ?? '')) !== '' && $this->normalizeDate((string) $row[$dateKey]) === null) {
                $errors[] = $dateKey . ' tidak valid';
            }
        }

        if (trim((string) ($row['released_at'] ?? '')) !== '' && $this->normalizeDateTime((string) $row['released_at']) === null) {
            $errors[] = 'released_at tidak valid';
        }

        $statusCurrent = $this->normalizeImportStatus((string) ($row['status_current'] ?? ''));
        $rfsDate = $this->normalizeDate((string) ($row['rfs_date'] ?? ''));
        $rfsClaims = $this->extractRfsClaimsFromRow($row);
        if ($this->quickStageAtLeast($statusCurrent, 'RFS') && $rfsDate === null && empty($rfsClaims)) {
            $errors[] = 'Kalau status_current sudah RFS/ATP/CHECKLIST/DONE, tanggal RFS wajib diisi.';
        }
        if ($this->quickStageAtLeast($statusCurrent, 'RFS') && trim((string) ($row['status_rfs'] ?? '')) === '') {
            $errors[] = 'Kalau status_current sudah RFS/ATP/CHECKLIST/DONE, status_rfs wajib diisi.';
        }
        if ($this->quickStageAtLeast($statusCurrent, 'ATP') && $this->normalizeDate((string) ($row['actual_atp_date'] ?? '')) === null) {
            $errors[] = 'Kalau status_current sudah ATP/CHECKLIST/DONE, actual_atp_date wajib diisi.';
        }

        $statusAtp = strtoupper(trim((string) ($row['status_atp'] ?? '')));
        if ($statusAtp !== '' && !in_array($statusAtp, ['DONE', 'PUNCLIST'], true)) {
            $errors[] = 'status_atp harus DONE atau PUNCLIST';
        }

        return array_values(array_unique($errors));
    }

    private function applyQuickUpdateToCluster(array $cluster, array $row, $userId)
    {
        $clusterId = (int) ($cluster['id_myrep_cluster'] ?? 0);
        if ($clusterId <= 0) {
            return ['status' => false, 'message' => 'Cluster MyRep tidak valid.'];
        }

        $statusCurrent = $this->normalizeImportStatus((string) ($row['status_current'] ?? ''));
        $target = $this->resolveQuickUpdateTarget($cluster, $row);
        if (empty($target)) {
            return ['status' => false, 'message' => 'Target kota tidak ditemukan.'];
        }

        $this->db->trans_start();
        $this->quickUpdateClusterHeader($clusterId, $row, $userId, $target, $statusCurrent);
        $this->quickUpsertBak($clusterId, $row, $userId, $statusCurrent);
        $this->quickUpsertValsal($clusterId, $row, $userId, $statusCurrent);
        $this->quickUpsertBatch($clusterId, $row, $userId, $statusCurrent);
        $this->quickUpsertDrm($clusterId, $row, $userId, $statusCurrent);
        $rfsClusterId = $this->quickUpsertRfsAtp($cluster, $row, $userId, $target, $statusCurrent);
        if ($rfsClusterId > 0) {
            $this->applyImportedChecklistStatuses($rfsClusterId, $row, $userId);
        }
        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['status' => false, 'message' => 'Quick update gagal, transaksi rollback.'];
        }

        return ['status' => true, 'message' => 'Quick update cluster berhasil disimpan.'];
    }

    private function resolveQuickUpdateTarget(array $cluster, array $row)
    {
        $incomingCity = strtoupper(trim((string) ($row['city_name'] ?? '')));
        $currentCity = strtoupper(trim((string) ($cluster['city_name'] ?? '')));
        $currentTargetId = (int) ($cluster['id_target'] ?? 0);
        if ($incomingCity !== '' && $incomingCity === $currentCity && $currentTargetId > 0) {
            return [
                'id_target' => $currentTargetId,
                'regional_name' => $cluster['regional_name'] ?? null,
                'province_name' => $cluster['province_name'] ?? null,
                'city_name' => $cluster['city_name'] ?? null,
                'team_name' => $cluster['team_name'] ?? null,
                'chief' => $cluster['chief'] ?? null,
                'rpm' => $cluster['rpm'] ?? null,
                'sm' => $cluster['sm'] ?? null,
                'spv' => $cluster['spv'] ?? null,
            ];
        }

        return $this->resolveTargetByCity($incomingCity);
    }

    private function quickUpdateClusterHeader($clusterId, array $row, $userId, array $target, $statusCurrent)
    {
        $ntpDate = $this->normalizeDate((string) ($row['tanggal_ntp'] ?? ''));
        $payload = [
            'id_target' => (int) ($target['id_target'] ?? 0),
            'cluster_name' => trim((string) ($row['cluster_name'] ?? '')),
            'cluster_code' => trim((string) ($row['cluster_code'] ?? '')) ?: null,
            'regional_name' => $target['regional_name'] ?? null,
            'province_name' => $target['province_name'] ?? null,
            'city_name' => strtoupper(trim((string) ($row['city_name'] ?? ''))),
            'district_name' => trim((string) ($row['district_name'] ?? '')) ?: null,
            'village_name' => trim((string) ($row['village_name'] ?? '')) ?: null,
            'team_name' => $target['team_name'] ?? null,
            'chief' => $target['chief'] ?? null,
            'rpm' => $target['rpm'] ?? null,
            'sm' => $target['sm'] ?? null,
            'spv' => $target['spv'] ?? null,
            'hp_plan' => max(0, (int) $this->normalizeNumber($row['hp_plan'] ?? 0)),
            'ntp_name' => trim((string) ($row['nomor_ntp'] ?? '')) ?: null,
            'ntp_date' => $ntpDate,
            'ntp_year' => $ntpDate ? (int) date('Y', strtotime($ntpDate)) : null,
            'status_current' => $statusCurrent,
            'remark_general' => trim((string) ($row['remark_general'] ?? '')) ?: null,
            'updated_by' => (int) $userId,
        ];
        $this->db
            ->where('id_myrep_cluster', (int) $clusterId)
            ->update('tb_myrep_cluster', $this->filterPayloadByTableFields('tb_myrep_cluster', $payload));
    }

    private function quickUpsertBak($clusterId, array $row, $userId, $statusCurrent)
    {
        if (!$this->db->table_exists('tb_myrep_bak')) {
            return;
        }

        $needsStage = $this->quickStageAtLeast($statusCurrent, 'BAK') || $this->quickHasAnyPayload($row, ['ba_open_date', 'bak_date', 'homepass_bak']);
        if (!$needsStage) {
            return;
        }

        $homepassBak = (int) $this->normalizeNumber($row['homepass_bak'] ?? 0);
        if ($homepassBak <= 0) {
            $homepassBak = (int) $this->normalizeNumber($row['hp_plan'] ?? 0);
        }
        $payload = [
            'ba_open_date' => $this->normalizeDate((string) ($row['ba_open_date'] ?? '')) ?: date('Y-m-d'),
            'bak_date' => $this->normalizeDate((string) ($row['bak_date'] ?? '')) ?: date('Y-m-d'),
            'homepass_bak' => max(0, $homepassBak),
            'status_bak' => 'DONE',
            'remark_bak' => trim((string) ($row['remark_general'] ?? '')) ?: null,
            'updated_by' => (int) $userId,
        ];
        $this->upsertSingleByWhere('tb_myrep_bak', ['id_myrep_cluster' => (int) $clusterId], $payload, [
            'id_myrep_cluster' => (int) $clusterId,
            'created_by' => (int) $userId,
        ]);
    }

    private function quickUpsertValsal($clusterId, array $row, $userId, $statusCurrent)
    {
        if (!$this->db->table_exists('tb_myrep_valsal')) {
            return;
        }

        $needsStage = $this->quickStageAtLeast($statusCurrent, 'VALSAL') || $this->quickHasAnyPayload($row, ['homepass_valsal', 'valsal_date', 'remark_valsal']);
        if (!$needsStage) {
            return;
        }

        $homepassValsal = (int) $this->normalizeNumber($row['homepass_valsal'] ?? 0);
        if ($homepassValsal <= 0) {
            $homepassValsal = (int) $this->normalizeNumber($row['homepass_bak'] ?? 0);
        }
        if ($homepassValsal <= 0) {
            $homepassValsal = (int) $this->normalizeNumber($row['hp_plan'] ?? 0);
        }
        $payload = [
            'valsal_date' => $this->normalizeDate((string) ($row['valsal_date'] ?? '')) ?: date('Y-m-d'),
            'homepass_valsal' => max(0, $homepassValsal),
            'status_valsal' => 'DONE',
            'remark_valsal' => trim((string) ($row['remark_valsal'] ?? '')) ?: (trim((string) ($row['remark_general'] ?? '')) ?: null),
            'updated_by' => (int) $userId,
        ];
        $this->upsertSingleByWhere('tb_myrep_valsal', ['id_myrep_cluster' => (int) $clusterId], $payload, [
            'id_myrep_cluster' => (int) $clusterId,
            'created_by' => (int) $userId,
        ]);
    }

    private function quickUpsertBatch($clusterId, array $row, $userId, $statusCurrent)
    {
        if (!$this->db->table_exists('tb_myrep_batch_approval')) {
            return;
        }

        $needsStage = $this->quickStageAtLeast($statusCurrent, 'WAITING HO')
            || $this->quickHasAnyPayload($row, ['hp_donasi', 'submission_date', 'nominal_pengajuan_area', 'staging_status', 'released_at']);
        if (!$needsStage) {
            return;
        }

        $stagingStatus = strtoupper(trim((string) ($row['staging_status'] ?? '')));
        $allowedStaging = ['DRAFT', 'WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED', 'REJECTED'];
        if (!in_array($stagingStatus, $allowedStaging, true)) {
            $stagingStatus = 'WAITING HO';
            if ($this->quickStageAtLeast($statusCurrent, 'WAITING MYREP')) {
                $stagingStatus = 'WAITING MYREP';
            }
            if ($this->quickStageAtLeast($statusCurrent, 'WAITING FINANCE')) {
                $stagingStatus = 'WAITING FINANCE';
            }
            if ($this->quickStageAtLeast($statusCurrent, 'RELEASED')) {
                $stagingStatus = 'RELEASED';
            }
        }

        $hpDonasi = (int) $this->normalizeNumber($row['hp_donasi'] ?? 0);
        if ($hpDonasi <= 0) {
            $hpDonasi = (int) $this->normalizeNumber($row['homepass_valsal'] ?? 0);
        }
        if ($hpDonasi <= 0) {
            $hpDonasi = (int) $this->normalizeNumber($row['homepass_bak'] ?? 0);
        }
        $nominalPengajuan = (float) $this->normalizeNumber($row['nominal_pengajuan_area'] ?? 0);
        $nominalPerHp = $this->normalizeNullableNumber($row['nominal_per_homepass'] ?? null);
        if ($nominalPerHp === null && $hpDonasi > 0 && $nominalPengajuan > 0) {
            $nominalPerHp = round($nominalPengajuan / $hpDonasi, 2);
        }
        if ($nominalPerHp === null) {
            $nominalPerHp = 0;
        }

        $now = date('Y-m-d H:i:s');
        $payload = [
            'submission_date' => $this->normalizeDate((string) ($row['submission_date'] ?? '')) ?: date('Y-m-d'),
            'hp_donasi' => max(0, $hpDonasi),
            'nominal_pengajuan_area' => $nominalPengajuan,
            'nominal_nego_emr' => $this->normalizeNullableNumber($row['nominal_nego_emr'] ?? null),
            'nominal_release_finance' => $this->normalizeNullableNumber($row['nominal_release_finance'] ?? null),
            'nominal_per_homepass' => $nominalPerHp,
            'bank_name' => trim((string) ($row['bank_name'] ?? '')) ?: '-',
            'bank_account_number' => trim((string) ($row['bank_account_number'] ?? '')) ?: '-',
            'recipient_name' => trim((string) ($row['recipient_name'] ?? '')) ?: 'QUICK UPDATE',
            'recipient_phone' => trim((string) ($row['recipient_phone'] ?? '')) ?: null,
            'recipient_position' => trim((string) ($row['recipient_position'] ?? '')) ?: null,
            'recipient_period' => trim((string) ($row['recipient_period'] ?? '')) ?: null,
            'free_wifi_qty' => $this->normalizeNullableInt($row['free_wifi_qty'] ?? null),
            'free_wifi_period_month' => $this->normalizeNullableInt($row['free_wifi_period_month'] ?? null),
            'astri_batch_number' => trim((string) ($row['astri_batch_number'] ?? '')) ?: null,
            'staging_status' => $stagingStatus,
            'submitted_to_ho_at' => in_array($stagingStatus, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED'], true) ? $now : null,
            'submitted_to_astri_at' => in_array($stagingStatus, ['WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED'], true) ? $now : null,
            'submitted_to_finance_at' => in_array($stagingStatus, ['WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED'], true) ? $now : null,
            'released_at' => $this->normalizeDateTime((string) ($row['released_at'] ?? '')),
            'remark_batch_approval' => trim((string) ($row['remark_batch_approval'] ?? '')) ?: (trim((string) ($row['remark_general'] ?? '')) ?: null),
            'updated_by' => (int) $userId,
        ];
        $this->upsertSingleByWhere('tb_myrep_batch_approval', ['id_myrep_cluster' => (int) $clusterId], $payload, [
            'id_myrep_cluster' => (int) $clusterId,
            'created_by' => (int) $userId,
        ]);
    }

    private function quickUpsertDrm($clusterId, array $row, $userId, $statusCurrent)
    {
        if (!$this->db->table_exists('tb_myrep_drm')) {
            return;
        }

        $needsStage = $this->quickStageAtLeast($statusCurrent, 'DRM') || $this->quickHasAnyPayload($row, ['homepass_drm', 'drm_date', 'nama_olt', 'remark_drm']);
        if (!$needsStage) {
            return;
        }

        $hpDrm = (int) $this->normalizeNumber($row['homepass_drm'] ?? 0);
        if ($hpDrm <= 0) {
            $hpDrm = (int) $this->normalizeNumber($row['hp_donasi'] ?? 0);
        }
        if ($hpDrm <= 0) {
            $hpDrm = (int) $this->normalizeNumber($row['homepass_valsal'] ?? 0);
        }
        if ($hpDrm <= 0) {
            $hpDrm = (int) $this->normalizeNumber($row['hp_plan'] ?? 0);
        }
        $payload = [
            'drm_date' => $this->normalizeDate((string) ($row['drm_date'] ?? '')) ?: date('Y-m-d'),
            'homepass_drm' => max(0, $hpDrm),
            'nama_olt' => trim((string) ($row['nama_olt'] ?? '')) ?: null,
            'status_drm' => 'COMPLETE',
            'remark_drm' => trim((string) ($row['remark_drm'] ?? '')) ?: (trim((string) ($row['remark_general'] ?? '')) ?: null),
            'updated_by' => (int) $userId,
        ];
        $this->upsertSingleByWhere('tb_myrep_drm', ['id_myrep_cluster' => (int) $clusterId], $payload, [
            'id_myrep_cluster' => (int) $clusterId,
            'created_by' => (int) $userId,
        ]);
    }

    private function quickUpsertRfsAtp(array $cluster, array $row, $userId, array $target, $statusCurrent)
    {
        if (!$this->db->table_exists('tb_rfs_myrep_cluster')) {
            return 0;
        }

        $needsStage = $this->quickStageAtLeast($statusCurrent, 'RFS')
            || $this->quickHasAnyPayload($row, ['rfs_date', 'status_rfs', 'email_atp_date', 'actual_atp_date', 'status_atp'])
            || !empty($this->extractRfsClaimsFromRow($row))
            || $this->hasChecklistImportPayload($row);
        if (!$needsStage) {
            return (int) ($cluster['rfs_cluster_id'] ?? 0);
        }

        $clusterId = (int) ($cluster['id_myrep_cluster'] ?? 0);
        $rfsClusterId = (int) ($cluster['rfs_cluster_id'] ?? 0);
        $rfsDate = $this->normalizeDate((string) ($row['rfs_date'] ?? ''));
        $emailAtpDate = $this->normalizeDate((string) ($row['email_atp_date'] ?? ''));
        $actualAtpDate = $this->normalizeDate((string) ($row['actual_atp_date'] ?? ''));
        $homepass = (int) $this->normalizeNumber($row['homepass_drm'] ?? 0);
        if ($homepass <= 0) {
            $homepass = (int) $this->normalizeNumber($row['hp_plan'] ?? 0);
        }
        $homepass = max(0, $homepass);

        $rfsClaims = $this->extractRfsClaimsFromRow($row);
        if (empty($rfsClaims) && $rfsDate !== null && $homepass > 0) {
            $rfsClaims[] = ['claim_date' => $rfsDate, 'claim_qty' => $homepass];
        }
        if ($rfsDate === null && !empty($rfsClaims)) {
            $rfsDate = (string) ($rfsClaims[0]['claim_date'] ?? '');
        }

        $totalClaimQty = 0;
        foreach ($rfsClaims as $claim) {
            $totalClaimQty += (int) ($claim['claim_qty'] ?? 0);
        }
        $homepassRfs = $totalClaimQty > 0 ? $totalClaimQty : $homepass;
        $statusRfs = $this->normalizeQuickRfsStatus((string) ($row['status_rfs'] ?? ''));
        if ($statusRfs === '') {
            $statusRfs = ($homepassRfs > 0 && $totalClaimQty >= $homepassRfs) ? 'FULL RFS' : 'NY RFS';
        }

        $statusAtp = strtoupper(trim((string) ($row['status_atp'] ?? '')));
        if (!in_array($statusAtp, ['DONE', 'PUNCLIST'], true)) {
            $statusAtp = $this->quickStageAtLeast($statusCurrent, 'ATP') ? 'DONE' : null;
        }

        $payload = [
            'id_target' => (int) ($target['id_target'] ?? 0),
            'cluster_name' => trim((string) ($row['cluster_name'] ?? $cluster['cluster_name'] ?? '')),
            'homepass' => max(0, $homepassRfs),
            'status_rfs' => $statusRfs,
            'email_atp_date' => $emailAtpDate,
            'status_atp' => $statusAtp,
        ];

        if ($rfsClusterId > 0) {
            $this->db
                ->where('id_cluster', $rfsClusterId)
                ->update('tb_rfs_myrep_cluster', $this->filterPayloadByTableFields('tb_rfs_myrep_cluster', $payload));
        } else {
            $this->db->insert('tb_rfs_myrep_cluster', $this->filterPayloadByTableFields('tb_rfs_myrep_cluster', array_merge($payload, [
                'created_by' => (int) $userId,
            ])));
            $rfsClusterId = (int) $this->db->insert_id();
            if ($rfsClusterId > 0) {
                $this->db
                    ->where('id_myrep_cluster', $clusterId)
                    ->update('tb_myrep_cluster', $this->filterPayloadByTableFields('tb_myrep_cluster', [
                        'rfs_cluster_id' => $rfsClusterId,
                        'updated_by' => (int) $userId,
                    ]));
            }
        }

        if ($rfsClusterId > 0 && isset($this->MChecklist_Dokument_MyRep)) {
            $this->MChecklist_Dokument_MyRep->ensureClusterPackages($rfsClusterId, $rfsDate);
            $packagePayload = ['updated_by' => (int) $userId];
            if ($rfsDate !== null) {
                $packagePayload['tanggal_rfs'] = $rfsDate;
            }
            if ($actualAtpDate !== null) {
                $packagePayload['actual_atp_date'] = $actualAtpDate;
            }
            if (count($packagePayload) > 1 && $this->db->table_exists('tb_rfs_myrep_doc_package')) {
                $this->db
                    ->where('cluster_id', $rfsClusterId)
                    ->update('tb_rfs_myrep_doc_package', $this->filterPayloadByTableFields('tb_rfs_myrep_doc_package', $packagePayload));
            }
            $this->upsertQuickRfsClaims($rfsClusterId, $rfsClaims, $statusRfs, $userId);
            $this->syncChecklistActualAtpDate($rfsClusterId, $actualAtpDate, $userId);
        }

        return $rfsClusterId;
    }

    private function upsertQuickRfsClaims($rfsClusterId, array $claims, $statusRfs, $userId)
    {
        if ((int) $rfsClusterId <= 0 || empty($claims) || !$this->db->table_exists('tb_rfs_myrep_claim')) {
            return;
        }

        foreach ($claims as $claim) {
            $claimDate = $this->normalizeDate((string) ($claim['claim_date'] ?? ''));
            $claimQty = (int) ($claim['claim_qty'] ?? 0);
            if ($claimDate === null || $claimQty <= 0) {
                continue;
            }

            $payload = [
                'claim_date' => $claimDate,
                'claim_qty' => $claimQty,
                'year_num' => (int) date('Y', strtotime($claimDate)),
                'month_num' => (int) date('n', strtotime($claimDate)),
                'claim_year' => (int) date('Y', strtotime($claimDate)),
                'claim_month' => (int) date('n', strtotime($claimDate)),
                'status_rfs' => $statusRfs,
                'status_claim' => 'APPROVED',
                'approval_status' => 'APPROVED',
                'rpm_approval_status' => 'APPROVED',
                'rpm_approval_note' => 'Auto approve from quick update',
                'remark' => 'Auto claim from quick update',
            ];
            $existing = $this->db
                ->from('tb_rfs_myrep_claim')
                ->where('cluster_id', (int) $rfsClusterId)
                ->where('claim_date', $claimDate)
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($existing['id_claim'])) {
                $this->db
                    ->where('id_claim', (int) $existing['id_claim'])
                    ->update('tb_rfs_myrep_claim', $this->filterPayloadByTableFields('tb_rfs_myrep_claim', $payload));
            } else {
                $payload['cluster_id'] = (int) $rfsClusterId;
                $payload['created_by'] = (int) $userId;
                $this->db->insert('tb_rfs_myrep_claim', $this->filterPayloadByTableFields('tb_rfs_myrep_claim', $payload));
            }
        }
    }

    private function quickStageAtLeast($statusCurrent, $stage)
    {
        $order = [
            'DRAFT',
            'BA OPEN',
            'BAK',
            'VALSAL',
            'WAITING HO',
            'WAITING MYREP',
            'WAITING FINANCE',
            'RELEASED',
            'DONE BATCH APPROVAL',
            'DRM',
            'RFS',
            'ATP',
            'CHECKLIST DOKUMENT',
            'DONE',
        ];
        $statusCurrent = $this->normalizeImportStatus($statusCurrent);
        $stage = strtoupper(trim((string) $stage));
        $currentIndex = array_search($statusCurrent, $order, true);
        $stageIndex = array_search($stage, $order, true);
        if ($currentIndex === false || $stageIndex === false) {
            return false;
        }
        return $currentIndex >= $stageIndex;
    }

    private function quickHasAnyPayload(array $row, array $keys)
    {
        foreach ($keys as $key) {
            if (trim((string) ($row[$key] ?? '')) !== '') {
                return true;
            }
        }
        return false;
    }

    private function normalizeQuickRfsStatus($status)
    {
        $status = strtoupper(trim((string) $status));
        if (in_array($status, ['FULL', 'FULL RFS'], true)) {
            return 'FULL RFS';
        }
        if (in_array($status, ['PARTIAL', 'PARTIAL RFS'], true)) {
            return 'PARTIAL';
        }
        if ($status === 'NY RFS') {
            return 'NY RFS';
        }
        return '';
    }

    private function resolveQuickRfsStatus($status, array $claims, array $cluster)
    {
        $status = $this->normalizeQuickRfsStatus($status);
        for ($i = count($claims) - 1; $i >= 0; $i--) {
            $claimStatus = $this->normalizeQuickRfsStatus((string) ($claims[$i]['status_rfs'] ?? ''));
            if ($claimStatus !== '' && $claimStatus !== 'NY RFS') {
                return $claimStatus;
            }
        }

        if ($status !== '' && $status !== 'NY RFS') {
            return $status;
        }

        $totalClaimQty = 0;
        foreach ($claims as $claim) {
            $totalClaimQty += (int) $this->normalizeNumber($claim['claim_qty'] ?? 0);
        }
        if ($totalClaimQty <= 0) {
            return $status;
        }

        $homepass = (int) $this->normalizeNumber($cluster['homepass_drm'] ?? 0);
        if ($homepass <= 0) {
            $homepass = (int) $this->normalizeNumber($cluster['hp_plan'] ?? 0);
        }

        if ($homepass > 0) {
            return $totalClaimQty >= $homepass ? 'FULL RFS' : 'PARTIAL';
        }

        return 'PARTIAL';
    }

    private function formatQuickIntegerValue($value)
    {
        if (trim((string) $value) === '') {
            return '';
        }

        $number = (float) $this->normalizeNumber($value);
        if (abs($number - round($number)) < 0.000001) {
            return (string) (int) round($number);
        }
        return rtrim(rtrim(number_format($number, 6, '.', ''), '0'), '.');
    }

    private function formatQuickTextNumber($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^[+-]?(?:\d+\.?\d*|\.\d+)e[+-]?\d+$/i', $value)) {
            return number_format((float) $value, 0, '.', '');
        }

        return $value;
    }

    private function upsertSingleByWhere($table, array $where, array $payload, array $insertOnly = [])
    {
        if (!$this->db->table_exists($table)) {
            return;
        }

        $existing = $this->db
            ->from($table)
            ->where($where)
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($existing)) {
            $this->db
                ->where($where)
                ->update($table, $this->filterPayloadByTableFields($table, $payload));
            return;
        }

        $this->db->insert($table, $this->filterPayloadByTableFields($table, array_merge($insertOnly, $payload)));
    }

    private function filterPayloadByTableFields($table, array $payload)
    {
        if (!$this->db->table_exists($table)) {
            return $payload;
        }

        $filtered = [];
        foreach ($payload as $field => $value) {
            if ($this->db->field_exists($field, $table)) {
                $filtered[$field] = $value;
            }
        }
        return $filtered;
    }

    private function ensurePoTerminCertificateColumn()
    {
        if (!$this->db->table_exists('tb_myrep_po_termin')) {
            return;
        }
        if ($this->db->field_exists('sertifikat_invoice_date', 'tb_myrep_po_termin')) {
            return;
        }

        try {
            $this->db->query('ALTER TABLE `tb_myrep_po_termin` ADD COLUMN `sertifikat_invoice_date` DATE NULL AFTER `invoice_date`');
        } catch (Throwable $e) {
            log_message('error', 'Failed ensuring tb_myrep_po_termin.sertifikat_invoice_date: ' . $e->getMessage());
        }
    }

    private function validateCutoffImportRow(array $row)
    {
        $errors = [];
        if (trim((string) ($row['cluster_name'] ?? '')) === '') {
            $errors[] = 'cluster_name wajib diisi';
        }
        if (trim((string) ($row['city_name'] ?? '')) === '') {
            $errors[] = 'city_name wajib diisi';
        }
        $statusCurrentRaw = trim((string) ($row['status_current'] ?? ''));
        if ($statusCurrentRaw !== '') {
            $statusRawUpper = strtoupper($statusCurrentRaw);
            $statusCurrent = $this->normalizeImportStatus($statusCurrentRaw);
            if ($statusRawUpper !== 'IMPLEMENTASI' && !in_array($statusRawUpper, $this->importAllowedStatuses, true)) {
                $errors[] = 'status_current tidak valid';
            }
        }
        foreach ($this->getChecklistImportColumnMap() as $column => $target) {
            $rawChecklistStatus = trim((string) ($row[$column] ?? ''));
            $normalizedChecklistStatus = $this->normalizeChecklistImportStatus($rawChecklistStatus);
            if ($rawChecklistStatus !== '' && $normalizedChecklistStatus === '') {
                $errors[] = $column . ' harus AREA, HO, EMR, CLOSED, atau NRO khusus cluster_rfs';
            }
            if ($normalizedChecklistStatus === 'NRO' && $column !== 'cluster_rfs') {
                $errors[] = 'NRO hanya boleh diisi pada cluster_rfs';
            }
        }
        foreach ($this->getChecklistNroFlowImportColumnMap() as $column => $target) {
            $rawFlowStatus = trim((string) ($row[$column] ?? ''));
            if ($rawFlowStatus === '') {
                continue;
            }

            $normalizedFlowStatus = $this->normalizeProjectOpnameFlowImportStatus($rawFlowStatus);
            if ($normalizedFlowStatus === '') {
                $errors[] = $column . ' harus WASPANG, PLANNING, TEAMLEADER/TL, atau LOGISTIK';
            }

            $statusColumn = (string) ($target['status_column'] ?? '');
            $rawChecklistStatus = trim((string) ($row[$statusColumn] ?? ''));
            $normalizedChecklistStatus = $this->normalizeChecklistImportStatus($rawChecklistStatus);
            if ($normalizedChecklistStatus !== 'NRO') {
                $errors[] = $column . ' hanya dipakai saat ' . $statusColumn . ' = NRO';
            }
        }
        foreach (['po_cluster_on_target', 'po_subfeeder_on_target', 'po_on_target'] as $booleanColumn) {
            if (!$this->isValidImportBoolean($row[$booleanColumn] ?? '')) {
                $errors[] = $booleanColumn . ' harus 1/0, TRUE/FALSE, YES/NO, atau ON/OFF';
            }
        }
        foreach (['po_cluster', 'po_subfeeder'] as $prefix) {
            for ($terminNo = 2; $terminNo <= 5; $terminNo++) {
                $sertifikatKey = $prefix . '_termin' . $terminNo . '_sertifikat_invoice';
                if (trim((string) ($row[$sertifikatKey] ?? '')) !== '' && $this->normalizeDate((string) $row[$sertifikatKey]) === null) {
                    $errors[] = $sertifikatKey . ' tidak valid';
                }
            }
        }
        if ($this->resolveTargetByCity((string) ($row['city_name'] ?? '')) === []) {
            $errors[] = 'target kota tidak ditemukan di tb_rfs_myrep_monthly_target';
        }
        return array_values(array_unique($errors));
    }

    private function resolveTargetByCity($cityName)
    {
        $cityName = strtoupper(trim((string) $cityName));
        if ($cityName === '') {
            return [];
        }

        return $this->db
            ->from('tb_rfs_myrep_monthly_target')
            ->where('UPPER(city_name)', $cityName)
            ->order_by('year_num', 'DESC')
            ->order_by('month_num', 'DESC')
            ->limit(1)
            ->get()
            ->row_array() ?: [];
    }

    private function normalizeImportStatus($status)
    {
        $status = strtoupper(trim((string) $status));
        if ($status === 'IMPLEMENTASI') {
            return 'DONE';
        }
        return in_array($status, $this->importAllowedStatuses, true) ? $status : 'DRAFT';
    }

    private function resolveImportStatusCurrent(array $row)
    {
        $statusAtpRaw = strtoupper(trim((string) ($row['status_atp'] ?? '')));
        if ($statusAtpRaw === 'DONE') {
            $statusCurrentRaw = strtoupper(trim((string) ($row['status_current'] ?? '')));
            // DONE dipakai hanya jika cluster sudah full closed.
            if ($statusCurrentRaw !== 'DONE') {
                return 'CHECKLIST DOKUMENT';
            }
        }

        $statusRaw = trim((string) ($row['status_current'] ?? ''));
        if ($statusRaw !== '') {
            return $this->normalizeImportStatus($statusRaw);
        }

        $stagingStatus = strtoupper(trim((string) ($row['staging_status'] ?? '')));
        if ($stagingStatus !== '') {
            if ($stagingStatus === 'WAITING MYREP') {
                return 'WAITING MYREP';
            }
            if ($stagingStatus === 'WAITING FINANCE') {
                return 'WAITING FINANCE';
            }
            if ($stagingStatus === 'RELEASED' || $stagingStatus === 'DONE' || $stagingStatus === 'COMPLETED') {
                return 'RELEASED';
            }
            if ($stagingStatus === 'WAITING HO') {
                return 'WAITING HO';
            }
        }

        $hasValsal = (int) $this->normalizeNumber($row['homepass_valsal'] ?? 0) > 0
            || $this->normalizeDate((string) ($row['valsal_date'] ?? '')) !== null
            || trim((string) ($row['remark_valsal'] ?? '')) !== '';
        if ($hasValsal) {
            return 'VALSAL';
        }

        $hasBatch = (int) $this->normalizeNumber($row['hp_donasi'] ?? 0) > 0
            || $this->normalizeDate((string) ($row['submission_date'] ?? '')) !== null
            || $this->normalizeDateTime((string) ($row['released_at'] ?? '')) !== null;
        if ($hasBatch) {
            return 'WAITING HO';
        }

        $hasDrm = (int) $this->normalizeNumber($row['homepass_drm'] ?? 0) > 0
            || $this->normalizeDate((string) ($row['drm_date'] ?? '')) !== null;
        if ($hasDrm) {
            return 'DRM';
        }

        if ($this->hasChecklistImportPayload($row)) {
            return 'CHECKLIST DOKUMENT';
        }

        $hasRfsAtp = $this->normalizeDate((string) ($row['rfs_date'] ?? $row['tanggal_rfs'] ?? '')) !== null
            || $this->normalizeDate((string) ($row['email_atp_date'] ?? '')) !== null
            || $this->normalizeDate((string) ($row['actual_atp_date'] ?? '')) !== null
            || trim((string) ($row['status_atp'] ?? '')) !== ''
            || !empty($this->extractRfsClaimsFromRow($row));
        if ($hasRfsAtp) {
            return 'RFS';
        }

        return 'BAK';
    }

    private function normalizeHeaderName($header)
    {
        $header = strtolower(trim((string) $header));
        $header = str_replace([' ', '-', '.'], '_', $header);
        return preg_replace('/[^a-z0-9_]/', '', $header);
    }

    private function normalizeNumber($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/\s+/', '', $value);
        $dotPos = strrpos($value, '.');
        $commaPos = strrpos($value, ',');
        if ($dotPos !== false && $commaPos !== false) {
            $value = $dotPos > $commaPos
                ? str_replace(',', '', $value)
                : str_replace(',', '.', str_replace('.', '', $value));
        } elseif ($commaPos !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif ($dotPos !== false && preg_match('/^-?\d{1,3}(?:\.\d{3})+$/', $value)) {
            $value = str_replace('.', '', $value);
        }

        return is_numeric($value) ? (float) $value : 0;
    }

    private function normalizeImportBoolean($value)
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '') {
            return null;
        }

        if (in_array($value, ['1', 'Y', 'YES', 'TRUE', 'ON', 'TARGET'], true)) {
            return 1;
        }

        if (in_array($value, ['0', 'N', 'NO', 'FALSE', 'OFF', 'NON TARGET', 'NOT TARGET'], true)) {
            return 0;
        }

        return null;
    }

    private function isValidImportBoolean($value)
    {
        return trim((string) $value) === '' || $this->normalizeImportBoolean($value) !== null;
    }

    private function formatImportBoolean($value)
    {
        if ($value === '' || $value === null) {
            return '';
        }

        return (int) $value === 1 ? '1' : '0';
    }

    private function normalizeNullableNumber($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        return $this->normalizeNumber($value);
    }

    private function normalizeNullableInt($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        return (int) $this->normalizeNumber($value);
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }
        return date('Y-m-d', $timestamp);
    }

    private function normalizeDateTime($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    private function jsonResponse($status, $message, $extra = [])
    {
        $payload = array_merge(['status' => (bool) $status, 'message' => (string) $message], $extra);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
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
        if (!$handle) {
            return $rows;
        }

        $delimiter = ';';
        $firstLine = fgets($handle);
        rewind($handle);
        if ($firstLine !== false && substr_count($firstLine, ',') > substr_count($firstLine, ';')) {
            $delimiter = ',';
        }

        $rowNumber = 1;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row = [];
            foreach ($data as $index => $value) {
                $column = chr(65 + $index);
                $row[$column] = trim((string) $value);
            }
            $rows[$rowNumber] = $row;
            $rowNumber++;
        }

        fclose($handle);
        return $rows;
    }
}
