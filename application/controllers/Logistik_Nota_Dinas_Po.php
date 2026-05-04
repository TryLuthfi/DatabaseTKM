<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logistik_Nota_Dinas_Po extends CI_Controller
{
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
        $this->load->model('MLogistik_Purchase_Request');
        $this->load->model('MLogistik_Pesanan_Pabrik');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $data['title'] = 'Nota Dinas PO';
        $data['nodinRows'] = $this->MLogistik_Purchase_Request->getNodinSummaryRows();
        $data['approvedPurchaseRequests'] = [];
        $data['masterPabrikOptions'] = $this->MLogistik_Pesanan_Pabrik->getMasterPabrikActive();
        $data['activeNodin'] = null;
        $data['activeNodinDetailRows'] = [];
        $data['activeNodinPurchaseRequestIds'] = [];
        $data['activeNodinCandidateItems'] = [];
        $data['activeNodinCurrentApprovalKey'] = '';
        $data['activeNodinCurrentApprovalLabel'] = '';
        $data['canApproveCurrentNodinStage'] = false;
        $data['activeNodinEditMode'] = false;
        $data['activeNodinReadOnly'] = false;

        $editId = trim((string) $this->input->get('id'));
        $isEditRequest = trim((string) $this->input->get('edit')) === '1';
        if ($editId !== '') {
            $activeNodin = $this->MLogistik_Purchase_Request->getNodinById($editId);
            if (!empty($activeNodin)) {
                $purchaseRequestIds = $this->MLogistik_Purchase_Request->getNodinPurchaseRequestIds($editId);
                $data['activeNodin'] = $activeNodin;
                $data['activeNodinDetailRows'] = $this->MLogistik_Purchase_Request->getNodinDetailRows($editId);
                $data['activeNodinPurchaseRequestIds'] = $purchaseRequestIds;
                $data['activeNodinCandidateItems'] = $this->buildCandidateItems($purchaseRequestIds);

                foreach (($activeNodin['workflow_stages'] ?? []) as $stage) {
                    if (empty($activeNodin[$stage['column']])) {
                        $data['activeNodinCurrentApprovalKey'] = strtolower(str_replace(' ', '_', $stage['label']));
                        $data['activeNodinCurrentApprovalLabel'] = $stage['label'];
                        break;
                    }
                }

                $data['canApproveCurrentNodinStage'] = !empty($data['activeNodinCurrentApprovalKey']) && $this->can_approve_nodin_stage($data['activeNodinCurrentApprovalKey']);
                $data['activeNodinEditMode'] = $isEditRequest && $this->can_manage_nodin();
                $data['activeNodinReadOnly'] = !$data['activeNodinEditMode'];
            }
        }

        $data['approvedPurchaseRequests'] = $this->getApprovedPurchaseRequestOptions($data['activeNodinPurchaseRequestIds']);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Logistik_Nota_Dinas_Po/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function get_purchase_request_items()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $prIds = [];
        $rawPrIds = $this->input->get('id_purchase_request');
        if (is_array($rawPrIds)) {
            $prIds = $rawPrIds;
        } elseif ($rawPrIds !== null && $rawPrIds !== '') {
            $prIds = [trim((string) $rawPrIds)];
        }

        $csv = trim((string) $this->input->get('id_purchase_request_csv'));
        if ($csv !== '') {
            $prIds = array_merge($prIds, array_filter(array_map('trim', explode(',', $csv))));
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($this->buildCandidateItems($prIds)));
    }

    public function save_nodin()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->can_manage_nodin()) {
            $this->session->set_flashdata('error', 'Hanya admin logistik HO yang dapat membuat atau mengubah NODIN.');
            redirect('Logistik_Nota_Dinas_Po');
            return;
        }

        $idNodin = trim((string) $this->input->post('id_nota_dinas_po'));
        $existingNodin = $idNodin !== '' ? $this->MLogistik_Purchase_Request->getNodinById($idNodin) : null;

        $selectedPrIds = array_values(array_filter(array_map('trim', (array) $this->input->post('selected_purchase_request'))));
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

        if (empty($selectedPrIds) || $nomorNodin === '' || $tanggalNodin === '' || $tujuanPo === '') {
            $this->session->set_flashdata('error', 'PR sumber, nomor NODIN, tanggal NODIN, dan tujuan penerbitan PO wajib diisi.');
            redirect('Logistik_Nota_Dinas_Po' . ($idNodin !== '' ? '?id=' . rawurlencode($idNodin) : ''));
            return;
        }

        $candidateItems = $this->buildCandidateItems($selectedPrIds);
        $candidateMap = [];
        foreach ($candidateItems as $item) {
            $candidateMap[(string) ($item['id_purchase_request_detail'] ?? '')] = $item;
        }

        $masterPabrikMap = [];
        foreach ($this->MLogistik_Pesanan_Pabrik->getMasterPabrikActive() as $pabrikRow) {
            $masterPabrikMap[(int) ($pabrikRow['id_pabrik'] ?? 0)] = $pabrikRow;
        }

        $nodinId = $idNodin !== '' ? $idNodin : $this->generateUniqId('NOD');
        $details = [];
        $qtyPerDetail = [];
        foreach ($detailIds as $index => $detailId) {
            $detailId = trim((string) $detailId);
            if ($detailId === '' || !isset($candidateMap[$detailId])) {
                continue;
            }

            $qtyPo = (float) ($qtyPoItems[$index] ?? 0);
            if ($qtyPo <= 0) {
                $this->session->set_flashdata('error', 'Qty PO usulan pada NODIN harus lebih dari 0.');
                redirect('Logistik_Nota_Dinas_Po' . ($idNodin !== '' ? '?id=' . rawurlencode($idNodin) : ''));
                return;
            }

            $qtyPerDetail[$detailId] = ($qtyPerDetail[$detailId] ?? 0) + $qtyPo;

            $idPabrik = !empty($pabrikItems[$index]) ? (int) $pabrikItems[$index] : 0;
            if ($idPabrik <= 0 || !isset($masterPabrikMap[$idPabrik])) {
                $this->session->set_flashdata('error', 'Vendor / pabrik pada setiap item NODIN wajib dipilih dari master pabrik aktif.');
                redirect('Logistik_Nota_Dinas_Po' . ($idNodin !== '' ? '?id=' . rawurlencode($idNodin) : ''));
                return;
            }

            $details[] = [
                'id_nota_dinas_po_detail' => $this->generateUniqId('NDD'),
                'id_nota_dinas_po' => $nodinId,
                'id_purchase_request_detail' => $detailId,
                'id_kode_item' => (int) ($kodeItems[$index] ?? 0),
                'id_pabrik' => $idPabrik,
                'vendor_pabrik' => (string) ($masterPabrikMap[$idPabrik]['nama_pabrik'] ?? ''),
                'kebutuhan_project' => (float) ($kebutuhanItems[$index] ?? ($candidateMap[$detailId]['volume_planning_final'] ?? 0)),
                'outstanding_pr' => (float) ($outstandingItems[$index] ?? ($candidateMap[$detailId]['qty_outstanding_pr'] ?? 0)),
                'qty_po_nodin' => $qtyPo,
                'harga_satuan' => (float) ($hargaItems[$index] ?? 0),
                'keterangan' => trim((string) ($keteranganItems[$index] ?? '')),
            ];
        }

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Tidak ada item NODIN yang valid untuk disimpan.');
            redirect('Logistik_Nota_Dinas_Po' . ($idNodin !== '' ? '?id=' . rawurlencode($idNodin) : ''));
            return;
        }

        foreach ($qtyPerDetail as $detailId => $totalQty) {
            $maxOutstanding = (float) ($candidateMap[$detailId]['qty_outstanding_pr'] ?? 0);
            if ($totalQty > $maxOutstanding) {
                $itemName = (string) ($candidateMap[$detailId]['nama_item'] ?? 'Item');
                $this->session->set_flashdata('error', $itemName . ' memiliki total split NODIN ' . number_format($totalQty, 0, ',', '.') . ', melebihi outstanding PR ' . number_format($maxOutstanding, 0, ',', '.') . '.');
                redirect('Logistik_Nota_Dinas_Po' . ($idNodin !== '' ? '?id=' . rawurlencode($idNodin) : ''));
                return;
            }
        }

        $header = [
            'id_nota_dinas_po' => $nodinId,
            'id_purchase_request' => $selectedPrIds[0],
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
        redirect('Logistik_Nota_Dinas_Po');
    }

    public function approve_nodin()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idNodin = trim((string) $this->input->post('id_nota_dinas_po'));
        $tipe = strtolower(trim((string) $this->input->post('tipe')));
        $column = $this->nodinApprovalMap[$tipe] ?? null;

        if ($column === null) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode([
                'status' => 'error',
                'message' => 'Tahap approval NODIN tidak valid.',
            ]));
            return;
        }

        if (!$this->can_approve_nodin_stage($tipe)) {
            $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk approval NODIN tahap ini.',
            ]));
            return;
        }

        $updated = $this->MLogistik_Purchase_Request->approveNodin($idNodin, $column);
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => $updated ? 'success' : 'error',
        ]));
    }

    public function delete_nodin($idNodin = null)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->can_manage_nodin()) {
            $this->session->set_flashdata('error', 'Hanya admin logistik HO yang dapat menghapus NODIN.');
            redirect('Logistik_Nota_Dinas_Po');
            return;
        }

        $idNodin = trim((string) $idNodin);
        if ($idNodin === '') {
            $this->session->set_flashdata('error', 'ID NODIN tidak valid.');
            redirect('Logistik_Nota_Dinas_Po');
            return;
        }

        $nodin = $this->MLogistik_Purchase_Request->getNodinById($idNodin);
        if (empty($nodin)) {
            $this->session->set_flashdata('error', 'Data NODIN tidak ditemukan.');
            redirect('Logistik_Nota_Dinas_Po');
            return;
        }

        $deleted = $this->MLogistik_Purchase_Request->deleteNodin($idNodin);
        $this->session->set_flashdata($deleted ? 'success' : 'error', $deleted ? 'NODIN berhasil dihapus.' : 'Gagal menghapus NODIN.');
        redirect('Logistik_Nota_Dinas_Po');
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

    private function getApprovedPurchaseRequestOptions($allowedPurchaseRequestIds = [])
    {
        $allowedPurchaseRequestIds = array_values(array_unique(array_filter(array_map('strval', (array) $allowedPurchaseRequestIds))));

        $rows = array_merge(
            $this->MLogistik_Purchase_Request->decorate_purchase_request_rows(
                $this->MLogistik_Purchase_Request->get_all_purchase_request('area')
            ),
            $this->MLogistik_Purchase_Request->decorate_purchase_request_rows(
                $this->MLogistik_Purchase_Request->get_all_purchase_request('ho')
            )
        );

        $approvedRows = [];
        foreach ($rows as $row) {
            if (empty($row['is_fully_approved'])) {
                continue;
            }

            $idPurchaseRequest = (string) ($row['id_purchase_request'] ?? '');
            $existingNodin = $this->MLogistik_Purchase_Request->getLatestNodinByPurchaseRequest($idPurchaseRequest);
            if (!empty($existingNodin) && !in_array($idPurchaseRequest, $allowedPurchaseRequestIds, true)) {
                continue;
            }

            $outstandingItems = $this->MLogistik_Pesanan_Pabrik->getOutstandingPrItemMap((string) $row['id_purchase_request']);
            if (empty($outstandingItems)) {
                continue;
            }

            $row['total_qty_outstanding_pr'] = array_sum(array_map(static function ($item) {
                return (float) ($item['qty_outstanding_pr'] ?? 0);
            }, $outstandingItems));

            $approvedRows[] = $row;
        }

        return $approvedRows;
    }

    private function buildCandidateItems($purchaseRequestIds)
    {
        $purchaseRequestIds = array_values(array_unique(array_filter(array_map('trim', (array) $purchaseRequestIds))));
        if (empty($purchaseRequestIds)) {
            return [];
        }

        $rows = [];
        foreach ($purchaseRequestIds as $idPurchaseRequest) {
            $items = $this->MLogistik_Pesanan_Pabrik->getApprovedPurchaseRequestItems($idPurchaseRequest);
            foreach ($items as $item) {
                $item['nomor_purchase_request'] = (string) ($item['nomor_purchase_request'] ?? '');
                $rows[] = $item;
            }
        }

        usort($rows, static function ($left, $right) {
            $leftPr = (string) ($left['nomor_purchase_request'] ?? '');
            $rightPr = (string) ($right['nomor_purchase_request'] ?? '');
            if ($leftPr === $rightPr) {
                return strcmp((string) ($left['nama_item'] ?? ''), (string) ($right['nama_item'] ?? ''));
            }

            return strcmp($leftPr, $rightPr);
        });

        return $rows;
    }

    private function is_super_admin()
    {
        return (string) $this->session->userdata('nama_level') === 'Super Admin';
    }

    private function get_validation_key()
    {
        return strtolower(str_replace(' ', '_', trim((string) $this->session->userdata('validation'))));
    }

    private function can_manage_nodin()
    {
        return $this->is_super_admin() || strtoupper((string) $this->session->userdata('lokasi_user')) === 'HO';
    }

    private function can_approve_nodin_stage($stageKey)
    {
        if ($this->is_super_admin()) {
            return true;
        }

        return $this->get_validation_key() === strtolower(trim((string) $stageKey));
    }
}
