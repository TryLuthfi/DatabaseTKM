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
        $data['clusterRows'] = $data['isReady']
            ? $this->MMyRepublik_Project->getClusterRows($selectedCity, $selectedStatus, $metricMode)
            : [];
        $data['clusterStageSummaryRows'] = $this->buildClusterStageSummaryRows($data['clusterRows']);
        $data['overview'] = $this->MMyRepublik_Project->getOverview($data['clusterRows']);
        $data['statusCards'] = $this->MMyRepublik_Project->getStatusCards($data['clusterRows'], $metricMode);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('MyRepublik_Project/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
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

        $deletedCount = (int) $this->MMyRep_Cleanup->deleteAllClusters();
        $this->session->set_flashdata(
            $deletedCount > 0 ? 'success' : 'error',
            $deletedCount > 0
                ? ('Berhasil menghapus ' . $deletedCount . ' cluster MyRep. Flow dari BAK sampai Checklist Dokument ikut terhapus.')
                : 'Tidak ada data cluster MyRep yang dihapus.'
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
        $inserted = 0;
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

                $saved = $this->saveOneImportedCluster($row, $userId);
                if ($saved) {
                    $inserted++;
                } else {
                    $skipped++;
                    $errorDetails[] = [
                        'row_number' => $index + 1,
                        'cluster_name' => (string) ($row['cluster_name'] ?? ''),
                        'message' => 'Dilewati (kemungkinan data sudah ada / tidak valid untuk insert).',
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

        if ($inserted <= 0) {
            $this->jsonResponse(false, 'Tidak ada data yang berhasil disimpan.', [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'error_rows' => $errorDetails,
            ]);
            return;
        }

        $this->jsonResponse(true, $inserted . ' cluster berhasil diimport. ' . $skipped . ' baris dilewati.', [
            'inserted' => $inserted,
            'skipped' => $skipped,
            'error_rows' => $errorDetails,
        ]);
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
            'po_cluster_category',
            'po_cluster_status',
            'po_cluster_number',
            'po_cluster_date',
            'po_cluster_value',
            'po_cluster_version_label',
            'po_cluster_remark',
            'po_subfeeder_category',
            'po_subfeeder_status',
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
                'po_cluster_termin2_plan_invoice' => '18250000', 'po_cluster_termin2_submit_invoice' => '', 'po_cluster_termin2_nilai_invoice' => '0',
                'po_cluster_termin3_plan_invoice' => '0', 'po_cluster_termin3_submit_invoice' => '2026-06-24', 'po_cluster_termin3_nilai_invoice' => '-1095000',
                'po_cluster_termin4_plan_invoice' => '21900000', 'po_cluster_termin4_submit_invoice' => '', 'po_cluster_termin4_nilai_invoice' => '0',
                'po_cluster_termin5_plan_invoice' => '7300000', 'po_cluster_termin5_submit_invoice' => '', 'po_cluster_termin5_nilai_invoice' => '0',
                'po_subfeeder_termin1_plan_invoice' => '0', 'po_subfeeder_termin1_submit_invoice' => '2026-05-25', 'po_subfeeder_termin1_nilai_invoice' => '1700000',
                'po_subfeeder_termin2_plan_invoice' => '2125000', 'po_subfeeder_termin2_submit_invoice' => '', 'po_subfeeder_termin2_nilai_invoice' => '0',
                'po_subfeeder_termin3_plan_invoice' => '1275000', 'po_subfeeder_termin3_submit_invoice' => '', 'po_subfeeder_termin3_nilai_invoice' => '0',
                'po_subfeeder_termin4_plan_invoice' => '0', 'po_subfeeder_termin4_submit_invoice' => '2026-06-30', 'po_subfeeder_termin4_nilai_invoice' => '2550000',
                'po_subfeeder_termin5_plan_invoice' => '850000', 'po_subfeeder_termin5_submit_invoice' => '', 'po_subfeeder_termin5_nilai_invoice' => '0',
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
                'po_cluster_termin2_plan_invoice' => '18250000', 'po_cluster_termin2_submit_invoice' => '', 'po_cluster_termin2_nilai_invoice' => '0',
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
                'po_subfeeder_termin2_plan_invoice' => '2262500', 'po_subfeeder_termin2_submit_invoice' => '', 'po_subfeeder_termin2_nilai_invoice' => '0',
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

        return $data;
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
            return false;
        }
        $targetId = (int) ($target['id_target'] ?? 0);
        if ($targetId <= 0) {
            return false;
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
            return false;
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

        $this->upsertImportedBak($clusterId, $row, $userId);
        $this->upsertImportedValsal($clusterId, $row, $userId);
        $this->upsertImportedBatch($clusterId, $row, $userId, $statusCurrent);
        $this->upsertImportedDrm($clusterId, $row, $userId);
        $this->upsertImportedPo($clusterId, $row, $userId);
        $this->upsertImportedRfsAtp($clusterId, $row, $userId, $target, $statusCurrent);

        $this->db->trans_complete();
        return $this->db->trans_status();
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

        $this->db->insert('tb_myrep_bak', [
            'id_myrep_cluster' => $clusterId,
            'ba_open_date' => $this->normalizeDate((string) ($row['ba_open_date'] ?? '')) ?: date('Y-m-d'),
            'bak_date' => $this->normalizeDate((string) ($row['bak_date'] ?? '')) ?: date('Y-m-d'),
            'homepass_bak' => max(0, $homepassBak),
            'status_bak' => 'DONE',
            'remark_bak' => trim((string) ($row['remark_general'] ?? '')) ?: null,
            'created_by' => $userId,
            'updated_by' => $userId,
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

        $this->db->insert('tb_myrep_valsal', [
            'id_myrep_cluster' => $clusterId,
            'valsal_date' => $valsalDate ?: date('Y-m-d'),
            'homepass_valsal' => max(0, $homepassValsal),
            'status_valsal' => 'DONE',
            'remark_valsal' => $remarkValsal !== '' ? $remarkValsal : (trim((string) ($row['remark_general'] ?? '')) ?: null),
            'created_by' => $userId,
            'updated_by' => $userId,
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
        $statusNeedsBatch = in_array($statusCurrent, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'DONE'], true);

        // Tahap BAK/VALSAL awal: jangan buat batch jika belum ada payload batch
        // dan status import tidak menuntut batch.
        if (!$hasBatchPayload && !$statusNeedsBatch) {
            return;
        }

        $stagingStatusFromRow = strtoupper(trim((string) ($row['staging_status'] ?? '')));
        $allowedStaging = ['DRAFT', 'WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED', 'REJECTED'];

        $stagingStatus = 'WAITING HO';
        if (in_array($statusCurrent, ['WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'DONE'], true)) {
            $stagingStatus = $statusCurrent === 'DONE BATCH APPROVAL' ? 'DONE' : $statusCurrent;
            if (in_array($statusCurrent, ['DRM', 'RFS', 'ATP', 'DONE'], true)) {
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

        $submittedToHoAt = in_array($stagingStatus, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED'], true)
            ? date('Y-m-d H:i:s')
            : null;
        $submittedToMyrepAt = in_array($stagingStatus, ['WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED'], true)
            ? date('Y-m-d H:i:s')
            : null;
        $submittedToFinanceAt = in_array($stagingStatus, ['WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED'], true)
            ? date('Y-m-d H:i:s')
            : null;

        $this->db->insert('tb_myrep_batch_approval', [
            'id_myrep_cluster' => $clusterId,
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
            'created_by' => $userId,
            'updated_by' => $userId,
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

        $this->db->insert('tb_myrep_drm', [
            'id_myrep_cluster' => $clusterId,
            'drm_date' => $drmDate ?: date('Y-m-d'),
            'homepass_drm' => max(0, $hpDrm),
            'nama_olt' => trim((string) ($row['nama_olt'] ?? '')) ?: null,
            'status_drm' => $statusDrm,
            'remark_drm' => $remarkDrm !== '' ? $remarkDrm : (trim((string) ($row['remark_general'] ?? '')) ?: null),
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    private function upsertImportedRfsAtp($clusterId, array $row, $userId, array $target, $statusCurrent)
    {
        if (!$this->db->table_exists('tb_rfs_myrep_cluster')) {
            return;
        }

        $rfsDate = $this->normalizeDate((string) ($row['rfs_date'] ?? $row['tanggal_rfs'] ?? ''));
        $emailAtpDate = $this->normalizeDate((string) ($row['email_atp_date'] ?? ''));
        $atpDate = $this->normalizeDate((string) ($row['actual_atp_date'] ?? ''));
        $statusAtpInput = strtoupper(trim((string) ($row['status_atp'] ?? '')));
        $rfsClaims = $this->extractRfsClaimsFromRow($row);
        $statusNeedsRfs = in_array($statusCurrent, ['RFS', 'ATP', 'DONE'], true);

        if (!$statusNeedsRfs && !$rfsDate && !$atpDate && empty($rfsClaims)) {
            return;
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
        if (in_array($statusRfsInput, ['FULL RFS', 'FULL'], true)) {
            // Explicit override from CSV: force full even if claim qty is still below DRM.
            $statusRfs = 'FULL RFS';
        } elseif (in_array($statusRfsInput, ['PARTIAL', 'PARTIAL RFS', 'NY RFS'], true)) {
            $statusRfs = 'NY RFS';
        }
        $statusAtp = 'NOT STARTED';
        if (in_array($statusAtpInput, ['DONE', 'PUNCLIST', 'REJECT'], true)) {
            $statusAtp = $statusAtpInput;
        } elseif (in_array($statusCurrent, ['ATP', 'DONE'], true)) {
            $statusAtp = 'DONE';
        } elseif ($emailAtpDate !== null) {
            $statusAtp = 'WAITING';
        }
        $dateAtp = $atpDate;

        $this->db->insert('tb_rfs_myrep_cluster', [
            'id_target' => (int) ($target['id_target'] ?? 0),
            'cluster_name' => trim((string) ($row['cluster_name'] ?? '')),
            'status_rfs' => $statusRfs,
            'homepass' => $homepassRfs,
            'status_atp' => $statusAtp,
            'email_atp_date' => $emailAtpDate,
            'created_by' => $userId,
        ]);
        $rfsClusterId = (int) $this->db->insert_id();

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
                if ($claimHasCreatedBy) {
                    $payload['created_by'] = $userId;
                }

                $this->db->insert('tb_rfs_myrep_claim', $payload);
            }
        }

        $syncAtpDate = $atpDate;
        if ($syncAtpDate === null && $statusAtp === 'DONE') {
            $syncAtpDate = $emailAtpDate;
        }
        $this->syncChecklistActualAtpDate($rfsClusterId, $syncAtpDate, $userId);
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

    private function buildPoTerminImportHeaders()
    {
        $headers = [];
        foreach (['po_cluster', 'po_subfeeder'] as $prefix) {
            for ($i = 1; $i <= 5; $i++) {
                $headers[] = $prefix . '_termin' . $i . '_plan_invoice';
                $headers[] = $prefix . '_termin' . $i . '_submit_invoice';
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

            $poHeaderId = (int) $this->MPO_MyRep->createPoHeader($clusterId, [
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
            ]);

            if ($poHeaderId > 0) {
                $this->applyImportedPoTerminDataFromRow($poHeaderId, $row, (string) ($poDef['prefix'] ?? 'po_cluster'));
            }
        }
    }

    private function applyImportedPoTerminDataFromRow($poHeaderId, array $row, $prefix)
    {
        if ($poHeaderId <= 0) {
            return;
        }

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
            $nilaiKey = $prefix . '_termin' . $terminNo . '_nilai_invoice';

            $planRaw = trim((string) ($row[$planKey] ?? ''));
            $submitRaw = trim((string) ($row[$submitKey] ?? ''));
            $nilaiRaw = trim((string) ($row[$nilaiKey] ?? ''));

            $submitDate = $this->normalizeDate($submitRaw);
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
        $value = str_replace(['.', ','], ['', '.'], $value);
        return is_numeric($value) ? (float) $value : 0;
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
        if (!class_exists('PHPExcel_IOFactory')) {
            $this->load->library('excel');
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
