<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ATP_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MATP_MyRep');
        $this->load->model('MChecklist_Dokument_MyRep');
        $this->load->model('MMonitoring_RFS_MyRep');
        $this->load->library('upload');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('ATP_MyRep');
            $this->myrepAccess->enforceByMethod('ATP_MyRep', (string) $this->router->fetch_method(), [
                'save' => 'EDIT',
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
        $selectedRegional = strtoupper(trim((string) $this->input->get('regional')));
        $selectedStage = trim((string) $this->input->get('stage'));
        $stageOptions = $this->MATP_MyRep->getStageOptions();
        if ($selectedStage !== '' && !in_array($selectedStage, $stageOptions, true)) {
            $selectedStage = '';
        }

        $this->MMonitoring_RFS_MyRep->syncMyrepCompatibilityBridge((int) date('Y'), (int) date('n'), $selectedCity);

        $schemaReady = $this->MATP_MyRep->supportsAtpColumns() && $this->MATP_MyRep->supportsAtpFileTable();

        $data['title'] = 'ATP MYREP';
        $data['schemaReady'] = $schemaReady;
        $data['selectedCity'] = $selectedCity;
        $data['selectedRegional'] = $selectedRegional;
        $data['selectedStage'] = $selectedStage;
        $data['stageOptions'] = $stageOptions;
        $data['cityOptions'] = $this->MATP_MyRep->getCityOptions();
        $data['regionalOptions'] = $this->MATP_MyRep->getRegionalOptions();
        $data['clusterList'] = $schemaReady
            ? $this->MATP_MyRep->getClusterRows($selectedCity, $selectedRegional, $selectedStage)
            : [];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('ATP_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function save()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->post('filter_city')));
        $selectedRegional = strtoupper(trim((string) $this->input->post('filter_regional')));
        $selectedStage = trim((string) $this->input->post('filter_stage'));

        if (!$this->MATP_MyRep->supportsAtpColumns() || !$this->MATP_MyRep->supportsAtpFileTable()) {
            $this->session->set_flashdata('error', 'Kolom ATP belum tersedia. Jalankan query patch ATP terlebih dahulu.');
            redirect($this->buildRedirectUrl($selectedCity, $selectedRegional, $selectedStage));
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $emailAtpDate = $this->normalizeDateInput($this->input->post('email_atp_date'));
        $actualAtpDate = $this->normalizeDateInput($this->input->post('actual_atp_date'));
        $statusAtp = strtoupper(trim((string) $this->input->post('status_atp')));
        $allowedStatuses = ['', 'PUNCLIST', 'DONE'];

        if ($clusterId <= 0) {
            $this->session->set_flashdata('error', 'Cluster ATP tidak valid.');
            redirect($this->buildRedirectUrl($selectedCity, $selectedRegional, $selectedStage));
            return;
        }

        if (!in_array($statusAtp, $allowedStatuses, true)) {
            $this->session->set_flashdata('error', 'Status ATP tidak dikenali.');
            redirect($this->buildRedirectUrl($selectedCity, $selectedRegional, $selectedStage));
            return;
        }

        if ($actualAtpDate !== null && $emailAtpDate === null) {
            $this->session->set_flashdata('error', 'Tanggal email ATP wajib diisi sebelum tanggal ATP.');
            redirect($this->buildRedirectUrl($selectedCity, $selectedRegional, $selectedStage));
            return;
        }

        if ($statusAtp !== '' && $actualAtpDate === null) {
            $this->session->set_flashdata('error', 'Tanggal ATP wajib diisi sebelum status ATP.');
            redirect($this->buildRedirectUrl($selectedCity, $selectedRegional, $selectedStage));
            return;
        }

        $cluster = $this->MATP_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data cluster tidak ditemukan.');
            redirect($this->buildRedirectUrl($selectedCity, $selectedRegional, $selectedStage));
            return;
        }

        $existingStatusAtp = strtoupper(trim((string) ($cluster['status_atp'] ?? '')));
        $recordPunclistExists = $this->MATP_MyRep->hasAtpDocument($clusterId, 'RECORD_PUNCLIST');
        $baRectificationExists = $this->MATP_MyRep->hasAtpDocument($clusterId, 'BA_RECTIFICATION');
        $hasRecordPunclistUpload = !empty($_FILES['record_punclist_file']['name']);
        $hasBaRectificationUpload = !empty($_FILES['ba_rectification_file']['name']);

        if ($statusAtp === 'PUNCLIST' && !$recordPunclistExists && !$hasRecordPunclistUpload) {
            $this->session->set_flashdata('error', 'Status ATP PUNCLIST wajib upload Record Punclist.');
            redirect($this->buildRedirectUrl($selectedCity, $selectedRegional, $selectedStage));
            return;
        }

        if ($existingStatusAtp === 'PUNCLIST' && $statusAtp === 'DONE' && !$baRectificationExists && !$hasBaRectificationUpload) {
            $this->session->set_flashdata('error', 'Perubahan ATP dari PUNCLIST ke DONE wajib upload BA Rectification.');
            redirect($this->buildRedirectUrl($selectedCity, $selectedRegional, $selectedStage));
            return;
        }

        $this->MChecklist_Dokument_MyRep->ensureClusterPackages($clusterId, $cluster['tanggal_rfs'] ?? null);
        $this->MChecklist_Dokument_MyRep->updateClusterTimeline($clusterId, [
            'actual_atp_date' => $actualAtpDate,
            'updated_by' => (int) $this->session->userdata('id_user'),
        ]);

        $uploadError = $this->handleAtpEvidenceUpload($clusterId, 'record_punclist_file', 'RECORD_PUNCLIST', trim((string) $this->input->post('record_punclist_remark')));
        if ($uploadError !== '') {
            $this->session->set_flashdata('error', $uploadError);
            redirect($this->buildRedirectUrl($selectedCity, $selectedRegional, $selectedStage));
            return;
        }

        $uploadError = $this->handleAtpEvidenceUpload($clusterId, 'ba_rectification_file', 'BA_RECTIFICATION', trim((string) $this->input->post('ba_rectification_remark')));
        if ($uploadError !== '') {
            $this->session->set_flashdata('error', $uploadError);
            redirect($this->buildRedirectUrl($selectedCity, $selectedRegional, $selectedStage));
            return;
        }

        $this->MATP_MyRep->updateClusterAtpMetadata($clusterId, $emailAtpDate, $statusAtp);

        $this->session->set_flashdata('success', 'Data ATP cluster berhasil diperbarui.');
        redirect($this->buildRedirectUrl($selectedCity, $selectedRegional, $selectedStage));
    }

    public function previewFile($fileId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $file = $this->MATP_MyRep->getAtpFileById((int) $fileId);
        if (empty($file) || empty($file['file_path'])) {
            show_404();
            return;
        }

        $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file['file_path']);
        if (!is_file($fullPath)) {
            show_404();
            return;
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        header('Content-Type: ' . ($mimeMap[$extension] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    private function normalizeDateInput($date)
    {
        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        return $date;
    }

    private function buildRedirectUrl($city = '', $regional = '', $stage = '')
    {
        $params = [];
        if ($regional !== '') {
            $params['regional'] = $regional;
        }
        if ($city !== '') {
            $params['city'] = $city;
        }
        if ($stage !== '') {
            $params['stage'] = $stage;
        }

        $query = http_build_query($params);
        return $query !== '' ? 'ATP_MyRep?' . $query : 'ATP_MyRep';
    }

    private function handleAtpEvidenceUpload($clusterId, $inputName, $docType, $remark)
    {
        if (empty($_FILES[$inputName]['name'])) {
            return '';
        }

        $uploadDir = './uploads/atp_myrep/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
        $safeDocType = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtoupper($docType));
        $fileName = 'ATP_' . (int) $clusterId . '_' . $safeDocType . '_' . date('YmdHis') . '.' . $extension;
        $config = [
            'upload_path' => $uploadDir,
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
            'max_size' => 30720,
            'file_name' => $fileName,
            'overwrite' => true,
        ];

        $this->upload->initialize($config);
        if (!$this->upload->do_upload($inputName)) {
            return strip_tags($this->upload->display_errors());
        }

        $fileData = $this->upload->data();
        $this->MATP_MyRep->saveAtpFileUpload([
            'cluster_id' => (int) $clusterId,
            'doc_type' => $docType,
            'file_name' => $fileData['file_name'],
            'file_path' => 'uploads/atp_myrep/' . $fileData['file_name'],
            'remark' => $remark,
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ]);

        return '';
    }
}
