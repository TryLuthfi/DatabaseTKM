<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logistik_Purchase_Request extends CI_Controller
{
    private $approvalMap = [
        'sm' => 'approved_sm',
        'rpm' => 'approved_rpm',
        'planning' => 'approved_planning',
        'manager_konstruksi' => 'approved_manager_konstruksi',
        'finance' => 'approved_finance',
        'gm' => 'approved_gm',
        'manager_logistik' => 'approved_manager_logistik',
        'direktur' => 'approved_direktur',
    ];
    private $nodinApprovalMap = [
        'manager_logistik' => 'approved_manager_logistik',
        'purchasing' => 'approved_purchasing',
        'general_manager_project' => 'approved_gm_project',
        'general_manager_finance' => 'approved_gm_finance',
        'direktur' => 'approved_direktur',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MLogistik_Purchase_Request');
        $this->load->model('MDashboard_Logistik_Stok');
        $this->load->model('MLogistik_Pesanan_Pabrik');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Purchase Request';
            $data['list_purchase_request_area'] = $this->MLogistik_Purchase_Request->decorate_purchase_request_rows(
                $this->MLogistik_Purchase_Request->get_all_purchase_request('area')
            );
            $data['list_purchase_request_ho'] = $this->MLogistik_Purchase_Request->decorate_purchase_request_rows(
                $this->MLogistik_Purchase_Request->get_all_purchase_request('ho')
            );
            $data['list_master_gudang'] = $this->MLogistik_Purchase_Request->get_all_gudang();
            $data['get_master_project'] = $this->MDashboard_Logistik_Stok->getMasterProject();
            $data['available_approval_columns'] = $this->MLogistik_Purchase_Request->resolve_available_approval_columns();
            $data['masterPabrikOptions'] = $this->MLogistik_Pesanan_Pabrik->getMasterPabrikActive();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('logistik_purchase_request', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function add_purchase_request()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $id_purchase_request = $this->generateUniqId('PR');

            $data_head = [
                'id_purchase_request' => $id_purchase_request,
                'nomor_purchase_request' => $this->input->post('nomor_pr'),
                'tanggal_pembuatan' => $this->input->post('tanggal_upload_pr'),
                'id_project' => $this->input->post('nama_bowher'),
                'lokasi_project' => $this->input->post('lokasi_project'),
                'pembuat' => $this->session->userdata('id_user'),
                'tanggal_estimasi_pengiriman' => $this->input->post('tanggal_pengiriman'),
                'nama_project' => $this->input->post('nama_project'),
                'nomer_sp' => $this->input->post('nomor_sp'),
                'tanggal_sp' => $this->input->post('tanggal_sp'),
            ];

            $data_child = [];

            foreach ($this->input->post('id_kode_item_') as $key => $value) {
                $data_child[] = [
                    'id_purchase_request_detail' => $this->generateUniqId('PR' . $key),
                    'id_purchase_request' => $id_purchase_request,
                    'id_kode_item' => $this->input->post('id_kode_item_')[$key],
                    'boq' => $this->input->post('boq_')[$key] ?? 0,
                    'stok_area' => $this->input->post('stok_area_')[$key],
                    'qty_request' => $this->input->post('stok_request_')[$key],
                    'keterangan' => $this->input->post('keterangan_')[$key],
                ];
            }

            $this->db->trans_start();
            $this->db->insert('tb_logistik_purchase_request', $data_head);
            if (!empty($data_child)) {
                $this->db->insert_batch('tb_logistik_purchase_request_detail', $data_child);
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->session->set_flashdata('error', 'Gagal menyimpan data purchase request. Silakan coba lagi.');
            } else {
                $this->session->set_flashdata('success', 'Berhasil Menyimpan Data!');
            }

            redirect('Logistik_Purchase_Request/index/');
        } else {
            redirect('Auth');
        }
    }

    public function get_material_options()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $projectItem = trim((string) $this->input->get('id_bowheer'));
        $idLokasiGudang = $this->input->get('id_lokasi_gudang');

        if ($projectItem === '') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([]));
            return;
        }

        $data = $this->MLogistik_Purchase_Request->get_material_options($projectItem, $idLokasiGudang);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    public function delete_purchase_request($id_purchase_request)
    {
        $this->db->trans_start();
        $this->db->delete('tb_logistik_purchase_request_detail', ['id_purchase_request' => $id_purchase_request]);
        $this->db->delete('tb_logistik_purchase_request', ['id_purchase_request' => $id_purchase_request]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal menghapus data. Silakan coba lagi.');
        } else {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        }

        redirect('Logistik_Purchase_Request/index/');
    }

    public function view_purchase_request($id_purchase_request)
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Purchase Request';
            $data['type'] = 'view';
            $data['detail_purchase_request'] = $this->MLogistik_Purchase_Request->get_detail_purchase_request($id_purchase_request);
            if (empty($data['detail_purchase_request'])) {
                $this->session->set_flashdata('error', 'Data purchase request tidak ditemukan.');
                redirect('Logistik_Purchase_Request');
                return;
            }
            $data['purchase_request_meta'] = $this->MLogistik_Purchase_Request->decorate_purchase_request_row($data['detail_purchase_request'][0]);
            $data = array_merge($data, $this->buildNodinContext($id_purchase_request, $data['purchase_request_meta']));

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('logistik_purchase_request_detail', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function edit_purchase_request($id_purchase_request)
    {
        if (!empty($this->session->userdata('id_user'))) {
            if (!$this->can_manage_planning()) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengedit review planning.');
                redirect('Logistik_Purchase_Request/view_purchase_request/' . $id_purchase_request);
                return;
            }

            $data['title'] = 'Purchase Request';
            $data['type'] = 'edit';
            $data['detail_purchase_request'] = $this->MLogistik_Purchase_Request->get_detail_purchase_request($id_purchase_request);
            if (empty($data['detail_purchase_request'])) {
                $this->session->set_flashdata('error', 'Data purchase request tidak ditemukan.');
                redirect('Logistik_Purchase_Request');
                return;
            }
            $data['purchase_request_meta'] = $this->MLogistik_Purchase_Request->decorate_purchase_request_row($data['detail_purchase_request'][0]);
            $data = array_merge($data, $this->buildNodinContext($id_purchase_request, $data['purchase_request_meta']));

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('logistik_purchase_request_detail', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }
    public function edit_purchase_request_by_planning()
    {
        if (!empty($this->session->userdata('id_user'))) {
            if (!$this->can_manage_planning()) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk menyimpan review planning.');
                redirect('Logistik_Purchase_Request');
                return;
            }

            $id_purchase_request = $this->input->post('id_purchase_request');
            $id_detail = $this->input->post('id_purchase_request_detail_');
            $volume_planning = $this->input->post('volume_planning_');
            $ket_planning = $this->input->post('keterangan_planning_');
            if (!empty($id_detail) && !empty($volume_planning)) {
                $hasVolumePlanningColumn = $this->db->field_exists('volume_planning', 'tb_logistik_purchase_request_detail');

                foreach ($id_detail as $key => $id) {
                    $data = [
                        'qty_planning' => $volume_planning[$key],
                        'keterangan_planning' => $ket_planning[$key]
                    ];

                    if ($hasVolumePlanningColumn) {
                        $data['volume_planning'] = $volume_planning[$key];
                    }

                    $this->db->where('id_purchase_request_detail', $id);
                    $this->db->update('tb_logistik_purchase_request_detail', $data);
                }

                $this->db->where('id_purchase_request', $id_purchase_request);
                $this->db->update('tb_logistik_purchase_request', ['approved_planning' => '1']);

                $this->session->set_flashdata('success', 'Data berhasil diperbarui');
            } else {
                $this->session->set_flashdata('error', 'Data tidak boleh kosong');
            }

            redirect('Logistik_Purchase_Request/view_purchase_request/' . $id_purchase_request);
        } else {
            redirect('Auth');
        }
    }

    public function upload_hardcopy()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $this->load->helper('date');
            $this->load->library('upload');

            $upload_path = "./uploads/";
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $id_purchase_request = $this->input->post('id_purchase_request');
            $timestamp = date('Y-m-d_H-i-s');
            $field_name = 'file-hardcopy';

            // Pastikan file ada sebelum lanjut
            if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
                $this->session->set_flashdata('error', 'Tidak ada file yang diunggah atau terjadi kesalahan!');
                redirect('Logistik_Purchase_Request/view_purchase_request/' . $id_purchase_request);
            }

            $file_ext = pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION);
            $Name_files = "PURCHASE_REQUEST_{$timestamp}_{$id_purchase_request}.{$file_ext}";

            $config = [
                'upload_path'   => $upload_path,
                'allowed_types' => 'pdf',
                'max_size'      => 5120,
                'file_name'     => $Name_files
            ];

            $this->upload->initialize($config);

            if (!$this->upload->do_upload($field_name)) {
                $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                redirect('Logistik_Purchase_Request/view_purchase_request/' . $id_purchase_request);
            }

            $uploaded_file = $this->upload->data('file_name');

            // Pastikan file berhasil diunggah sebelum update database
            if (!empty($uploaded_file)) {
                $this->db->where('id_purchase_request', $id_purchase_request);
                $this->db->update('tb_logistik_purchase_request', ['hardcopy_file' => $uploaded_file]);

                $this->session->set_flashdata('success', 'Dokumen berhasil diupload!');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengupload dokumen.');
            }

            redirect('Logistik_Purchase_Request/view_purchase_request/' . $id_purchase_request);
        } else {
            redirect('Auth');
        }
    }

    public function approve_purchase_request()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $id_purchase_request = $this->input->post('id_purchase_request');
            $tipe = strtolower(trim((string) $this->input->post('tipe')));
            $column = isset($this->approvalMap[$tipe]) ? $this->approvalMap[$tipe] : null;
            if ($column && !$this->db->field_exists($column, 'tb_logistik_purchase_request')) {
                $column = null;
            }

            if ($column) {
                if (!$this->can_approve_stage($tipe)) {
                    $this->output
                        ->set_status_header(403)
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'status' => 'error',
                            'message' => 'Anda tidak memiliki akses untuk approval tahap ini.'
                        ]));
                    return;
                }

                $this->db->where('id_purchase_request', $id_purchase_request);
                $update = $this->db->update('tb_logistik_purchase_request', [$column => 1]);

                if ($update) {
                    $this->session->set_flashdata('success', 'Data berhasil diperbarui.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui data.');
                }
            } else {
                $this->session->set_flashdata('error', 'Tipe approval tidak valid.');
            }
            return true;
        } else {
            redirect('Auth');
        }
    }

    public function print_purchase_request($idPurchaseRequest = '')
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idPurchaseRequest = trim((string) $idPurchaseRequest);
        if ($idPurchaseRequest === '') {
            $this->session->set_flashdata('error', 'Data purchase request tidak ditemukan.');
            redirect('Logistik_Purchase_Request');
            return;
        }

        $detailRows = $this->MLogistik_Purchase_Request->get_detail_purchase_request($idPurchaseRequest);
        if (empty($detailRows)) {
            $this->session->set_flashdata('error', 'Data purchase request tidak ditemukan.');
            redirect('Logistik_Purchase_Request');
            return;
        }

        $purchaseRequest = $this->MLogistik_Purchase_Request->decorate_purchase_request_row($detailRows[0]);
        if (empty($purchaseRequest['is_fully_approved'])) {
            $this->session->set_flashdata('error', 'PR hanya bisa dicetak setelah full approved.');
            redirect('Logistik_Purchase_Request/view_purchase_request/' . $idPurchaseRequest);
            return;
        }

        $data = [
            'title' => 'Print Purchase Request',
            'purchaseRequest' => $purchaseRequest,
            'detailPurchaseRequest' => $detailRows,
        ];

        $this->load->view('format_purchase_request_print', $data);
    }

    public function generateUniqId($prefix)
    {
        $prefix = substr($prefix, 0, 3);
        try {
            $random = strtoupper(bin2hex(random_bytes(4)));
        } catch (Exception $e) {
            $random = strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8));
        }

        $microtimeDigits = preg_replace('/\D/', '', (string) microtime(true));
        $suffix = substr($microtimeDigits, -6);

        return strtoupper($prefix . '_' . $random . '_' . $suffix);
    }

    public function save_nodin()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idPurchaseRequest = trim((string) $this->input->post('id_purchase_request'));
        $purchaseRequest = $this->MLogistik_Purchase_Request->decorate_purchase_request_row(
            $this->MLogistik_Purchase_Request->get_detail_purchase_request($idPurchaseRequest)[0] ?? []
        );

        if (empty($purchaseRequest)) {
            $this->session->set_flashdata('error', 'Data PR tidak ditemukan.');
            redirect('Logistik_Purchase_Request');
            return;
        }

        if (empty($purchaseRequest['is_fully_approved'])) {
            $this->session->set_flashdata('error', 'NODIN hanya bisa dibuat setelah PR selesai approval.');
            redirect('Logistik_Purchase_Request/view_purchase_request/' . $idPurchaseRequest);
            return;
        }

        if (!$this->can_manage_nodin()) {
            $this->session->set_flashdata('error', 'Hanya admin logistik HO yang dapat membuat NODIN.');
            redirect('Logistik_Purchase_Request/view_purchase_request/' . $idPurchaseRequest);
            return;
        }

        $existingNodin = $this->MLogistik_Purchase_Request->getLatestNodinByPurchaseRequest($idPurchaseRequest);
        if (!empty($existingNodin['is_fully_approved'])) {
            $this->session->set_flashdata('error', 'NODIN yang sudah approved tidak dapat diubah lagi.');
            redirect('Logistik_Purchase_Request/view_purchase_request/' . $idPurchaseRequest);
            return;
        }

        $nomorNodin = trim((string) $this->input->post('nomor_nota_dinas'));
        $tanggalNodin = trim((string) $this->input->post('tanggal_nota_dinas'));
        $ditujukanKepada = trim((string) $this->input->post('ditujukan_kepada'));
        $tujuanPo = trim((string) $this->input->post('tujuan_penerbitan_po'));
        $detailIds = (array) $this->input->post('id_purchase_request_detail');
        $kodeItems = (array) $this->input->post('id_kode_item');
        $kebutuhanItems = (array) $this->input->post('kebutuhan_project');
        $outstandingItems = (array) $this->input->post('outstanding_pr');
        $qtyPoItems = (array) $this->input->post('qty_po_nodin');
        $hargaItems = (array) $this->input->post('harga_satuan');
        $pabrikItems = (array) $this->input->post('id_pabrik');
        $keteranganItems = (array) $this->input->post('keterangan_nodin');

        if ($nomorNodin === '' || $tanggalNodin === '' || $tujuanPo === '') {
            $this->session->set_flashdata('error', 'Nomor NODIN, tanggal NODIN, dan tujuan penerbitan PO wajib diisi.');
            redirect('Logistik_Purchase_Request/view_purchase_request/' . $idPurchaseRequest);
            return;
        }

        $outstandingMap = $this->MLogistik_Pesanan_Pabrik->getOutstandingPrItemMap($idPurchaseRequest);
        $masterPabrikMap = [];
        foreach ($this->MLogistik_Pesanan_Pabrik->getMasterPabrikActive() as $pabrikRow) {
            $masterPabrikMap[(int) ($pabrikRow['id_pabrik'] ?? 0)] = $pabrikRow;
        }

        $nodinId = !empty($existingNodin['id_nota_dinas_po'])
            ? $existingNodin['id_nota_dinas_po']
            : $this->generateUniqId('NOD');

        $details = [];
        foreach ($detailIds as $index => $detailId) {
            $detailId = trim((string) $detailId);
            if ($detailId === '' || !isset($outstandingMap[$detailId])) {
                continue;
            }

            $qtyPo = (float) ($qtyPoItems[$index] ?? 0);
            $maxOutstanding = (float) ($outstandingMap[$detailId]['qty_outstanding_pr'] ?? 0);
            if ($qtyPo <= 0) {
                $this->session->set_flashdata('error', 'Qty PO usulan pada NODIN harus lebih dari 0.');
                redirect('Logistik_Purchase_Request/view_purchase_request/' . $idPurchaseRequest);
                return;
            }

            $idPabrik = !empty($pabrikItems[$index]) ? (int) $pabrikItems[$index] : 0;
            if ($idPabrik <= 0) {
                $this->session->set_flashdata('error', 'Vendor / pabrik pada setiap item NODIN wajib dipilih dari master pabrik.');
                redirect('Logistik_Purchase_Request/view_purchase_request/' . $idPurchaseRequest);
                return;
            }

            if (!isset($masterPabrikMap[$idPabrik])) {
                $this->session->set_flashdata('error', 'Pabrik yang dipilih tidak valid atau sudah tidak aktif.');
                redirect('Logistik_Purchase_Request/view_purchase_request/' . $idPurchaseRequest);
                return;
            }

            $detailNodinId = $this->generateUniqId('NDD');

            $details[] = [
                'id_nota_dinas_po_detail' => $detailNodinId,
                'id_nota_dinas_po' => $nodinId,
                'id_purchase_request_detail' => $detailId,
                'id_kode_item' => (int) ($kodeItems[$index] ?? 0),
                'id_pabrik' => $idPabrik,
                'vendor_pabrik' => (string) ($masterPabrikMap[$idPabrik]['nama_pabrik'] ?? ''),
                'kebutuhan_project' => (float) ($kebutuhanItems[$index] ?? ($outstandingMap[$detailId]['volume_planning_final'] ?? 0)),
                'outstanding_pr' => (float) ($outstandingItems[$index] ?? $maxOutstanding),
                'qty_po_nodin' => $qtyPo,
                'harga_satuan' => (float) ($hargaItems[$index] ?? 0),
                'keterangan' => trim((string) ($keteranganItems[$index] ?? '')),
            ];
        }

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Tidak ada item NODIN yang valid untuk disimpan.');
            redirect('Logistik_Purchase_Request/view_purchase_request/' . $idPurchaseRequest);
            return;
        }

        $header = [
            'id_nota_dinas_po' => $nodinId,
            'id_purchase_request' => $idPurchaseRequest,
            'nomor_nota_dinas' => $nomorNodin,
            'tanggal_nota_dinas' => $tanggalNodin,
            'ditujukan_kepada' => $ditujukanKepada,
            'dibuat_oleh' => (int) $this->session->userdata('id_user'),
            'tujuan_penerbitan_po' => $tujuanPo,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (empty($existingNodin)) {
            $header['created_at'] = date('Y-m-d H:i:s');
        } else {
            foreach ($this->nodinApprovalMap as $approvalColumn) {
                $header[$approvalColumn] = 0;
            }
        }

        $isSuccess = $this->MLogistik_Purchase_Request->saveNodin($header, $details, $existingNodin['id_nota_dinas_po'] ?? null);
        $successMessage = empty($existingNodin)
            ? 'NODIN berhasil disimpan.'
            : 'NODIN berhasil diperbarui dan proses approval diulang dari awal.';
        $this->session->set_flashdata($isSuccess ? 'success' : 'error', $isSuccess ? $successMessage : 'Gagal menyimpan NODIN.');
        redirect('Logistik_Purchase_Request/view_purchase_request/' . $idPurchaseRequest);
    }

    public function approve_nodin()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idPurchaseRequest = trim((string) $this->input->post('id_purchase_request'));
        $idNodin = trim((string) $this->input->post('id_nota_dinas_po'));
        $tipe = strtolower(trim((string) $this->input->post('tipe')));
        $column = $this->nodinApprovalMap[$tipe] ?? null;

        if ($column === null) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Tahap approval NODIN tidak valid.']));
            return;
        }

        if (!$this->can_approve_nodin_stage($tipe)) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki akses untuk approval NODIN tahap ini.']));
            return;
        }

        $updated = $this->MLogistik_Purchase_Request->approveNodin($idNodin, $column);
        if ($updated) {
            $this->session->set_flashdata('success', 'Approval NODIN berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui approval NODIN.');
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => $updated ? 'success' : 'error']));
    }

    private function is_super_admin()
    {
        return (string) $this->session->userdata('nama_level') === 'Super Admin';
    }

    private function get_validation_key()
    {
        $keys = function_exists('get_validation_key_list') ? get_validation_key_list() : [];
        if (!empty($keys)) {
            return (string) $keys[0];
        }

        return strtolower(str_replace(' ', '_', trim((string) $this->session->userdata('validation'))));
    }

    private function can_manage_planning()
    {
        return $this->is_super_admin() || (function_exists('has_validation_key') ? has_validation_key('planning') : $this->get_validation_key() === 'planning');
    }

    private function can_manage_nodin()
    {
        return $this->is_super_admin() || strtoupper((string) $this->session->userdata('lokasi_user')) === 'HO';
    }

    private function can_approve_stage($stageKey)
    {
        if ($this->is_super_admin()) {
            return true;
        }

        $normalizedStageKey = strtolower(trim((string) $stageKey));
        if ($normalizedStageKey === '') {
            return false;
        }

        if (function_exists('has_validation_key')) {
            return has_validation_key($normalizedStageKey);
        }

        return $this->get_validation_key() === $normalizedStageKey;
    }

    private function can_approve_nodin_stage($stageKey)
    {
        if ($this->is_super_admin()) {
            return true;
        }

        $normalizedStageKey = strtolower(trim((string) $stageKey));
        if ($normalizedStageKey === '') {
            return false;
        }

        if (function_exists('has_validation_key')) {
            return has_validation_key($normalizedStageKey);
        }

        return $this->get_validation_key() === $normalizedStageKey;
    }

    private function buildNodinContext($idPurchaseRequest, $purchaseRequestMeta)
    {
        $nodin = $this->MLogistik_Purchase_Request->getLatestNodinByPurchaseRequest($idPurchaseRequest);
        $nodinDetailRows = !empty($nodin['id_nota_dinas_po'])
            ? $this->MLogistik_Purchase_Request->getNodinDetailRows($nodin['id_nota_dinas_po'])
            : [];
        $nodinDetailRows = array_values(array_filter($nodinDetailRows, static function ($row) use ($idPurchaseRequest, $purchaseRequestMeta) {
            if ((string) ($row['id_purchase_request'] ?? '') !== '') {
                return (string) ($row['id_purchase_request'] ?? '') === (string) $idPurchaseRequest;
            }

            return (string) ($row['nomor_purchase_request'] ?? '') === (string) ($purchaseRequestMeta['nomor_purchase_request'] ?? '');
        }));
        $candidateItems = !empty($purchaseRequestMeta['is_fully_approved'])
            ? $this->MLogistik_Pesanan_Pabrik->getApprovedPurchaseRequestItems($idPurchaseRequest)
            : [];
        $candidateItems = $this->mergeExistingNodinDetailRowsIntoCandidates($candidateItems, $nodinDetailRows);

        $nodinApprovalStages = $nodin['workflow_stages'] ?? $this->MLogistik_Purchase_Request->get_nodin_workflow();
        $nodinCurrentApprovalKey = '';
        $nodinCurrentApprovalLabel = '';
        foreach ($nodinApprovalStages as $stage) {
            if (empty($nodin[$stage['column']])) {
                $nodinCurrentApprovalKey = strtolower(str_replace(' ', '_', $stage['label']));
                $nodinCurrentApprovalLabel = $stage['label'];
                break;
            }
        }

        return [
            'nodinData' => $nodin,
            'nodinDetailRows' => $nodinDetailRows,
            'nodinCandidateItems' => $candidateItems,
            'masterPabrikOptions' => $this->MLogistik_Pesanan_Pabrik->getMasterPabrikActive(),
            'canManageNodin' => $this->can_manage_nodin(),
            'nodinApprovalStages' => $nodinApprovalStages,
            'nodinCurrentApprovalKey' => $nodinCurrentApprovalKey,
            'nodinCurrentApprovalLabel' => $nodinCurrentApprovalLabel,
            'canApproveCurrentNodinStage' => !empty($nodinCurrentApprovalKey) && $this->can_approve_nodin_stage($nodinCurrentApprovalKey),
        ];
    }

    private function mergeExistingNodinDetailRowsIntoCandidates(array $candidateItems, array $nodinDetailRows)
    {
        if (empty($nodinDetailRows)) {
            return $candidateItems;
        }

        $candidateMap = [];
        foreach ($candidateItems as $index => $item) {
            $itemKey = (string) ($item['id_purchase_request_detail'] ?? '');
            if ($itemKey === '') {
                $itemKey = (string) ($item['id_kode_item'] ?? strtolower(trim((string) ($item['nama_item'] ?? ''))));
            }
            $candidateMap[$itemKey] = $index;
        }

        foreach ($nodinDetailRows as $detailRow) {
            $detailKey = (string) ($detailRow['id_purchase_request_detail'] ?? '');
            if ($detailKey === '') {
                $detailKey = (string) ($detailRow['id_kode_item'] ?? strtolower(trim((string) ($detailRow['nama_item'] ?? ''))));
            }

            if (isset($candidateMap[$detailKey])) {
                continue;
            }

            $candidateItems[] = [
                'id_purchase_request_detail' => (string) ($detailRow['id_purchase_request_detail'] ?? ''),
                'id_purchase_request' => (string) ($detailRow['id_purchase_request'] ?? ''),
                'id_kode_item' => (int) ($detailRow['id_kode_item'] ?? 0),
                'nama_item' => (string) ($detailRow['nama_item'] ?? '-'),
                'satuan_item' => (string) ($detailRow['satuan_item'] ?? '-'),
                'volume_planning_final' => (float) ($detailRow['kebutuhan_project'] ?? 0),
                'qty_outstanding_pr' => (float) ($detailRow['outstanding_pr'] ?? 0),
                'nomor_purchase_request' => (string) ($detailRow['nomor_purchase_request'] ?? ''),
                'nama_project' => (string) ($detailRow['nama_project'] ?? ''),
                'id_project' => (string) ($detailRow['id_project'] ?? ''),
            ];
        }

        return $candidateItems;
    }
}
