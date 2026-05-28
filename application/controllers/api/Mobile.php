<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mobile extends CI_Controller
{
    private $tokenTtlSeconds = 2592000;
    private $tokenTable = 'tb_mobile_api_token';
    private $currentTokenHash = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        $this->ensureTokenTable();
    }

    public function login()
    {
        if (!$this->allowMethods(['POST'])) {
            return;
        }

        $input = $this->getJsonInput();
        $username = trim((string) ($input['username'] ?? $this->input->post('username')));
        $password = (string) ($input['password'] ?? $input['pass'] ?? $this->input->post('password') ?? $this->input->post('pass'));

        if ($username === '' || $password === '') {
            $this->json(false, 'Username dan password wajib diisi.', [], 422);
            return;
        }

        $auth = $this->authenticateUser($username, $password);
        if (!$auth['status']) {
            $this->json(false, $auth['message'], [
                'first_login_required' => !empty($auth['first_login_required']),
            ], $auth['code']);
            return;
        }

        $token = $this->issueToken((int) $auth['user']['id'], (string) ($input['device_name'] ?? ''));

        $this->json(true, 'Login berhasil.', [
            'token' => $token,
            'user' => $this->formatUser($auth['user']),
        ]);
    }

    public function logout()
    {
        if (!$this->allowMethods(['POST'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        $this->db
            ->where('token_hash', $this->currentTokenHash)
            ->update($this->tokenTable, [
                'revoked_at' => date('Y-m-d H:i:s'),
            ]);

        $this->json(true, 'Logout berhasil.');
    }

    public function me()
    {
        if (!$this->allowMethods(['GET'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        $this->json(true, 'OK', [
            'user' => $this->formatUser($user),
        ]);
    }

    public function dashboard()
    {
        if (!$this->allowMethods(['GET'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        $this->load->model('MChecklist_Dokument_MyRep');

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedRegional = strtoupper(trim((string) $this->input->get('regional')));

        try {
            $clusters = $this->MChecklist_Dokument_MyRep->getFullRfsClusters($selectedCity, $selectedRegional);
            $summary = $this->buildMobileDashboardSummary($clusters);

            $this->json(true, 'OK', [
                'data' => $summary,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Mobile dashboard failed: ' . $e->getMessage());
            $this->json(false, 'Gagal memuat dashboard.', [], 500);
        }
    }

    public function filters()
    {
        if (!$this->allowMethods(['GET'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        $this->load->model('MChecklist_Dokument_MyRep');

        try {
            $this->json(true, 'OK', [
                'data' => [
                    'cities' => $this->MChecklist_Dokument_MyRep->getCityOptions(),
                    'regionals' => $this->MChecklist_Dokument_MyRep->getRegionalOptions(),
                    'document_statuses' => [
                        'NOT UPLOADED',
                        'ON REVIEW',
                        'APPROVED',
                        'REJECTED',
                    ],
                    'astri_statuses' => [
                        'NY',
                        'ON REVIEW',
                        'APPROVED',
                        'REJECTED',
                        'WAITING WASPANG',
                        'WAITING PLANNING',
                        'WAITING TL',
                        'WAITING LOGISTIK',
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Mobile filters failed: ' . $e->getMessage());
            $this->json(false, 'Gagal memuat filter.', [], 500);
        }
    }

    public function checklists()
    {
        if (!$this->allowMethods(['GET'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        $this->load->model('MChecklist_Dokument_MyRep');

        $page = max(1, (int) $this->input->get('page'));
        $limit = (int) $this->input->get('limit');
        if ($limit <= 0 || $limit > 50) {
            $limit = 20;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedRegional = strtoupper(trim((string) $this->input->get('regional')));
        $search = strtoupper(trim((string) $this->input->get('search')));
        $start = ($page - 1) * $limit;

        try {
            $result = $this->MChecklist_Dokument_MyRep->getFullRfsClusterPage(
                $selectedCity,
                $selectedRegional,
                ['search' => $search],
                $start,
                $limit
            );

            $rows = [];
            foreach ((array) ($result['rows'] ?? []) as $row) {
                $rows[] = $this->formatChecklistSummary($row);
            }

            $this->json(true, 'OK', [
                'data' => $rows,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'records_total' => (int) ($result['recordsTotal'] ?? 0),
                    'records_filtered' => (int) ($result['recordsFiltered'] ?? 0),
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Mobile checklists failed: ' . $e->getMessage());
            $this->json(false, 'Gagal memuat checklist.', [], 500);
        }
    }

    public function checklist($clusterId = 0)
    {
        if (!$this->allowMethods(['GET'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            $this->json(false, 'Cluster tidak valid.', [], 422);
            return;
        }

        $this->load->model('MChecklist_Dokument_MyRep');

        try {
            $detail = $this->MChecklist_Dokument_MyRep->getClusterDetail($clusterId);
            if (empty($detail)) {
                $this->json(false, 'Cluster tidak ditemukan atau tidak dapat diakses.', [], 404);
                return;
            }

            $this->json(true, 'OK', [
                'data' => [
                    'cluster' => $this->formatChecklistSummary($detail),
                    'raw_cluster' => $detail,
                    'scopes' => $this->MChecklist_Dokument_MyRep->getClusterScopeTabs($clusterId),
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Mobile checklist detail failed: ' . $e->getMessage());
            $this->json(false, 'Gagal memuat detail checklist.', [], 500);
        }
    }

    public function uploadChecklistDocument()
    {
        if (!$this->allowMethods(['POST'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        if (!$this->hasMyrepPermission('TAMBAH')) {
            $this->json(false, 'Anda tidak memiliki akses upload dokumen.', [], 403);
            return;
        }

        $this->load->model('MChecklist_Dokument_MyRep');

        $clusterId = (int) $this->input->post('cluster_id');
        $packageId = (int) $this->input->post('id_doc_package');
        $itemId = (int) $this->input->post('id_doc_item');
        $docName = trim((string) $this->input->post('doc_name'));
        $remark = trim((string) $this->input->post('remark'));
        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;

        if ($clusterId <= 0 || $packageId <= 0 || $itemId <= 0) {
            $this->json(false, 'Data upload dokumen belum lengkap.', [], 422);
            return;
        }

        if (strlen($remark) > 500) {
            $this->json(false, 'Remark maksimal 500 karakter.', [], 422);
            return;
        }

        if (!$isNoDocumentRequired && empty($_FILES['file']['name'])) {
            $this->json(false, 'File wajib dipilih jika dokumen dibutuhkan.', [], 422);
            return;
        }

        $fileName = '';
        $filePath = '';
        if (!$isNoDocumentRequired) {
            $upload = $this->handleChecklistFileUpload($clusterId, $packageId, $itemId, $docName);
            if (!$upload['status']) {
                $this->json(false, $upload['message'], [], 422);
                return;
            }
            $fileName = $upload['file_name'];
            $filePath = $upload['file_path'];
        }

        $fileId = $this->MChecklist_Dokument_MyRep->saveFileUpload([
            'id_doc_package' => $packageId,
            'id_doc_item' => $itemId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status_file' => 'UPLOADED',
            'remark' => $remark,
            'uploaded_by' => (int) $user['id'],
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
        ]);

        if (!$fileId) {
            $this->json(false, 'Dokumen tidak dapat diupload atau tidak dapat diakses.', [], 403);
            return;
        }

        $this->json(true, $isNoDocumentRequired ? 'Dokumen ditandai tidak dibutuhkan.' : 'Dokumen berhasil diupload.', [
            'data' => [
                'id_doc_file' => (int) $fileId,
                'file_name' => $fileName,
                'file_path' => $filePath,
            ],
        ]);
    }

    public function approveChecklistDocument()
    {
        $this->reviewChecklistDocument('APPROVED');
    }

    public function rejectChecklistDocument()
    {
        $this->reviewChecklistDocument('REJECTED');
    }

    public function updateChecklistAstri()
    {
        if (!$this->allowMethods(['POST'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        if (!$this->isApprover() || !$this->hasMyrepPermission('APPROVAL')) {
            $this->json(false, 'Anda tidak memiliki akses update status ASTRI.', [], 403);
            return;
        }

        $this->load->model('MChecklist_Dokument_MyRep');

        $input = $this->getJsonInput();
        $fileId = (int) ($input['id_doc_file'] ?? $this->input->post('id_doc_file'));
        $astriStatus = strtoupper(trim((string) ($input['astri_status'] ?? $this->input->post('astri_status'))));
        $astriSubmittedDate = $this->normalizeDateInput($input['astri_submitted_date'] ?? $this->input->post('astri_submitted_date'));
        $astriRemark = trim((string) ($input['astri_remark'] ?? $this->input->post('astri_remark')));

        if ($fileId <= 0) {
            $this->json(false, 'Dokumen tidak valid.', [], 422);
            return;
        }

        if (strlen($astriRemark) > 500) {
            $this->json(false, 'Remark ASTRI maksimal 500 karakter.', [], 422);
            return;
        }

        $allowedStatuses = [
            'NY',
            'ON REVIEW',
            'WAITING WASPANG',
            'WAITING PLANNING',
            'WAITING TL',
            'WAITING LOGISTIK',
            'REJECTED',
            'APPROVED',
        ];
        if (!in_array($astriStatus, $allowedStatuses, true)) {
            $this->json(false, 'Status ASTRI tidak dikenali.', [], 422);
            return;
        }

        $file = $this->MChecklist_Dokument_MyRep->getFileById($fileId);
        if (empty($file)) {
            $this->json(false, 'Dokumen tidak ditemukan atau tidak dapat diakses.', [], 404);
            return;
        }

        $specialAstriStatuses = ['WAITING WASPANG', 'WAITING PLANNING', 'WAITING TL', 'WAITING LOGISTIK'];
        if (!(int) ($file['is_special_project_opname'] ?? 0) && in_array($astriStatus, $specialAstriStatuses, true)) {
            $this->json(false, 'Status ASTRI khusus ini hanya berlaku untuk Project Opname.', [], 422);
            return;
        }

        if ((int) ($file['is_special_project_opname'] ?? 0) && empty($file['cluster_actual_atp_date']) && $astriStatus !== 'NY') {
            $this->json(false, 'Project Opname hanya bisa masuk flow approval ASTRI setelah ATP terisi.', [], 422);
            return;
        }

        if (($file['status_file'] ?? '') !== 'APPROVED' && $astriStatus !== 'NY') {
            $this->json(false, 'Dokumen internal harus APPROVED sebelum di-submit ke ASTRI.', [], 422);
            return;
        }

        if ($astriStatus !== 'NY' && empty($astriSubmittedDate)) {
            $this->json(false, 'Tanggal submit ASTRI wajib diisi untuk status selain NY.', [], 422);
            return;
        }

        $updated = $this->MChecklist_Dokument_MyRep->updateAstriStatus($fileId, [
            'astri_submitted_date' => $astriStatus === 'NY' ? null : $astriSubmittedDate,
            'astri_status' => $astriStatus,
            'astri_remark' => $astriRemark,
        ]);

        if (!$updated) {
            $this->json(false, 'Status ASTRI gagal diperbarui.', [], 500);
            return;
        }

        $this->json(true, 'Status ASTRI berhasil diperbarui.');
    }

    public function updateChecklistTimeline()
    {
        if (!$this->allowMethods(['POST'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        if (!$this->hasMyrepPermission('EDIT')) {
            $this->json(false, 'Anda tidak memiliki akses update timeline.', [], 403);
            return;
        }

        $this->load->model('MChecklist_Dokument_MyRep');

        $input = $this->getJsonInput();
        $clusterId = (int) ($input['cluster_id'] ?? $this->input->post('cluster_id'));
        $actualAtpDate = $this->normalizeDateInput($input['actual_atp_date'] ?? $this->input->post('actual_atp_date'));

        if ($clusterId <= 0) {
            $this->json(false, 'Cluster tidak valid.', [], 422);
            return;
        }

        $updated = $this->MChecklist_Dokument_MyRep->updateClusterTimeline($clusterId, [
            'actual_atp_date' => $actualAtpDate,
            'updated_by' => (int) $user['id'],
        ]);

        if (!$updated) {
            $this->json(false, 'Timeline gagal diperbarui atau tidak dapat diakses.', [], 500);
            return;
        }

        $this->json(true, 'Timeline berhasil diperbarui.');
    }

    public function bakFilters()
    {
        if (!$this->allowMethods(['GET'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        $this->load->model('MBAK_MyRep');
        if ($this->MBAK_MyRep->bakDocumentTablesReady()) {
            $this->MBAK_MyRep->ensureBakDocumentSetup();
        }

        $this->json(true, 'OK', [
            'data' => [
                'cities' => $this->MBAK_MyRep->getCityOptions(),
                'regionals' => $this->MBAK_MyRep->getRegionalOptions(),
                'statuses' => ['DRAFT', 'ON REVIEW', 'DONE', 'REJECTED', 'BA OPEN', 'BAK'],
            ],
        ]);
    }

    public function bakClusters()
    {
        if (!$this->allowMethods(['GET'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        $this->load->model('MBAK_MyRep');
        if (!$this->MBAK_MyRep->bakTablesReady()) {
            $this->json(false, 'Tabel BAK MyRep belum tersedia.', [], 500);
            return;
        }
        if ($this->MBAK_MyRep->bakDocumentTablesReady()) {
            $this->MBAK_MyRep->ensureBakDocumentSetup();
        }

        $page = max(1, (int) $this->input->get('page'));
        $limit = (int) $this->input->get('limit');
        if ($limit <= 0 || $limit > 50) {
            $limit = 20;
        }

        $city = strtoupper(trim((string) $this->input->get('city')));
        $regional = strtoupper(trim((string) $this->input->get('regional')));
        $status = strtoupper(trim((string) $this->input->get('status')));
        $search = strtoupper(trim((string) $this->input->get('search')));

        $rows = $this->MBAK_MyRep->getBakRows($city, $status, $regional);
        if ($search !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($search) {
                $haystack = strtoupper(implode(' ', [
                    $row['cluster_name'] ?? '',
                    $row['cluster_code'] ?? '',
                    $row['regional_name'] ?? '',
                    $row['city_name'] ?? '',
                    $row['district_name'] ?? '',
                    $row['village_name'] ?? '',
                    $row['status_bak'] ?? '',
                    $row['status_current'] ?? '',
                ]));
                return strpos($haystack, $search) !== false;
            }));
        }

        $total = count($rows);
        $slice = array_slice($rows, ($page - 1) * $limit, $limit);

        $clusterIds = array_values(array_filter(array_map(static function ($row) {
            return (int) ($row['id_myrep_cluster'] ?? 0);
        }, $slice)));
        $documentMap = $this->MBAK_MyRep->bakDocumentTablesReady() && !empty($clusterIds)
            ? $this->MBAK_MyRep->getBakDocumentItemsByClusterIds($clusterIds)
            : [];

        $data = [];
        foreach ($slice as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $data[] = $this->formatBakCluster($row, $documentMap[$clusterId] ?? []);
        }

        $this->json(true, 'OK', [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'records_total' => $total,
                'records_filtered' => $total,
            ],
        ]);
    }

    public function bakCluster($clusterId = 0)
    {
        if (!$this->allowMethods(['GET'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        $this->load->model('MBAK_MyRep');
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            $this->json(false, 'Cluster tidak valid.', [], 422);
            return;
        }

        if ($this->MBAK_MyRep->bakDocumentTablesReady()) {
            $this->MBAK_MyRep->ensureBakDocumentSetup();
        }

        $cluster = $this->MBAK_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->json(false, 'Cluster BAK tidak ditemukan atau tidak dapat diakses.', [], 404);
            return;
        }

        $documentMap = $this->MBAK_MyRep->bakDocumentTablesReady()
            ? $this->MBAK_MyRep->getBakDocumentItemsByClusterIds([$clusterId])
            : [];
        $documents = array_values($documentMap[$clusterId] ?? []);

        $this->json(true, 'OK', [
            'data' => [
                'cluster' => $this->formatBakCluster($cluster, $documents),
                'raw_cluster' => $cluster,
                'documents' => array_map([$this, 'formatBakDocument'], $documents),
            ],
        ]);
    }

    public function uploadBakDocument()
    {
        if (!$this->allowMethods(['POST'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        if (!$this->hasMyrepPermission('TAMBAH')) {
            $this->json(false, 'Anda tidak memiliki akses upload dokumen BAK.', [], 403);
            return;
        }

        $this->load->model('MBAK_MyRep');
        if (!$this->MBAK_MyRep->bakTablesReady() || !$this->MBAK_MyRep->bakDocumentTablesReady()) {
            $this->json(false, 'Tabel dokumen BAK belum tersedia.', [], 500);
            return;
        }

        $this->MBAK_MyRep->ensureBakDocumentSetup();

        $clusterId = (int) $this->input->post('cluster_id');
        $docItemId = (int) $this->input->post('doc_item_id');
        $remark = trim((string) $this->input->post('remark'));
        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;

        if ($clusterId <= 0 || $docItemId <= 0) {
            $this->json(false, 'Data upload BAK belum lengkap.', [], 422);
            return;
        }
        if (strlen($remark) > 500) {
            $this->json(false, 'Remark maksimal 500 karakter.', [], 422);
            return;
        }

        $context = $this->MBAK_MyRep->getBakDocumentContext($clusterId, $docItemId);
        if (empty($context['id_doc_item'])) {
            $this->json(false, 'Konfigurasi dokumen BAK belum ditemukan.', [], 404);
            return;
        }

        $fileName = '';
        $filePath = '';
        if (!$isNoDocumentRequired) {
            if (empty($_FILES['file']['name'])) {
                $this->json(false, 'File ' . ($context['doc_name'] ?? 'dokumen') . ' wajib dipilih.', [], 422);
                return;
            }
            $upload = $this->handleMyrepFlowUpload('BAK', 'uploads/myrep_bak/', $clusterId, $docItemId, (string) ($context['doc_name'] ?? 'BAK_DOC'));
            if (!$upload['status']) {
                $this->json(false, $upload['message'], [], 422);
                return;
            }
            $fileName = $upload['file_name'];
            $filePath = $upload['file_path'];
        }

        $fileId = $this->MBAK_MyRep->saveBakFileUpload($clusterId, $docItemId, [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => $remark,
            'uploaded_by' => (int) $user['id'],
        ]);

        if ($fileId <= 0) {
            $this->json(false, 'Dokumen BAK gagal disimpan.', [], 500);
            return;
        }

        $this->MBAK_MyRep->syncBakStatusByCluster($clusterId, (int) $user['id']);
        $this->json(true, $isNoDocumentRequired ? 'Dokumen BAK ditandai tidak dibutuhkan.' : 'Dokumen BAK berhasil diupload.', [
            'data' => ['id_doc_file' => (int) $fileId],
        ]);
    }

    public function approveBakDocument()
    {
        $this->reviewBakDocument('APPROVED');
    }

    public function rejectBakDocument()
    {
        $this->reviewBakDocument('REJECTED');
    }

    public function approveAllBakDocuments()
    {
        if (!$this->allowMethods(['POST'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        if (!$this->isApprover() || !$this->hasMyrepPermission('APPROVAL')) {
            $this->json(false, 'Anda tidak memiliki akses approve semua dokumen BAK.', [], 403);
            return;
        }

        $this->load->model('MBAK_MyRep');
        $input = $this->getJsonInput();
        $clusterId = (int) ($input['cluster_id'] ?? $this->input->post('cluster_id'));
        if ($clusterId <= 0) {
            $this->json(false, 'Cluster BAK tidak valid.', [], 422);
            return;
        }

        $documentMap = $this->MBAK_MyRep->getBakDocumentItemsByClusterIds([$clusterId]);
        $documents = array_values($documentMap[$clusterId] ?? []);
        $updatedCount = 0;
        foreach ($documents as $document) {
            $fileId = (int) ($document['id_doc_file'] ?? 0);
            $status = strtoupper(trim((string) ($document['status_file'] ?? '')));
            if ($fileId <= 0 || !in_array($status, ['UPLOADED', 'REJECTED'], true)) {
                continue;
            }
            if ($this->MBAK_MyRep->updateBakFileStatus($fileId, [
                'status_file' => 'APPROVED',
                'remark' => (string) ($document['remark'] ?? ''),
                'approved_by' => (int) $user['id'],
            ])) {
                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            $this->MBAK_MyRep->syncBakStatusByCluster($clusterId, (int) $user['id']);
            $this->json(true, $updatedCount . ' dokumen BAK berhasil di-approve.');
            return;
        }

        $this->json(false, 'Tidak ada dokumen BAK yang bisa di-approve.', [], 422);
    }

    public function valsalFilters()
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MVALSAL_MyRep');
        if ($this->MVALSAL_MyRep->valsalDocumentTablesReady()) {
            $this->MVALSAL_MyRep->ensureValsalDocumentSetup();
        }

        $this->json(true, 'OK', [
            'data' => [
                'cities' => $this->MVALSAL_MyRep->getCityOptions(),
                'regionals' => $this->MVALSAL_MyRep->getRegionalOptions(),
                'statuses' => ['DRAFT', 'ON REVIEW', 'DONE', 'APPROVED', 'REJECTED', 'VALSAL'],
            ],
        ]);
    }

    public function valsalClusters()
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MVALSAL_MyRep');
        if (!$this->MVALSAL_MyRep->valsalTablesReady()) {
            $this->json(false, 'Tabel VALSAL MyRep belum tersedia.', [], 500);
            return;
        }
        if ($this->MVALSAL_MyRep->valsalDocumentTablesReady()) {
            $this->MVALSAL_MyRep->ensureValsalDocumentSetup();
        }

        $page = max(1, (int) $this->input->get('page'));
        $limit = $this->normalizeLimit($this->input->get('limit'));
        $city = strtoupper(trim((string) $this->input->get('city')));
        $regional = strtoupper(trim((string) $this->input->get('regional')));
        $status = strtoupper(trim((string) $this->input->get('status')));
        $search = strtoupper(trim((string) $this->input->get('search')));

        $rows = $this->MVALSAL_MyRep->getValsalRows($city, $status, $regional);
        $rows = $this->filterStageRowsBySearch($rows, $search);
        $total = count($rows);
        $slice = array_slice($rows, ($page - 1) * $limit, $limit);
        $clusterIds = $this->extractClusterIds($slice);
        $documentMap = $this->MVALSAL_MyRep->valsalDocumentTablesReady() && !empty($clusterIds)
            ? $this->MVALSAL_MyRep->getValsalDocumentItemsByClusterIds($clusterIds)
            : [];

        $data = [];
        foreach ($slice as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $data[] = $this->formatFlowCluster($row, $documentMap[$clusterId] ?? [], 'VALSAL');
        }

        $this->json(true, 'OK', [
            'data' => $data,
            'pagination' => $this->buildPagination($page, $limit, $total),
        ]);
    }

    public function valsalCluster($clusterId = 0)
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MVALSAL_MyRep');
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            $this->json(false, 'Cluster VALSAL tidak valid.', [], 422);
            return;
        }
        if ($this->MVALSAL_MyRep->valsalDocumentTablesReady()) {
            $this->MVALSAL_MyRep->ensureValsalDocumentSetup();
        }

        $cluster = $this->MVALSAL_MyRep->getValsalByClusterId($clusterId);
        if (empty($cluster)) {
            $this->json(false, 'Cluster VALSAL tidak ditemukan atau tidak dapat diakses.', [], 404);
            return;
        }

        $documentMap = $this->MVALSAL_MyRep->valsalDocumentTablesReady()
            ? $this->MVALSAL_MyRep->getValsalDocumentItemsByClusterIds([$clusterId])
            : [];
        $documents = array_values($documentMap[$clusterId] ?? []);

        $this->json(true, 'OK', [
            'data' => [
                'cluster' => $this->formatFlowCluster($cluster, $documents, 'VALSAL'),
                'raw_cluster' => $cluster,
                'documents' => array_map([$this, 'formatFlowDocument'], $documents),
            ],
        ]);
    }

    public function uploadValsalDocument()
    {
        $this->uploadFlowDocument('VALSAL');
    }

    public function approveValsalDocument()
    {
        $this->reviewFlowDocument('VALSAL', 'APPROVED');
    }

    public function rejectValsalDocument()
    {
        $this->reviewFlowDocument('VALSAL', 'REJECTED');
    }

    public function approveAllValsalDocuments()
    {
        $this->approveAllFlowDocuments('VALSAL');
    }

    public function postDonasiFilters()
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MPost_Donasi_MyRep');
        $rows = $this->MPost_Donasi_MyRep->getRows('', '');
        $this->json(true, 'OK', [
            'data' => [
                'cities' => $this->MPost_Donasi_MyRep->getCityOptions(),
                'regionals' => $this->extractDistinctFromRows($rows, 'regional_name'),
                'statuses' => ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'],
            ],
        ]);
    }

    public function postDonasiClusters()
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MPost_Donasi_MyRep');
        if (!$this->MPost_Donasi_MyRep->tablesReady()) {
            $this->json(false, 'Tabel Post Donasi MyRep belum tersedia.', [], 500);
            return;
        }

        $page = max(1, (int) $this->input->get('page'));
        $limit = $this->normalizeLimit($this->input->get('limit'));
        $city = strtoupper(trim((string) $this->input->get('city')));
        $regional = strtoupper(trim((string) $this->input->get('regional')));
        $status = strtoupper(trim((string) $this->input->get('status')));
        $search = strtoupper(trim((string) $this->input->get('search')));

        $rows = $this->MPost_Donasi_MyRep->getRows($city, $status);
        if ($regional !== '') {
            $rows = array_values(array_filter($rows, static function ($row) use ($regional) {
                return strtoupper(trim((string) ($row['regional_name'] ?? ''))) === $regional;
            }));
        }
        $rows = $this->filterStageRowsBySearch($rows, $search);
        $total = count($rows);
        $slice = array_slice($rows, ($page - 1) * $limit, $limit);

        $data = [];
        foreach ($slice as $row) {
            $data[] = $this->formatFlowCluster($row, [], 'POST_DONASI');
        }

        $this->json(true, 'OK', [
            'data' => $data,
            'pagination' => $this->buildPagination($page, $limit, $total),
        ]);
    }

    public function postDonasiCluster($clusterId = 0)
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MPost_Donasi_MyRep');
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            $this->json(false, 'Cluster Post Donasi tidak valid.', [], 422);
            return;
        }

        $cluster = $this->MPost_Donasi_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->json(false, 'Cluster Post Donasi tidak ditemukan.', [], 404);
            return;
        }
        $documents = $this->MPost_Donasi_MyRep->documentTablesReady()
            ? $this->MPost_Donasi_MyRep->getDocumentRows($clusterId)
            : [];

        $this->json(true, 'OK', [
            'data' => [
                'cluster' => $this->formatFlowCluster($cluster, $documents, 'POST_DONASI'),
                'raw_cluster' => $cluster,
                'documents' => array_map([$this, 'formatFlowDocument'], $documents),
            ],
        ]);
    }

    public function uploadPostDonasiDocument()
    {
        $this->uploadFlowDocument('POST_DONASI');
    }

    public function approvePostDonasiDocument()
    {
        $this->reviewFlowDocument('POST_DONASI', 'APPROVED');
    }

    public function rejectPostDonasiDocument()
    {
        $this->reviewFlowDocument('POST_DONASI', 'REJECTED');
    }

    public function approveAllPostDonasiDocuments()
    {
        $this->approveAllFlowDocuments('POST_DONASI');
    }

    public function batchFilters()
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MBatch_Approval_MyRep');
        $this->json(true, 'OK', [
            'data' => [
                'cities' => $this->MBatch_Approval_MyRep->getCityOptions(),
                'regionals' => $this->MBatch_Approval_MyRep->getRegionalOptions(),
                'statuses' => ['DRAFT', 'WAITING DOC', 'WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL', 'REJECTED', 'COMPLETED'],
            ],
        ]);
    }

    public function batchClusters()
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MBatch_Approval_MyRep');
        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
            $this->json(false, 'Tabel Batch Approval MyRep belum tersedia.', [], 500);
            return;
        }

        $page = max(1, (int) $this->input->get('page'));
        $limit = $this->normalizeLimit($this->input->get('limit'));
        $city = strtoupper(trim((string) $this->input->get('city')));
        $regional = strtoupper(trim((string) $this->input->get('regional')));
        $status = strtoupper(trim((string) $this->input->get('status')));
        $search = strtoupper(trim((string) $this->input->get('search')));

        $rows = $this->MBatch_Approval_MyRep->getBatchRows($city, $status, $regional);
        $rows = $this->filterStageRowsBySearch($rows, $search);
        $total = count($rows);
        $slice = array_slice($rows, ($page - 1) * $limit, $limit);
        $data = [];
        foreach ($slice as $row) {
            $data[] = $this->formatFlowCluster($row, $this->extractBatchDocumentsFromRow($row), 'BATCH');
        }

        $this->json(true, 'OK', [
            'data' => $data,
            'pagination' => $this->buildPagination($page, $limit, $total),
        ]);
    }

    public function batchCluster($clusterId = 0)
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MBatch_Approval_MyRep');
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            $this->json(false, 'Cluster Batch tidak valid.', [], 422);
            return;
        }

        $cluster = $this->MBatch_Approval_MyRep->getBatchByClusterId($clusterId);
        if (empty($cluster)) {
            $this->json(false, 'Cluster Batch tidak ditemukan atau tidak dapat diakses.', [], 404);
            return;
        }
        $document = $this->MBatch_Approval_MyRep->batchDocumentTablesReady()
            ? $this->MBatch_Approval_MyRep->getBatchDocumentContext($clusterId)
            : [];
        $documents = empty($document['id_doc_item']) ? [] : [$this->normalizeBatchDocument($document)];

        $this->json(true, 'OK', [
            'data' => [
                'cluster' => $this->formatFlowCluster($cluster, $documents, 'BATCH'),
                'raw_cluster' => $cluster,
                'documents' => array_map([$this, 'formatFlowDocument'], $documents),
            ],
        ]);
    }

    public function uploadBatchDocument()
    {
        $this->uploadFlowDocument('BATCH');
    }

    public function approveBatchDocument()
    {
        $this->reviewFlowDocument('BATCH', 'APPROVED');
    }

    public function rejectBatchDocument()
    {
        $this->reviewFlowDocument('BATCH', 'REJECTED');
    }

    public function drmFilters()
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MDRM_MyRep');
        $this->json(true, 'OK', [
            'data' => [
                'cities' => $this->MDRM_MyRep->getCityOptions(),
                'regionals' => $this->MDRM_MyRep->getRegionalOptions(),
                'statuses' => ['NOT STARTED', 'DRAFT', 'WAITING DOC', 'WAITING HO', 'APPROVED', 'REJECTED', 'RELEASED', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'],
                'scopes' => ['CLUSTER', 'SUBFEEDER'],
            ],
        ]);
    }

    public function drmClusters()
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MDRM_MyRep');
        if (!$this->MDRM_MyRep->drmTablesReady()) {
            $this->json(false, 'Tabel DRM MyRep belum tersedia.', [], 500);
            return;
        }

        $page = max(1, (int) $this->input->get('page'));
        $limit = $this->normalizeLimit($this->input->get('limit'));
        $city = strtoupper(trim((string) $this->input->get('city')));
        $regional = strtoupper(trim((string) $this->input->get('regional')));
        $status = strtoupper(trim((string) $this->input->get('status')));
        $search = strtoupper(trim((string) $this->input->get('search')));

        $rows = $this->MDRM_MyRep->getDrmRows($city, $status, $regional);
        $rows = $this->filterStageRowsBySearch($rows, $search);
        $total = count($rows);
        $slice = array_slice($rows, ($page - 1) * $limit, $limit);
        $data = [];
        foreach ($slice as $row) {
            $data[] = $this->formatFlowCluster($row, [], 'DRM');
        }

        $this->json(true, 'OK', [
            'data' => $data,
            'pagination' => $this->buildPagination($page, $limit, $total),
        ]);
    }

    public function drmCluster($clusterId = 0)
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MDRM_MyRep');
        $clusterId = (int) $clusterId;
        $scope = strtoupper(trim((string) $this->input->get('scope')));
        if ($scope !== 'SUBFEEDER') $scope = 'CLUSTER';
        if ($clusterId <= 0) {
            $this->json(false, 'Cluster DRM tidak valid.', [], 422);
            return;
        }

        $cluster = $this->MDRM_MyRep->getDrmByClusterId($clusterId);
        if (empty($cluster)) {
            $this->json(false, 'Cluster DRM tidak ditemukan atau tidak dapat diakses.', [], 404);
            return;
        }
        $documents = $this->MDRM_MyRep->drmDocumentTablesReady()
            ? $this->MDRM_MyRep->getDrmDocumentRows($clusterId, $scope)
            : [];

        $this->json(true, 'OK', [
            'data' => [
                'cluster' => $this->formatFlowCluster($cluster, $documents, 'DRM'),
                'raw_cluster' => $cluster,
                'scope' => $scope,
                'documents' => array_map([$this, 'formatFlowDocument'], $documents),
            ],
        ]);
    }

    public function uploadDrmDocument()
    {
        $this->uploadFlowDocument('DRM');
    }

    public function approveDrmDocument()
    {
        $this->reviewFlowDocument('DRM', 'APPROVED');
    }

    public function rejectDrmDocument()
    {
        $this->reviewFlowDocument('DRM', 'REJECTED');
    }

    public function atpFilters()
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MATP_MyRep');
        $this->json(true, 'OK', [
            'data' => [
                'cities' => $this->MATP_MyRep->getCityOptions(),
                'regionals' => $this->MATP_MyRep->getRegionalOptions(),
                'statuses' => $this->MATP_MyRep->getStageOptions(),
                'atp_statuses' => ['', 'PUNCLIST', 'DONE'],
            ],
        ]);
    }

    public function atpClusters()
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MATP_MyRep');
        if (!$this->MATP_MyRep->supportsAtpColumns() || !$this->MATP_MyRep->supportsAtpFileTable()) {
            $this->json(false, 'Kolom atau tabel ATP belum tersedia.', [], 500);
            return;
        }

        $page = max(1, (int) $this->input->get('page'));
        $limit = $this->normalizeLimit($this->input->get('limit'));
        $city = strtoupper(trim((string) $this->input->get('city')));
        $regional = strtoupper(trim((string) $this->input->get('regional')));
        $stage = trim((string) $this->input->get('status'));
        $search = strtoupper(trim((string) $this->input->get('search')));

        $rows = $this->MATP_MyRep->getClusterRows($city, $regional, $stage);
        $rows = $this->filterStageRowsBySearch($rows, $search);
        $total = count($rows);
        $slice = array_slice($rows, ($page - 1) * $limit, $limit);
        $data = [];
        foreach ($slice as $row) {
            $data[] = $this->formatAtpCluster($row);
        }

        $this->json(true, 'OK', [
            'data' => $data,
            'pagination' => $this->buildPagination($page, $limit, $total),
        ]);
    }

    public function atpCluster($clusterId = 0)
    {
        if (!$this->allowMethods(['GET'])) return;
        if (!$this->requireAuth()) return;

        $this->load->model('MATP_MyRep');
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            $this->json(false, 'Cluster ATP tidak valid.', [], 422);
            return;
        }

        $cluster = $this->MATP_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->json(false, 'Cluster ATP tidak ditemukan.', [], 404);
            return;
        }

        $this->json(true, 'OK', [
            'data' => [
                'cluster' => $this->formatAtpCluster($cluster),
                'raw_cluster' => $cluster,
                'documents' => $this->formatAtpDocuments($cluster),
            ],
        ]);
    }

    public function updateAtpCluster()
    {
        if (!$this->allowMethods(['POST'])) return;
        $user = $this->requireAuth();
        if (!$user) return;
        if (!$this->hasMyrepPermission('EDIT')) {
            $this->json(false, 'Anda tidak memiliki akses update ATP.', [], 403);
            return;
        }

        $this->load->model('MATP_MyRep');
        $this->load->model('MChecklist_Dokument_MyRep');

        $input = $this->getJsonInput();
        $clusterId = (int) ($input['cluster_id'] ?? $this->input->post('cluster_id'));
        $emailAtpDate = $this->normalizeDateInput($input['email_atp_date'] ?? $this->input->post('email_atp_date'));
        $actualAtpDate = $this->normalizeDateInput($input['actual_atp_date'] ?? $this->input->post('actual_atp_date'));
        $statusAtp = strtoupper(trim((string) ($input['status_atp'] ?? $this->input->post('status_atp'))));

        if ($clusterId <= 0) {
            $this->json(false, 'Cluster ATP tidak valid.', [], 422);
            return;
        }
        if (!in_array($statusAtp, ['', 'PUNCLIST', 'DONE'], true)) {
            $this->json(false, 'Status ATP tidak dikenali.', [], 422);
            return;
        }
        if ($actualAtpDate !== null && $emailAtpDate === null) {
            $this->json(false, 'Tanggal email ATP wajib diisi sebelum tanggal ATP.', [], 422);
            return;
        }
        if ($statusAtp !== '' && $actualAtpDate === null) {
            $this->json(false, 'Tanggal ATP wajib diisi sebelum status ATP.', [], 422);
            return;
        }

        $cluster = $this->MATP_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->json(false, 'Cluster ATP tidak ditemukan.', [], 404);
            return;
        }

        if ($statusAtp === 'PUNCLIST' && !$this->MATP_MyRep->hasAtpDocument($clusterId, 'RECORD_PUNCLIST')) {
            $this->json(false, 'Status ATP PUNCLIST wajib upload Record Punclist.', [], 422);
            return;
        }
        if (strtoupper((string) ($cluster['status_atp'] ?? '')) === 'PUNCLIST' && $statusAtp === 'DONE' && !$this->MATP_MyRep->hasAtpDocument($clusterId, 'BA_RECTIFICATION')) {
            $this->json(false, 'Perubahan ATP dari PUNCLIST ke DONE wajib upload BA Rectification.', [], 422);
            return;
        }

        $this->MChecklist_Dokument_MyRep->ensureClusterPackages($clusterId, $cluster['tanggal_rfs'] ?? null);
        $this->MChecklist_Dokument_MyRep->updateClusterTimeline($clusterId, [
            'actual_atp_date' => $actualAtpDate,
            'updated_by' => (int) $user['id'],
        ]);

        $updated = $this->MATP_MyRep->updateClusterAtpMetadata($clusterId, $emailAtpDate, $statusAtp);
        if (!$updated) {
            $this->json(false, 'Data ATP gagal diperbarui.', [], 500);
            return;
        }

        $this->json(true, 'Data ATP berhasil diperbarui.');
    }

    public function uploadAtpDocument()
    {
        if (!$this->allowMethods(['POST'])) return;
        $user = $this->requireAuth();
        if (!$user) return;
        if (!$this->hasMyrepPermission('TAMBAH')) {
            $this->json(false, 'Anda tidak memiliki akses upload dokumen ATP.', [], 403);
            return;
        }

        $this->load->model('MATP_MyRep');
        $clusterId = (int) $this->input->post('cluster_id');
        $docType = strtoupper(trim((string) $this->input->post('doc_type')));
        $remark = trim((string) $this->input->post('remark'));
        if ($clusterId <= 0 || !in_array($docType, ['RECORD_PUNCLIST', 'BA_RECTIFICATION'], true)) {
            $this->json(false, 'Data upload ATP belum lengkap.', [], 422);
            return;
        }
        if (strlen($remark) > 500) {
            $this->json(false, 'Remark maksimal 500 karakter.', [], 422);
            return;
        }
        if (empty($_FILES['file']['name'])) {
            $this->json(false, 'File ATP wajib dipilih.', [], 422);
            return;
        }
        if (empty($this->MATP_MyRep->getClusterById($clusterId))) {
            $this->json(false, 'Cluster ATP tidak ditemukan.', [], 404);
            return;
        }

        $upload = $this->handleMyrepFlowUpload('ATP_' . $docType, 'uploads/atp_myrep/', $clusterId, 0, $docType);
        if (!$upload['status']) {
            $this->json(false, $upload['message'], [], 422);
            return;
        }

        $saved = $this->MATP_MyRep->saveAtpFileUpload([
            'cluster_id' => $clusterId,
            'doc_type' => $docType,
            'file_name' => $upload['file_name'],
            'file_path' => $upload['file_path'],
            'remark' => $remark,
            'uploaded_by' => (int) $user['id'],
        ]);
        if (!$saved) {
            $this->json(false, 'Dokumen ATP gagal disimpan.', [], 500);
            return;
        }

        $this->json(true, 'Dokumen ATP berhasil diupload.');
    }

    private function uploadFlowDocument($flow)
    {
        if (!$this->allowMethods(['POST'])) return;
        $user = $this->requireAuth();
        if (!$user) return;
        if (!$this->hasMyrepPermission('TAMBAH')) {
            $this->json(false, 'Anda tidak memiliki akses upload dokumen ' . $this->flowLabel($flow) . '.', [], 403);
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $docItemId = (int) $this->input->post('doc_item_id');
        $remark = trim((string) $this->input->post('remark'));
        $isNoDocumentRequired = (int) $this->input->post('is_document_not_required') === 1;
        $scope = strtoupper(trim((string) $this->input->post('scope')));
        if ($scope !== 'SUBFEEDER') $scope = 'CLUSTER';

        if ($clusterId <= 0) {
            $this->json(false, 'Cluster ' . $this->flowLabel($flow) . ' tidak valid.', [], 422);
            return;
        }
        if ($flow !== 'BATCH' && $docItemId <= 0) {
            $this->json(false, 'Dokumen ' . $this->flowLabel($flow) . ' tidak valid.', [], 422);
            return;
        }
        if (strlen($remark) > 500) {
            $this->json(false, 'Remark maksimal 500 karakter.', [], 422);
            return;
        }

        $model = $this->loadFlowModel($flow);
        $context = $this->getFlowDocumentContext($flow, $model, $clusterId, $docItemId, $scope);
        if (empty($context['id_doc_item'])) {
            $this->json(false, 'Konfigurasi dokumen ' . $this->flowLabel($flow) . ' belum ditemukan.', [], 404);
            return;
        }
        $docItemId = (int) ($context['id_doc_item'] ?? $docItemId);

        $fileName = '';
        $filePath = '';
        if (!$isNoDocumentRequired) {
            if (empty($_FILES['file']['name'])) {
                $this->json(false, 'File ' . ($context['doc_name'] ?? 'dokumen') . ' wajib dipilih.', [], 422);
                return;
            }
            $upload = $this->handleMyrepFlowUpload($this->flowUploadPrefix($flow), $this->flowUploadDir($flow), $clusterId, $docItemId, (string) ($context['doc_name'] ?? $this->flowLabel($flow)));
            if (!$upload['status']) {
                $this->json(false, $upload['message'], [], 422);
                return;
            }
            $fileName = $upload['file_name'];
            $filePath = $upload['file_path'];
        }

        $payload = [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'is_document_not_required' => $isNoDocumentRequired ? 1 : 0,
            'status_file' => 'UPLOADED',
            'remark' => $remark,
            'uploaded_by' => (int) $user['id'],
        ];

        $fileId = $this->saveFlowFileUpload($flow, $model, $clusterId, $docItemId, $payload, $scope);
        if ($fileId <= 0) {
            $this->json(false, 'Dokumen ' . $this->flowLabel($flow) . ' gagal disimpan.', [], 500);
            return;
        }
        $this->syncFlowStatus($flow, $model, $clusterId, (int) $user['id']);

        $this->json(true, $isNoDocumentRequired ? 'Dokumen ditandai tidak dibutuhkan.' : 'Dokumen berhasil diupload.', [
            'data' => ['id_doc_file' => (int) $fileId],
        ]);
    }

    private function reviewFlowDocument($flow, $status)
    {
        if (!$this->allowMethods(['POST'])) return;
        $user = $this->requireAuth();
        if (!$user) return;
        if (!$this->isApprover() || !$this->hasMyrepPermission('APPROVAL')) {
            $this->json(false, 'Anda tidak memiliki akses review dokumen ' . $this->flowLabel($flow) . '.', [], 403);
            return;
        }

        $input = $this->getJsonInput();
        $fileId = (int) ($input['id_doc_file'] ?? $this->input->post('id_doc_file'));
        $remark = trim((string) ($input['remark'] ?? $this->input->post('remark')));
        if ($fileId <= 0) {
            $this->json(false, 'Dokumen ' . $this->flowLabel($flow) . ' tidak valid.', [], 422);
            return;
        }
        if ($status === 'REJECTED' && $remark === '') {
            $this->json(false, 'Remark wajib diisi saat reject dokumen.', [], 422);
            return;
        }
        if (strlen($remark) > 500) {
            $this->json(false, 'Remark maksimal 500 karakter.', [], 422);
            return;
        }

        $model = $this->loadFlowModel($flow);
        $file = $this->getFlowFileById($flow, $model, $fileId);
        if (empty($file['id_myrep_cluster'])) {
            $this->json(false, 'Dokumen ' . $this->flowLabel($flow) . ' tidak ditemukan atau tidak dapat diakses.', [], 404);
            return;
        }

        $updated = $this->updateFlowFileStatus($flow, $model, $fileId, [
            'status_file' => $status,
            'remark' => $remark,
            'approved_by' => (int) $user['id'],
        ]);
        if (!$updated) {
            $this->json(false, 'Status dokumen gagal diperbarui.', [], 500);
            return;
        }
        $this->syncFlowStatus($flow, $model, (int) $file['id_myrep_cluster'], (int) $user['id']);

        $this->json(true, $status === 'APPROVED' ? 'Dokumen berhasil di-approve.' : 'Dokumen berhasil di-reject.');
    }

    private function approveAllFlowDocuments($flow)
    {
        if (!$this->allowMethods(['POST'])) return;
        $user = $this->requireAuth();
        if (!$user) return;
        if (!$this->isApprover() || !$this->hasMyrepPermission('APPROVAL')) {
            $this->json(false, 'Anda tidak memiliki akses approve semua dokumen ' . $this->flowLabel($flow) . '.', [], 403);
            return;
        }

        $input = $this->getJsonInput();
        $clusterId = (int) ($input['cluster_id'] ?? $this->input->post('cluster_id'));
        if ($clusterId <= 0) {
            $this->json(false, 'Cluster tidak valid.', [], 422);
            return;
        }

        $model = $this->loadFlowModel($flow);
        $documents = $this->getFlowDocuments($flow, $model, $clusterId, 'CLUSTER');
        $updatedCount = 0;
        foreach ($documents as $document) {
            $fileId = (int) ($document['id_doc_file'] ?? 0);
            $status = strtoupper(trim((string) ($document['status_file'] ?? '')));
            if ($fileId <= 0 || !in_array($status, ['UPLOADED', 'REJECTED'], true)) {
                continue;
            }
            if ($this->updateFlowFileStatus($flow, $model, $fileId, [
                'status_file' => 'APPROVED',
                'remark' => (string) ($document['remark'] ?? ''),
                'approved_by' => (int) $user['id'],
            ])) {
                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            $this->syncFlowStatus($flow, $model, $clusterId, (int) $user['id']);
            $this->json(true, $updatedCount . ' dokumen berhasil di-approve.');
            return;
        }

        $this->json(false, 'Tidak ada dokumen yang bisa di-approve.', [], 422);
    }

    private function loadFlowModel($flow)
    {
        switch ($flow) {
            case 'VALSAL':
                $this->load->model('MVALSAL_MyRep');
                return $this->MVALSAL_MyRep;
            case 'POST_DONASI':
                $this->load->model('MPost_Donasi_MyRep');
                return $this->MPost_Donasi_MyRep;
            case 'BATCH':
                $this->load->model('MBatch_Approval_MyRep');
                return $this->MBatch_Approval_MyRep;
            case 'DRM':
                $this->load->model('MDRM_MyRep');
                return $this->MDRM_MyRep;
        }

        show_error('Flow tidak didukung.', 500);
    }

    private function getFlowDocumentContext($flow, $model, $clusterId, $docItemId, $scope)
    {
        switch ($flow) {
            case 'VALSAL':
                return $model->getValsalDocumentContext($clusterId, $docItemId);
            case 'POST_DONASI':
                return $model->getDocumentDetail($clusterId, $docItemId);
            case 'BATCH':
                return $model->getBatchDocumentContext($clusterId);
            case 'DRM':
                return $model->getDrmDocumentDetail($clusterId, $docItemId, $scope);
        }
        return [];
    }

    private function saveFlowFileUpload($flow, $model, $clusterId, $docItemId, array $payload, $scope)
    {
        switch ($flow) {
            case 'VALSAL':
                return (int) $model->saveValsalFileUpload($clusterId, $docItemId, $payload);
            case 'POST_DONASI':
                return (int) $model->saveFileUpload($clusterId, $docItemId, $payload);
            case 'BATCH':
                return (int) $model->saveBatchFileUpload($clusterId, $payload);
            case 'DRM':
                return (int) $model->saveDrmFileUpload($clusterId, $docItemId, $payload, $scope);
        }
        return 0;
    }

    private function getFlowFileById($flow, $model, $fileId)
    {
        switch ($flow) {
            case 'VALSAL':
                return $model->getValsalFileById($fileId);
            case 'POST_DONASI':
                return $model->getFileById($fileId);
            case 'BATCH':
                return $model->getBatchFileById($fileId);
            case 'DRM':
                return $model->getDrmFileById($fileId);
        }
        return [];
    }

    private function updateFlowFileStatus($flow, $model, $fileId, array $payload)
    {
        switch ($flow) {
            case 'VALSAL':
                return $model->updateValsalFileStatus($fileId, $payload);
            case 'POST_DONASI':
                return $model->updateFileStatus($fileId, $payload);
            case 'BATCH':
                return $model->updateBatchFileStatus($fileId, $payload);
            case 'DRM':
                return $model->updateDrmFileStatus($fileId, $payload);
        }
        return false;
    }

    private function syncFlowStatus($flow, $model, $clusterId, $userId)
    {
        if ($flow === 'VALSAL') {
            $model->syncValsalStatusByCluster($clusterId, $userId);
        }
    }

    private function getFlowDocuments($flow, $model, $clusterId, $scope)
    {
        switch ($flow) {
            case 'VALSAL':
                $map = $model->getValsalDocumentItemsByClusterIds([$clusterId]);
                return array_values($map[$clusterId] ?? []);
            case 'POST_DONASI':
                return $model->getDocumentRows($clusterId);
            case 'BATCH':
                $document = $model->getBatchDocumentContext($clusterId);
                return empty($document['id_doc_item']) ? [] : [$this->normalizeBatchDocument($document)];
            case 'DRM':
                return $model->getDrmDocumentRows($clusterId, $scope);
        }
        return [];
    }

    private function flowLabel($flow)
    {
        $labels = [
            'VALSAL' => 'VALSAL',
            'POST_DONASI' => 'Post Donasi',
            'BATCH' => 'Batch Approval',
            'DRM' => 'DRM',
        ];
        return $labels[$flow] ?? (string) $flow;
    }

    private function flowUploadPrefix($flow)
    {
        $prefixes = [
            'VALSAL' => 'VALSAL',
            'POST_DONASI' => 'POST_DONASI',
            'BATCH' => 'BATCH',
            'DRM' => 'DRM',
        ];
        return $prefixes[$flow] ?? 'DOC';
    }

    private function flowUploadDir($flow)
    {
        $dirs = [
            'VALSAL' => 'uploads/myrep_valsal/',
            'POST_DONASI' => 'uploads/myrep_post_donasi/',
            'BATCH' => 'uploads/myrep_batch_approval/',
            'DRM' => 'uploads/myrep_drm/',
        ];
        return $dirs[$flow] ?? 'uploads/';
    }

    private function authenticateUser($username, $password)
    {
        $user = $this->db->query("select
                                    a.*,
                                    tl.*,
                                    COALESCE(a.nama_karyawan, '') as nama_user,
                                    COALESCE(a.lokasi_kantor, a.homebase, 'HO') as lokasi_user,
                                    '' as nama_jabatan,
                                    '' as under_sm,
                                    '' as under_pm
                                from
                                    tb_master_user_new a
                                left join tb_level tl ON a.id_level = tl.id_level
                                WHERE a.username_user = " . $this->db->escape($username) . "
                        ")->row_array();

        if (!$user) {
            return ['status' => false, 'message' => 'User tidak ditemukan.', 'code' => 401];
        }

        if (($user['status_user'] ?? '') !== 'ACTIVE') {
            return ['status' => false, 'message' => 'User tidak aktif.', 'code' => 403];
        }

        if ((string) ($user['password_user'] ?? '') !== (string) $password) {
            return ['status' => false, 'message' => 'Password salah.', 'code' => 401];
        }

        $isBypassUser = $this->isFirstLoginBypassUser((string) ($user['username_user'] ?? ''));
        $isFirstLogin = !$isBypassUser
            && trim((string) ($user['nik'] ?? '')) !== ''
            && trim((string) ($user['password_user'] ?? '')) === trim((string) ($user['nik'] ?? ''));

        if ($isFirstLogin) {
            return [
                'status' => false,
                'message' => 'Akun wajib ganti password pertama kali melalui website.',
                'code' => 403,
                'first_login_required' => true,
            ];
        }

        $validationRows = $this->db
            ->select('validation_user')
            ->from('tb_master_user_child')
            ->where('id_master_user', (int) $user['id'])
            ->get()
            ->result_array();

        $validationList = [];
        if (!empty($user['validation_user'])) {
            $validationList[] = trim((string) $user['validation_user']);
        }

        foreach ($validationRows as $row) {
            $value = trim((string) ($row['validation_user'] ?? ''));
            if ($value !== '') {
                $validationList[] = $value;
            }
        }

        $user['validation_user'] = array_values(array_unique(array_filter($validationList)));
        $user['validation'] = !empty($user['validation_user']) ? implode(', ', $user['validation_user']) : 'non';

        return ['status' => true, 'user' => $user];
    }

    private function reviewChecklistDocument($status)
    {
        if (!$this->allowMethods(['POST'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        if (!$this->isApprover() || !$this->hasMyrepPermission('APPROVAL')) {
            $this->json(false, 'Anda tidak memiliki akses review dokumen.', [], 403);
            return;
        }

        $this->load->model('MChecklist_Dokument_MyRep');

        $input = $this->getJsonInput();
        $fileId = (int) ($input['id_doc_file'] ?? $this->input->post('id_doc_file'));
        $remark = trim((string) ($input['remark'] ?? $this->input->post('remark')));

        if ($fileId <= 0) {
            $this->json(false, 'Dokumen tidak valid.', [], 422);
            return;
        }

        if ($status === 'REJECTED' && $remark === '') {
            $this->json(false, 'Remark wajib diisi saat reject dokumen.', [], 422);
            return;
        }

        if (strlen($remark) > 500) {
            $this->json(false, 'Remark maksimal 500 karakter.', [], 422);
            return;
        }

        $file = $this->MChecklist_Dokument_MyRep->getFileById($fileId);
        if (empty($file)) {
            $this->json(false, 'Dokumen tidak ditemukan atau tidak dapat diakses.', [], 404);
            return;
        }

        $updated = $this->MChecklist_Dokument_MyRep->updateFileStatus($fileId, [
            'status_file' => $status,
            'remark' => $remark,
            'approved_by' => (int) $user['id'],
        ]);

        if (!$updated) {
            $this->json(false, 'Status dokumen gagal diperbarui.', [], 500);
            return;
        }

        $this->json(true, $status === 'APPROVED' ? 'Dokumen berhasil di-approve.' : 'Dokumen berhasil di-reject.');
    }

    private function reviewBakDocument($status)
    {
        if (!$this->allowMethods(['POST'])) {
            return;
        }

        $user = $this->requireAuth();
        if (!$user) {
            return;
        }

        if (!$this->isApprover() || !$this->hasMyrepPermission('APPROVAL')) {
            $this->json(false, 'Anda tidak memiliki akses review dokumen BAK.', [], 403);
            return;
        }

        $this->load->model('MBAK_MyRep');

        $input = $this->getJsonInput();
        $fileId = (int) ($input['id_doc_file'] ?? $this->input->post('id_doc_file'));
        $remark = trim((string) ($input['remark'] ?? $this->input->post('remark')));

        if ($fileId <= 0) {
            $this->json(false, 'Dokumen BAK tidak valid.', [], 422);
            return;
        }
        if ($status === 'REJECTED' && $remark === '') {
            $this->json(false, 'Remark wajib diisi saat reject dokumen BAK.', [], 422);
            return;
        }
        if (strlen($remark) > 500) {
            $this->json(false, 'Remark maksimal 500 karakter.', [], 422);
            return;
        }

        $file = $this->MBAK_MyRep->getBakFileById($fileId);
        if (empty($file['id_myrep_cluster'])) {
            $this->json(false, 'Dokumen BAK tidak ditemukan atau tidak dapat diakses.', [], 404);
            return;
        }

        $updated = $this->MBAK_MyRep->updateBakFileStatus($fileId, [
            'status_file' => $status,
            'remark' => $remark,
            'approved_by' => (int) $user['id'],
        ]);

        if (!$updated) {
            $this->json(false, 'Status dokumen BAK gagal diperbarui.', [], 500);
            return;
        }

        $this->MBAK_MyRep->syncBakStatusByCluster((int) $file['id_myrep_cluster'], (int) $user['id']);
        $this->json(true, $status === 'APPROVED' ? 'Dokumen BAK berhasil di-approve.' : 'Dokumen BAK berhasil di-reject.');
    }

    private function handleChecklistFileUpload($clusterId, $packageId, $itemId, $docName)
    {
        $uploadDir = './uploads/checklist_myrep/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = (string) ($_FILES['file']['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        if (!in_array($extension, $allowedExtensions, true)) {
            return [
                'status' => false,
                'message' => 'Format file tidak didukung. Gunakan PDF, Word, Excel, JPG, atau PNG.',
            ];
        }

        $size = (int) ($_FILES['file']['size'] ?? 0);
        if ($size <= 0) {
            return ['status' => false, 'message' => 'File kosong atau tidak valid.'];
        }
        if ($size > 30 * 1024 * 1024) {
            return ['status' => false, 'message' => 'Ukuran file maksimal 30 MB.'];
        }

        $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName !== '' ? $docName : 'DOKUMEN');
        $fileName = 'DOC_' . (int) $clusterId . '_' . (int) $packageId . '_' . (int) $itemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

        $config = [
            'upload_path' => $uploadDir,
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
            'max_size' => 30720,
            'file_name' => $fileName,
            'overwrite' => true,
        ];

        $this->upload->initialize($config);
        if (!$this->upload->do_upload('file')) {
            return [
                'status' => false,
                'message' => strip_tags($this->upload->display_errors()),
            ];
        }

        $fileData = $this->upload->data();
        return [
            'status' => true,
            'file_name' => (string) $fileData['file_name'],
            'file_path' => 'uploads/checklist_myrep/' . (string) $fileData['file_name'],
        ];
    }

    private function handleMyrepFlowUpload($prefix, $relativeDir, $clusterId, $docItemId, $docName)
    {
        $uploadDir = './' . trim($relativeDir, '/') . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = (string) ($_FILES['file']['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        if (!in_array($extension, $allowedExtensions, true)) {
            return ['status' => false, 'message' => 'Format file tidak didukung.'];
        }

        $size = (int) ($_FILES['file']['size'] ?? 0);
        if ($size <= 0) {
            return ['status' => false, 'message' => 'File kosong atau tidak valid.'];
        }
        if ($size > 30 * 1024 * 1024) {
            return ['status' => false, 'message' => 'Ukuran file maksimal 30 MB.'];
        }

        $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName !== '' ? $docName : 'DOC');
        $fileName = strtoupper((string) $prefix) . '_' . (int) $clusterId . '_' . (int) $docItemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

        $config = [
            'upload_path' => $uploadDir,
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
            'max_size' => 30720,
            'file_name' => $fileName,
            'overwrite' => true,
        ];

        $this->upload->initialize($config);
        if (!$this->upload->do_upload('file')) {
            return ['status' => false, 'message' => strip_tags($this->upload->display_errors())];
        }

        $fileData = $this->upload->data();
        return [
            'status' => true,
            'file_name' => (string) $fileData['file_name'],
            'file_path' => trim($relativeDir, '/') . '/' . (string) $fileData['file_name'],
        ];
    }

    private function issueToken($userId, $deviceName = '')
    {
        $token = $this->createRandomToken();
        $now = date('Y-m-d H:i:s');

        $this->db->insert($this->tokenTable, [
            'user_id' => (int) $userId,
            'token_hash' => hash('sha256', $token),
            'device_name' => substr(trim((string) $deviceName), 0, 150),
            'ip_address' => substr((string) $this->input->ip_address(), 0, 45),
            'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
            'expires_at' => date('Y-m-d H:i:s', time() + $this->tokenTtlSeconds),
            'created_at' => $now,
            'last_used_at' => $now,
        ]);

        $this->db
            ->where('expires_at <', $now)
            ->where('revoked_at IS NULL', null, false)
            ->update($this->tokenTable, [
                'revoked_at' => $now,
            ]);

        return $token;
    }

    private function requireAuth()
    {
        $token = $this->getBearerToken();
        if ($token === '') {
            $this->json(false, 'Token tidak ditemukan.', [], 401);
            return null;
        }

        $this->currentTokenHash = hash('sha256', $token);
        $row = $this->db
            ->select('t.id AS token_id, t.expires_at, u.*, tl.*, COALESCE(u.nama_karyawan, "") AS nama_user, COALESCE(u.lokasi_kantor, u.homebase, "HO") AS lokasi_user, "" AS nama_jabatan', false)
            ->from($this->tokenTable . ' t')
            ->join('tb_master_user_new u', 'u.id = t.user_id', 'inner')
            ->join('tb_level tl', 'u.id_level = tl.id_level', 'left')
            ->where('t.token_hash', $this->currentTokenHash)
            ->where('t.revoked_at IS NULL', null, false)
            ->where('t.expires_at >=', date('Y-m-d H:i:s'))
            ->where('u.status_user', 'ACTIVE')
            ->limit(1)
            ->get()
            ->row_array();

        if (!$row) {
            $this->json(false, 'Token tidak valid atau sudah expired.', [], 401);
            return null;
        }

        $this->db
            ->where('id', (int) $row['token_id'])
            ->update($this->tokenTable, [
                'last_used_at' => date('Y-m-d H:i:s'),
                'ip_address' => substr((string) $this->input->ip_address(), 0, 45),
                'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
            ]);

        $validationRows = $this->db
            ->select('validation_user')
            ->from('tb_master_user_child')
            ->where('id_master_user', (int) $row['id'])
            ->get()
            ->result_array();

        $validationList = [];
        if (!empty($row['validation_user'])) {
            $validationList[] = trim((string) $row['validation_user']);
        }
        foreach ($validationRows as $validationRow) {
            $value = trim((string) ($validationRow['validation_user'] ?? ''));
            if ($value !== '') {
                $validationList[] = $value;
            }
        }
        $validationList = array_values(array_unique(array_filter($validationList)));

        $this->session->set_userdata([
            'id_user' => (int) $row['id'],
            'id_level' => isset($row['id_level']) ? (int) $row['id_level'] : 0,
            'nama_user' => (string) ($row['nama_user'] ?? ''),
            'username_user' => (string) ($row['username_user'] ?? ''),
            'password_user' => (string) ($row['password_user'] ?? ''),
            'lokasi_user' => (string) ($row['lokasi_user'] ?? ''),
            'nama_level' => (string) ($row['nama_level'] ?? ''),
            'validation' => !empty($validationList) ? implode(', ', $validationList) : 'non',
            'validation_user' => $validationList,
            'nama_jabatan' => (string) ($row['nama_jabatan'] ?? ''),
        ]);

        $row['validation_user'] = $validationList;
        return $row;
    }

    private function formatUser(array $user)
    {
        return [
            'id' => (int) ($user['id'] ?? 0),
            'name' => (string) ($user['nama_user'] ?? $user['nama_karyawan'] ?? ''),
            'username' => (string) ($user['username_user'] ?? ''),
            'level' => (string) ($user['nama_level'] ?? ''),
            'location' => (string) ($user['lokasi_user'] ?? $user['lokasi_kantor'] ?? $user['homebase'] ?? ''),
            'validation' => $user['validation_user'] ?? [],
        ];
    }

    private function formatChecklistSummary(array $row)
    {
        $uploaded = (int) ($row['doc_cw_atp_uploaded'] ?? 0)
            + (int) ($row['doc_full_opm_uploaded'] ?? 0)
            + (int) ($row['doc_rfs_uploaded'] ?? 0);
        $required = (int) ($row['doc_cw_atp_required'] ?? 0)
            + (int) ($row['doc_full_opm_required'] ?? 0)
            + (int) ($row['doc_rfs_required'] ?? 0);

        return [
            'id' => (int) ($row['id_cluster'] ?? 0),
            'clusterName' => (string) ($row['cluster_name'] ?? ''),
            'packageName' => (string) ($row['status_rfs'] ?? ''),
            'city' => (string) ($row['city_name'] ?? ''),
            'regional' => (string) ($row['regional_name'] ?? ''),
            'progressPercent' => $required > 0 ? (int) round(($uploaded / $required) * 100) : 0,
            'uploadedDocs' => $uploaded,
            'requiredDocs' => $required,
            'status' => (string) ($row['status_current'] ?? $row['status_rfs'] ?? ''),
            'tanggalRfs' => (string) ($row['tanggal_rfs'] ?? ''),
            'planAtpDate' => (string) ($row['plan_atp_date'] ?? ''),
            'actualAtpDate' => (string) ($row['actual_atp_date'] ?? ''),
            'updatedAt' => (string) ($row['actual_submit_doc_date'] ?? $row['approved_astri_date'] ?? ''),
        ];
    }

    private function buildMobileDashboardSummary(array $clusters)
    {
        $total = count($clusters);
        $completed = 0;
        $pending = 0;
        $onReview = 0;
        $rejected = 0;
        $uploadedDocs = 0;
        $requiredDocs = 0;

        foreach ($clusters as $cluster) {
            $summary = $this->formatChecklistSummary($cluster);
            $uploadedDocs += (int) $summary['uploadedDocs'];
            $requiredDocs += (int) $summary['requiredDocs'];

            if ((int) $summary['requiredDocs'] > 0 && (int) $summary['uploadedDocs'] >= (int) $summary['requiredDocs']) {
                $completed++;
            } else {
                $pending++;
            }

            $onReview += (int) ($cluster['doc_cw_atp_on_review'] ?? 0)
                + (int) ($cluster['doc_full_opm_on_review'] ?? 0)
                + (int) ($cluster['doc_rfs_on_review'] ?? 0);
            $rejected += (int) ($cluster['doc_cw_atp_rejected'] ?? 0)
                + (int) ($cluster['doc_full_opm_rejected'] ?? 0)
                + (int) ($cluster['doc_rfs_rejected'] ?? 0);
        }

        return [
            'clusters_total' => $total,
            'clusters_completed' => $completed,
            'clusters_pending' => $pending,
            'documents_uploaded' => $uploadedDocs,
            'documents_required' => $requiredDocs,
            'documents_progress_percent' => $requiredDocs > 0 ? (int) round(($uploadedDocs / $requiredDocs) * 100) : 0,
            'documents_on_review' => $onReview,
            'documents_rejected' => $rejected,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function formatBakCluster(array $row, array $documents = [])
    {
        $required = count($documents);
        $uploaded = 0;
        $approved = 0;
        $rejected = 0;
        foreach ($documents as $document) {
            $status = strtoupper(trim((string) ($document['status_file'] ?? '')));
            if (in_array($status, ['UPLOADED', 'APPROVED', 'REJECTED'], true) || (int) ($document['is_document_not_required'] ?? 0) === 1) {
                $uploaded++;
            }
            if ($status === 'APPROVED') {
                $approved++;
            }
            if ($status === 'REJECTED') {
                $rejected++;
            }
        }

        return [
            'id' => (int) ($row['id_myrep_cluster'] ?? 0),
            'clusterName' => (string) ($row['cluster_name'] ?? ''),
            'clusterCode' => (string) ($row['cluster_code'] ?? ''),
            'regional' => (string) ($row['regional_name'] ?? ''),
            'province' => (string) ($row['province_name'] ?? ''),
            'city' => (string) ($row['city_name'] ?? ''),
            'district' => (string) ($row['district_name'] ?? ''),
            'village' => (string) ($row['village_name'] ?? ''),
            'homepassBak' => (int) ($row['homepass_bak'] ?? $row['hp_plan'] ?? 0),
            'baOpenDate' => (string) ($row['ba_open_date'] ?? ''),
            'bakDate' => (string) ($row['bak_date'] ?? ''),
            'statusBak' => (string) ($row['status_bak'] ?? ''),
            'statusCurrent' => (string) ($row['status_current'] ?? ''),
            'remark' => (string) ($row['remark_bak'] ?? $row['remark_general'] ?? ''),
            'uploadedDocs' => $uploaded,
            'approvedDocs' => $approved,
            'rejectedDocs' => $rejected,
            'requiredDocs' => $required,
            'progressPercent' => $required > 0 ? (int) round(($approved / $required) * 100) : 0,
        ];
    }

    private function formatBakDocument(array $row)
    {
        return [
            'id_doc_file' => (int) ($row['id_doc_file'] ?? 0),
            'id_doc_item' => (int) ($row['id_doc_item'] ?? 0),
            'doc_name' => (string) ($row['doc_name'] ?? ''),
            'file_name' => (string) ($row['file_name'] ?? ''),
            'file_path' => (string) ($row['file_path'] ?? ''),
            'status_file' => (string) ($row['status_file'] ?? 'NOT UPLOADED'),
            'is_document_not_required' => (int) ($row['is_document_not_required'] ?? 0),
            'remark' => (string) ($row['remark'] ?? ''),
            'uploaded_at' => (string) ($row['uploaded_at'] ?? ''),
            'reviewed_at' => (string) ($row['reviewed_at'] ?? ''),
            'approved_at' => (string) ($row['approved_at'] ?? ''),
            'uploaded_by_name' => (string) ($row['uploaded_by_name'] ?? ''),
            'approved_by_name' => (string) ($row['approved_by_name'] ?? ''),
            'city_pic_approval_name' => (string) ($row['city_pic_approval_name'] ?? ''),
            'history' => is_array($row['history'] ?? null) ? $row['history'] : [],
        ];
    }

    private function formatFlowCluster(array $row, array $documents = [], $flow = '')
    {
        $required = count($documents);
        $uploaded = 0;
        $approved = 0;
        $rejected = 0;
        foreach ($documents as $document) {
            $status = strtoupper(trim((string) ($document['status_file'] ?? '')));
            if (in_array($status, ['UPLOADED', 'APPROVED', 'REJECTED'], true) || (int) ($document['is_document_not_required'] ?? 0) === 1) {
                $uploaded++;
            }
            if ($status === 'APPROVED') $approved++;
            if ($status === 'REJECTED') $rejected++;
        }

        if ($required <= 0) {
            $required = (int) ($row['doc_total'] ?? $row['post_doc_total'] ?? 0);
            $uploaded = max($uploaded, (int) ($row['doc_uploaded'] ?? $row['post_doc_uploaded'] ?? 0));
            $approved = max($approved, (int) ($row['doc_approved'] ?? $row['post_doc_approved'] ?? 0));
            $rejected = max($rejected, (int) ($row['doc_rejected'] ?? 0));
        }

        $status = (string) (
            $row['status_valsal']
            ?? $row['display_staging_status']
            ?? $row['staging_status']
            ?? $row['display_status_drm']
            ?? $row['status_drm']
            ?? $row['status_current']
            ?? ''
        );

        return [
            'id' => (int) ($row['id_myrep_cluster'] ?? 0),
            'flow' => $flow,
            'clusterName' => (string) ($row['cluster_name'] ?? ''),
            'clusterCode' => (string) ($row['cluster_code'] ?? ''),
            'regional' => (string) ($row['regional_name'] ?? ''),
            'province' => (string) ($row['province_name'] ?? ''),
            'city' => (string) ($row['city_name'] ?? ''),
            'district' => (string) ($row['district_name'] ?? ''),
            'village' => (string) ($row['village_name'] ?? ''),
            'homepass' => (int) ($row['homepass_valsal'] ?? $row['hp_donasi'] ?? $row['homepass_drm'] ?? $row['homepass_bak'] ?? $row['hp_plan'] ?? 0),
            'stageDate' => (string) ($row['valsal_date'] ?? $row['submission_date'] ?? $row['released_at'] ?? $row['drm_date'] ?? $row['bak_date'] ?? ''),
            'status' => $status,
            'statusCurrent' => (string) ($row['status_current'] ?? ''),
            'remark' => (string) ($row['remark_valsal'] ?? $row['remark_batch_approval'] ?? $row['remark_drm'] ?? $row['remark_general'] ?? ''),
            'uploadedDocs' => $uploaded,
            'approvedDocs' => $approved,
            'rejectedDocs' => $rejected,
            'requiredDocs' => $required,
            'progressPercent' => $required > 0 ? (int) round(($approved / $required) * 100) : 0,
            'extra' => [
                'bakDate' => (string) ($row['bak_date'] ?? ''),
                'valsalDate' => (string) ($row['valsal_date'] ?? ''),
                'submissionDate' => (string) ($row['submission_date'] ?? ''),
                'releasedAt' => (string) ($row['released_at'] ?? ''),
                'drmDate' => (string) ($row['drm_date'] ?? ''),
                'astriBatchNumber' => (string) ($row['astri_batch_number'] ?? ''),
                'namaOlt' => (string) ($row['nama_olt'] ?? ''),
            ],
        ];
    }

    private function formatFlowDocument(array $row)
    {
        return [
            'id_doc_file' => (int) ($row['id_doc_file'] ?? 0),
            'id_doc_item' => (int) ($row['id_doc_item'] ?? 0),
            'group_label' => (string) ($row['group_label'] ?? ''),
            'doc_name' => (string) ($row['doc_name'] ?? ''),
            'doc_requirement_note' => (string) ($row['doc_requirement_note'] ?? ''),
            'verification_team' => (string) ($row['verification_team'] ?? ''),
            'file_name' => (string) ($row['file_name'] ?? ''),
            'file_path' => (string) ($row['file_path'] ?? ''),
            'status_file' => (string) ($row['status_file'] ?? 'NOT UPLOADED'),
            'is_document_not_required' => (int) ($row['is_document_not_required'] ?? 0),
            'remark' => (string) ($row['remark'] ?? ''),
            'uploaded_at' => (string) ($row['uploaded_at'] ?? ''),
            'reviewed_at' => (string) ($row['reviewed_at'] ?? ''),
            'approved_at' => (string) ($row['approved_at'] ?? ''),
            'uploaded_by_name' => (string) ($row['uploaded_by_name'] ?? ''),
            'approved_by_name' => (string) ($row['approved_by_name'] ?? ''),
            'city_pic_approval_name' => (string) ($row['city_pic_approval_name'] ?? ''),
            'history' => is_array($row['history'] ?? null) ? $row['history'] : [],
        ];
    }

    private function formatAtpCluster(array $row)
    {
        return [
            'id' => (int) ($row['id_cluster'] ?? 0),
            'flow' => 'ATP',
            'clusterName' => (string) ($row['cluster_name'] ?? ''),
            'regional' => (string) ($row['regional_name'] ?? ''),
            'province' => (string) ($row['province_name'] ?? ''),
            'city' => (string) ($row['city_name'] ?? ''),
            'homepass' => (int) ($row['homepass'] ?? 0),
            'tanggalRfs' => (string) ($row['tanggal_rfs'] ?? ''),
            'emailAtpDate' => (string) ($row['email_atp_date'] ?? ''),
            'actualAtpDate' => (string) ($row['actual_atp_date'] ?? ''),
            'statusAtp' => (string) ($row['status_atp'] ?? ''),
            'status' => (string) ($row['stage_atp'] ?? ''),
            'recordPunclistFileId' => (int) ($row['record_punclist_file_id'] ?? 0),
            'recordPunclistFileName' => (string) ($row['record_punclist_file_name'] ?? ''),
            'baRectificationFileId' => (int) ($row['ba_rectification_file_id'] ?? 0),
            'baRectificationFileName' => (string) ($row['ba_rectification_file_name'] ?? ''),
            'uploadedDocs' => ((int) ($row['record_punclist_file_id'] ?? 0) > 0 ? 1 : 0) + ((int) ($row['ba_rectification_file_id'] ?? 0) > 0 ? 1 : 0),
            'approvedDocs' => ((int) ($row['record_punclist_file_id'] ?? 0) > 0 ? 1 : 0) + ((int) ($row['ba_rectification_file_id'] ?? 0) > 0 ? 1 : 0),
            'rejectedDocs' => 0,
            'requiredDocs' => 2,
            'progressPercent' => (int) round((((int) ($row['record_punclist_file_id'] ?? 0) > 0 ? 1 : 0) + ((int) ($row['ba_rectification_file_id'] ?? 0) > 0 ? 1 : 0)) / 2 * 100),
        ];
    }

    private function formatAtpDocuments(array $row)
    {
        return [
            [
                'id_doc_file' => (int) ($row['record_punclist_file_id'] ?? 0),
                'id_doc_item' => 0,
                'doc_type' => 'RECORD_PUNCLIST',
                'doc_name' => 'Record Punclist',
                'file_name' => (string) ($row['record_punclist_file_name'] ?? ''),
                'status_file' => (int) ($row['record_punclist_file_id'] ?? 0) > 0 ? 'UPLOADED' : 'NOT UPLOADED',
                'uploaded_at' => (string) ($row['record_punclist_uploaded_at'] ?? ''),
            ],
            [
                'id_doc_file' => (int) ($row['ba_rectification_file_id'] ?? 0),
                'id_doc_item' => 0,
                'doc_type' => 'BA_RECTIFICATION',
                'doc_name' => 'BA Rectification',
                'file_name' => (string) ($row['ba_rectification_file_name'] ?? ''),
                'status_file' => (int) ($row['ba_rectification_file_id'] ?? 0) > 0 ? 'UPLOADED' : 'NOT UPLOADED',
                'uploaded_at' => (string) ($row['ba_rectification_uploaded_at'] ?? ''),
            ],
        ];
    }

    private function normalizeLimit($value)
    {
        $limit = (int) $value;
        return $limit > 0 && $limit <= 50 ? $limit : 20;
    }

    private function buildPagination($page, $limit, $total)
    {
        return [
            'page' => (int) $page,
            'limit' => (int) $limit,
            'records_total' => (int) $total,
            'records_filtered' => (int) $total,
        ];
    }

    private function extractClusterIds(array $rows)
    {
        return array_values(array_filter(array_map(static function ($row) {
            return (int) ($row['id_myrep_cluster'] ?? 0);
        }, $rows)));
    }

    private function filterStageRowsBySearch(array $rows, $search)
    {
        $search = strtoupper(trim((string) $search));
        if ($search === '') {
            return $rows;
        }

        return array_values(array_filter($rows, static function ($row) use ($search) {
            $haystack = strtoupper(implode(' ', [
                $row['cluster_name'] ?? '',
                $row['cluster_code'] ?? '',
                $row['regional_name'] ?? '',
                $row['province_name'] ?? '',
                $row['city_name'] ?? '',
                $row['district_name'] ?? '',
                $row['village_name'] ?? '',
                $row['status_current'] ?? '',
                $row['status_valsal'] ?? '',
                $row['staging_status'] ?? '',
                $row['display_staging_status'] ?? '',
                $row['display_status_drm'] ?? '',
                $row['status_drm'] ?? '',
                $row['stage_atp'] ?? '',
            ]));
            return strpos($haystack, $search) !== false;
        }));
    }

    private function extractDistinctFromRows(array $rows, $column)
    {
        $values = [];
        foreach ($rows as $row) {
            $value = strtoupper(trim((string) ($row[$column] ?? '')));
            if ($value !== '') {
                $values[$value] = $value;
            }
        }
        return array_values($values);
    }

    private function extractBatchDocumentsFromRow(array $row)
    {
        $document = $this->normalizeBatchDocument($row);
        return empty($document['id_doc_item']) ? [] : [$document];
    }

    private function normalizeBatchDocument(array $row)
    {
        return [
            'id_doc_file' => (int) ($row['id_doc_file'] ?? $row['batch_doc_file_id'] ?? 0),
            'id_doc_item' => (int) ($row['id_doc_item'] ?? $row['batch_doc_item_id'] ?? 0),
            'group_label' => (string) ($row['group_label'] ?? 'RAR'),
            'doc_name' => (string) ($row['doc_name'] ?? 'RAR'),
            'file_name' => (string) ($row['file_name'] ?? $row['batch_doc_file_name'] ?? ''),
            'file_path' => (string) ($row['file_path'] ?? $row['batch_doc_file_path'] ?? ''),
            'status_file' => (string) ($row['status_file'] ?? $row['batch_doc_status'] ?? 'NOT UPLOADED'),
            'is_document_not_required' => (int) ($row['is_document_not_required'] ?? $row['batch_doc_not_required'] ?? 0),
            'remark' => (string) ($row['remark'] ?? $row['batch_doc_remark'] ?? ''),
            'uploaded_at' => (string) ($row['uploaded_at'] ?? ''),
            'reviewed_at' => (string) ($row['reviewed_at'] ?? $row['batch_doc_reviewed_at'] ?? ''),
            'approved_at' => (string) ($row['approved_at'] ?? $row['batch_doc_approved_at'] ?? ''),
            'uploaded_by_name' => (string) ($row['uploaded_by_name'] ?? $row['batch_doc_uploaded_by_name'] ?? ''),
        ];
    }

    private function getJsonInput()
    {
        $raw = (string) $this->input->raw_input_stream;
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function getBearerToken()
    {
        $header = '';
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = (string) $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $header = (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $header = (string) $headers['Authorization'];
            }
        }

        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return trim((string) $matches[1]);
        }

        return '';
    }

    private function createRandomToken()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(32));
        }

        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    private function allowMethods(array $methods)
    {
        $method = strtoupper((string) $this->input->method(true));
        if ($method === 'OPTIONS') {
            $this->json(true, 'OK');
            return false;
        }

        if (!in_array($method, $methods, true)) {
            $this->json(false, 'Method tidak diizinkan.', [], 405);
            return false;
        }

        return true;
    }

    private function json($status, $message, array $payload = [], $statusCode = 200)
    {
        $body = array_merge([
            'status' => (bool) $status,
            'message' => (string) $message,
        ], $payload);

        $this->output
            ->set_status_header((int) $statusCode)
            ->set_content_type('application/json')
            ->set_header('Access-Control-Allow-Origin: *')
            ->set_header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept')
            ->set_header('Access-Control-Allow-Methods: GET, POST, OPTIONS')
            ->set_output(json_encode($body));
    }

    private function hasMyrepPermission($actionKey)
    {
        if (!isset($this->myrepAccess)) {
            return true;
        }

        return (bool) $this->myrepAccess->hasPermission('Checklist_Dokument_MyRep', $actionKey);
    }

    private function isApprover()
    {
        return $this->session->userdata('lokasi_user') === 'HO'
            || $this->session->userdata('nama_level') === 'Super Admin';
    }

    private function normalizeDateInput($date)
    {
        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function ensureTokenTable()
    {
        if ($this->db->table_exists($this->tokenTable)) {
            return;
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->tokenTable}` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `token_hash` CHAR(64) NOT NULL,
            `device_name` VARCHAR(150) DEFAULT NULL,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `user_agent` VARCHAR(255) DEFAULT NULL,
            `expires_at` DATETIME NOT NULL,
            `last_used_at` DATETIME DEFAULT NULL,
            `revoked_at` DATETIME DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `ux_mobile_token_hash` (`token_hash`),
            KEY `idx_mobile_token_user` (`user_id`, `revoked_at`, `expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }

    private function isFirstLoginBypassUser($username)
    {
        $username = strtolower(trim((string) $username));
        if ($username === '') {
            return false;
        }

        $raw = (string) $this->readEnvValue('FIRST_LOGIN_BYPASS_USERS', '');
        if ($raw === '') {
            return false;
        }

        $items = array_filter(array_map('trim', explode(',', $raw)));
        foreach ($items as $item) {
            if ($username === strtolower($item)) {
                return true;
            }
        }

        return false;
    }

    private function readEnvValue($key, $default = '')
    {
        if ($key === '') {
            return $default;
        }

        $envValue = getenv($key);
        if ($envValue !== false && is_scalar($envValue)) {
            return (string) $envValue;
        }

        $envFile = FCPATH . '.env';
        if (!is_file($envFile)) {
            return $default;
        }

        $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return $default;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2 || trim($parts[0]) !== $key) {
                continue;
            }

            $value = trim($parts[1]);
            $first = substr($value, 0, 1);
            $last = substr($value, -1);
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }

            return $value;
        }

        return $default;
    }
}
