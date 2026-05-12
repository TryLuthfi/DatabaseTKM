<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kontrak_Payung extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MKontrak_Payung');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $jenisFilter = trim((string) $this->input->get('jenis'));
        $data['title'] = 'Kontrak Payung (PKS)';
        $data['jenisFilter'] = $jenisFilter;
        $data['pksRows'] = $this->MKontrak_Payung->getPksRows($jenisFilter);
        $data['openDetailId'] = (int) $this->input->get('detail');

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Kontrak_Payung/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function save()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idPks = (int) $this->input->post('id_pks');
        $tanggalPks = trim((string) $this->input->post('tanggal_pks'));
        $picPks = trim((string) $this->input->post('pic_pks'));
        $jenisPks = strtoupper(trim((string) $this->input->post('jenis_pks')));
        $noTelp = trim((string) $this->input->post('no_telp'));
        $emailPic = trim((string) $this->input->post('email_pic'));
        $ttl = trim((string) $this->input->post('ttl'));
        $noKtp = trim((string) $this->input->post('no_ktp'));
        $alamatKtp = trim((string) $this->input->post('alamat_ktp'));
        $gradePic = strtoupper(trim((string) $this->input->post('grade_pic')));
        $areaPic = trim((string) $this->input->post('area_pic'));
        $tocPks = '';
        $statusPks = strtolower(trim((string) $this->input->post('status_pks')));

        if ($tanggalPks === '' || $picPks === '' || !in_array($jenisPks, ['MDR', 'MPL', 'PKS'], true)) {
            $this->session->set_flashdata('error', 'Tanggal, PIC, dan jenis PKS wajib valid.');
            redirect('Kontrak_Payung');
            return;
        }

        $tocPks = $this->buildTocFromTanggalPks($tanggalPks);

        if (!in_array($statusPks, ['active', 'non aktif'], true)) {
            $statusPks = 'active';
        }

        if ($idPks > 0) {
            $existingPks = $this->MKontrak_Payung->getPksById($idPks);
            if (empty($existingPks)) {
                $this->session->set_flashdata('error', 'Data PKS tidak ditemukan.');
                redirect('Kontrak_Payung');
                return;
            }

            if ((string) ($existingPks['workflow_status'] ?? '') === 'approved') {
                $this->session->set_flashdata('error', 'PKS yang sudah approved dikunci dan tidak bisa diedit.');
                $this->redirectToIndexDetail($idPks);
                return;
            }
        }

        $nomorPksData = $idPks > 0 ? null : $this->MKontrak_Payung->generateNomorPks($jenisPks, $tanggalPks);

        $payload = [
            'tanggal_pks' => $tanggalPks,
            'pic_pks' => $picPks,
            'jenis_pks' => $jenisPks,
            'no_telp' => $noTelp,
            'email_pic' => $emailPic,
            'no_ktp' => $noKtp,
            'alamat_ktp' => $alamatKtp,
            'grade_pic' => $gradePic,
            'area_pic' => $areaPic,
            'toc_pks' => $tocPks,
            'status_pks' => $statusPks,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($this->db->field_exists('ttl', 'tb_logistik_pks')) {
            $payload['ttl'] = $ttl;
        }

        if ($idPks <= 0) {
            $payload['nomor_pks'] = $nomorPksData['nomor'];
            $payload['pks_seq'] = $nomorPksData['seq'];
            $payload['bulan_doc'] = $nomorPksData['bulan'];
            $payload['tahun_doc'] = $nomorPksData['tahun'];
            $payload['workflow_status'] = 'draft';
            $payload['created_at'] = date('Y-m-d H:i:s');
            $payload['created_by'] = (int) $this->session->userdata('id_user');
        } else {
            // Setiap perubahan draft/revisi harus kembali menunggu approval.
            $payload['workflow_status'] = 'draft';
            $payload['approval_note'] = null;
            $payload['approved_by'] = null;
            $payload['approved_at'] = null;
        }

        $savedId = $this->MKontrak_Payung->savePks($payload, $idPks > 0 ? $idPks : null);
        $this->session->set_flashdata('success', $idPks > 0 ? 'PKS berhasil diperbarui.' : 'PKS berhasil dibuat.');
        $this->redirectToIndexDetail($savedId);
    }

    public function detail($idPks)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $pks = $this->MKontrak_Payung->getPksById((int) $idPks);
        if (empty($pks)) {
            $this->session->set_flashdata('error', 'Data PKS tidak ditemukan.');
            redirect('Kontrak_Payung');
            return;
        }

        $data['title'] = 'Detail PKS';
        $data['pks'] = $pks;
        $data['spkRows'] = $this->MKontrak_Payung->getSpkRows((int) $idPks);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Kontrak_Payung/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function print_doc($idPks, $docType = 'doc1')
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $pks = $this->MKontrak_Payung->getPksById((int) $idPks);
        if (empty($pks)) {
            show_error('PKS tidak ditemukan.', 404);
            return;
        }

        $data = [
            'title' => 'Print PKS',
            'pks' => $pks,
            'docType' => $docType === 'doc2' ? 'doc2' : 'doc1',
        ];
        $viewName = $this->resolvePrintViewName($pks, $data['docType']);
        $this->load->view($viewName, $data);
    }

    public function upload_signed($idPks)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $docType = trim((string) $this->input->post('doc_type'));
        $field = $docType === 'doc2' ? 'signed_doc_2' : 'signed_doc_1';

        if (empty($_FILES['signed_file']['name'])) {
            $this->session->set_flashdata('error', 'File signed wajib dipilih.');
            $this->redirectToIndexDetail((int) $idPks);
            return;
        }

        $targetPath = FCPATH . 'uploads/pks_signed';
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true);
        }

        $config = [
            'upload_path' => $targetPath,
            'allowed_types' => 'pdf|jpg|jpeg|png',
            'max_size' => 10240,
            'file_name' => 'PKS_' . (int) $idPks . '_' . $field . '_' . date('YmdHis'),
        ];

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('signed_file')) {
            $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
            $this->redirectToIndexDetail((int) $idPks);
            return;
        }

        $fileData = $this->upload->data();
        $this->MKontrak_Payung->setPksSignedDoc((int) $idPks, $field, (string) $fileData['file_name']);
        $this->session->set_flashdata('success', 'Dokumen signed berhasil diupload.');
        $this->redirectToIndexDetail((int) $idPks);
    }

    public function submit_approval($idPks)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $pks = $this->MKontrak_Payung->getPksById((int) $idPks);
        if (empty($pks)) {
            $this->session->set_flashdata('error', 'PKS tidak ditemukan.');
            redirect('Kontrak_Payung');
            return;
        }

        if (empty($pks['signed_doc_1']) || empty($pks['signed_doc_2'])) {
            $this->session->set_flashdata('error', 'Kedua dokumen signed wajib terupload sebelum submit approval.');
            $this->redirectToIndexDetail((int) $idPks);
            return;
        }

        if (!in_array((string) ($pks['workflow_status'] ?? ''), ['draft', 'rejected'], true)) {
            $this->session->set_flashdata('error', 'Status PKS tidak valid untuk dikirim ke approval.');
            $this->redirectToIndexDetail((int) $idPks);
            return;
        }

        $this->MKontrak_Payung->updatePksApproval((int) $idPks, 'waiting_approval', '', null);
        $this->session->set_flashdata('success', 'PKS masuk antrian approval.');
        $this->redirectToIndexDetail((int) $idPks);
    }

    public function approve($idPks)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $pks = $this->MKontrak_Payung->getPksById((int) $idPks);
        if (empty($pks)) {
            $this->session->set_flashdata('error', 'PKS tidak ditemukan.');
            redirect('Kontrak_Payung');
            return;
        }

        if (empty($pks['signed_doc_1']) || empty($pks['signed_doc_2'])) {
            $this->session->set_flashdata('error', 'Dokumen signed 1 dan 2 wajib ada sebelum approve.');
            $this->redirectToIndexDetail((int) $idPks);
            return;
        }

        if ((string) ($pks['workflow_status'] ?? '') !== 'waiting_approval') {
            $this->session->set_flashdata('error', 'PKS harus berstatus waiting approval sebelum di-approve.');
            $this->redirectToIndexDetail((int) $idPks);
            return;
        }

        $this->MKontrak_Payung->updatePksApproval((int) $idPks, 'approved', '', (int) $this->session->userdata('id_user'));
        $this->session->set_flashdata('success', 'PKS berhasil di-approve.');
        $this->redirectToIndexDetail((int) $idPks);
    }

    public function reject($idPks)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $pks = $this->MKontrak_Payung->getPksById((int) $idPks);
        if (empty($pks)) {
            $this->session->set_flashdata('error', 'PKS tidak ditemukan.');
            redirect('Kontrak_Payung');
            return;
        }

        if ((string) ($pks['workflow_status'] ?? '') !== 'waiting_approval') {
            $this->session->set_flashdata('error', 'PKS harus berstatus waiting approval sebelum di-reject.');
            $this->redirectToIndexDetail((int) $idPks);
            return;
        }

        $note = trim((string) $this->input->post('approval_note'));
        if ($note === '') {
            $this->session->set_flashdata('error', 'Catatan reject wajib diisi.');
            $this->redirectToIndexDetail((int) $idPks);
            return;
        }

        $this->MKontrak_Payung->updatePksApproval((int) $idPks, 'rejected', $note, (int) $this->session->userdata('id_user'));
        $this->session->set_flashdata('success', 'PKS berhasil di-reject.');
        $this->redirectToIndexDetail((int) $idPks);
    }

    public function detail_json($idPks)
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->output->set_status_header(401)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
            return;
        }

        $pks = $this->MKontrak_Payung->getPksById((int) $idPks);
        if (empty($pks)) {
            $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'PKS tidak ditemukan']));
            return;
        }

        $spkRows = $this->MKontrak_Payung->getSpkRows((int) $idPks);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'pks' => $pks,
                'spk_rows' => $spkRows,
            ]));
    }

    private function redirectToIndexDetail($idPks)
    {
        redirect('Kontrak_Payung?detail=' . (int) $idPks);
    }

    private function buildTocFromTanggalPks($tanggalPks)
    {
        $date = DateTime::createFromFormat('Y-m-d', (string) $tanggalPks);
        if (!$date) {
            return '';
        }

        $date->modify('+1 year');
        return $date->format('d-m-Y');
    }

    private function resolvePrintViewName(array $pks, $docType)
    {
        $jenisPks = strtoupper((string) ($pks['jenis_pks'] ?? ''));
        $safeDocType = $docType === 'doc2' ? 'doc2' : 'doc1';
        $viewName = 'format_pks_print';

        // Mapping template print per jenis PKS + lampiran.
        if ($jenisPks === 'MDR' && $safeDocType === 'doc1') {
            return 'format_pks_mdr_lampiran1';
        }
        if ($jenisPks === 'MDR' && $safeDocType === 'doc2') {
            return 'format_pks_mdr_lampiran2';
        }

        if ($jenisPks === 'MPL' && $safeDocType === 'doc1') {
            return 'format_pks_mpl_lampiran1';
        }

        return $viewName;
    }
}
