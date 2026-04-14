<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Checklist_Dokument_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MChecklist_Dokument_MyRep');
        $this->load->library('upload');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));

        $data['title'] = 'Checklist Dokument';
        $data['selectedCity'] = $selectedCity;
        $data['cityOptions'] = $this->MChecklist_Dokument_MyRep->getCityOptions();
        $data['clusterList'] = $this->MChecklist_Dokument_MyRep->getFullRfsClusters($selectedCity);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Checklist_Dokument_MyRep/index', $data);
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
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $cluster = $this->MChecklist_Dokument_MyRep->getClusterDetail($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Data cluster tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $this->MChecklist_Dokument_MyRep->ensureClusterPackages($clusterId, $cluster['tanggal_rfs'] ?? null);
        $cluster = $this->MChecklist_Dokument_MyRep->getClusterDetail($clusterId);

        $data['title'] = 'Checklist Dokument Detail';
        $data['cluster'] = $cluster;
        $data['scopeTabs'] = $this->MChecklist_Dokument_MyRep->getClusterScopeTabs($clusterId);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Checklist_Dokument_MyRep/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveTimeline()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $packageId = (int) $this->input->post('id_doc_package');
        if ($clusterId <= 0 || $packageId <= 0) {
            $this->session->set_flashdata('error', 'Data timeline tidak valid.');
            redirect('Checklist_Dokument_MyRep');
            return;
        }

        $payload = [
            'tanggal_rfs' => $this->normalizeDateInput($this->input->post('tanggal_rfs')),
            'actual_atp_date' => $this->normalizeDateInput($this->input->post('actual_atp_date')),
            'remarks' => trim((string) $this->input->post('remarks')),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ];

        $this->MChecklist_Dokument_MyRep->updatePackageTimeline($packageId, $payload);
        $this->session->set_flashdata('success', 'Timeline berhasil diperbarui.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function uploadDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $packageId = (int) $this->input->post('id_doc_package');
        $itemId = (int) $this->input->post('id_doc_item');
        $docName = trim((string) $this->input->post('doc_name'));

        if ($clusterId <= 0 || $packageId <= 0 || $itemId <= 0 || empty($_FILES['file']['name'])) {
            $this->session->set_flashdata('error', 'Data upload dokumen belum lengkap.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $uploadDir = './uploads/checklist_myrep/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $safeDocName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName);
        $fileName = 'DOC_' . $clusterId . '_' . $packageId . '_' . $itemId . '_' . $safeDocName . '_' . date('YmdHis') . '.' . $extension;

        $config = [
            'upload_path' => $uploadDir,
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
            'max_size' => 10240,
            'file_name' => $fileName,
            'overwrite' => true,
        ];

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file')) {
            $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $fileData = $this->upload->data();
        $payload = [
            'id_doc_package' => $packageId,
            'id_doc_item' => $itemId,
            'file_name' => $fileData['file_name'],
            'file_path' => 'uploads/checklist_myrep/' . $fileData['file_name'],
            'status_file' => 'UPLOADED',
            'remark' => trim((string) $this->input->post('remark')),
            'uploaded_by' => (int) $this->session->userdata('id_user'),
        ];

        $this->MChecklist_Dokument_MyRep->saveFileUpload($payload);
        $this->session->set_flashdata('success', 'Dokumen berhasil diupload.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    public function approveDocument($fileId = 0, $clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses approve dokumen.');
            redirect('Checklist_Dokument_MyRep/detail/' . (int) $clusterId);
            return;
        }

        $this->MChecklist_Dokument_MyRep->updateFileStatus((int) $fileId, [
            'status_file' => 'APPROVED',
            'remark' => '',
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata('success', 'Dokumen berhasil di-approve.');
        redirect('Checklist_Dokument_MyRep/detail/' . (int) $clusterId);
    }

    public function rejectDocument()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        if (!$this->isApprover()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses reject dokumen.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $fileId = (int) $this->input->post('id_doc_file');
        $remark = trim((string) $this->input->post('remark'));
        if ($fileId <= 0) {
            $this->session->set_flashdata('error', 'Dokumen tidak ditemukan.');
            redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
            return;
        }

        $this->MChecklist_Dokument_MyRep->updateFileStatus($fileId, [
            'status_file' => 'REJECTED',
            'remark' => $remark,
            'approved_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata('success', 'Dokumen berhasil di-reject.');
        redirect('Checklist_Dokument_MyRep/detail/' . $clusterId);
    }

    private function normalizeDateInput($date)
    {
        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        return $date;
    }

    private function isApprover()
    {
        return $this->session->userdata('lokasi_user') === 'HO'
            || $this->session->userdata('nama_level') === 'Super Admin';
    }
}
