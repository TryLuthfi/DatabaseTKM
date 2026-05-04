<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logistik_Pesanan_Pabrik extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MLogistik_Pesanan_Pabrik');
        $this->load->model('MLogistik_Purchase_Request');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $data['title'] = 'PO Pabrik';
        $data['judul'] = 'PO Pabrik';
        $data['poRows'] = $this->MLogistik_Pesanan_Pabrik->getPoSummaryRows();
        $data['poStats'] = $this->MLogistik_Pesanan_Pabrik->getPoDashboardStats();
        $data['masterPabrik'] = $this->MLogistik_Pesanan_Pabrik->getMasterPabrikActive();
        $data['approvedPurchaseRequests'] = $this->getApprovedPurchaseRequestOptions();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Logistik_Pesanan_Pabrik/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function get_purchase_request_items()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idPurchaseRequest = trim((string) $this->input->get('id_purchase_request'));
        if ($idPurchaseRequest === '') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([]));
            return;
        }

        $items = $this->MLogistik_Pesanan_Pabrik->getApprovedPurchaseRequestItems($idPurchaseRequest);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($items));
    }

    public function create_po_from_pr()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idPurchaseRequest = trim((string) $this->input->post('id_purchase_request'));
        $idPabrik = (int) $this->input->post('id_pabrik');
        $nomorPo = trim((string) $this->input->post('nomor_po_pabrik'));
        $tanggalPo = trim((string) $this->input->post('tanggal_po_pabrik'));
        $selectedItems = (array) $this->input->post('selected_item');
        $detailIds = (array) $this->input->post('id_purchase_request_detail');
        $kodeItems = (array) $this->input->post('id_kode_item');
        $qtyItems = (array) $this->input->post('qty_item');
        $hargaItems = (array) $this->input->post('harga_item');
        $volumeSnapshots = (array) $this->input->post('volume_planning_snapshot');

        if ($idPurchaseRequest === '' || $idPabrik <= 0 || $nomorPo === '' || $tanggalPo === '' || empty($selectedItems)) {
            $this->session->set_flashdata('error', 'Data PO belum lengkap. Pastikan PR, pabrik, nomor PO, tanggal PO, dan item sudah dipilih.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $nodin = $this->MLogistik_Purchase_Request->getLatestNodinByPurchaseRequest($idPurchaseRequest);
        if (empty($nodin) || empty($nodin['is_fully_approved'])) {
            $this->session->set_flashdata('error', 'PR belum memiliki NODIN approved penuh, sehingga belum bisa dibuatkan PO.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $poId = $this->generatePoId();

        $header = [
            'id_pesanan_pabrik' => $poId,
            'id_pabrik' => $idPabrik,
            'nomor_po_pabrik' => $nomorPo,
            'tanggal_po_pabrik' => $tanggalPo,
            'purchase_order_document' => null,
            'id_user' => $this->session->userdata('id_user'),
        ];

        if ($this->db->field_exists('id_purchase_request', 'tb_logistik_pesanan_pabrik')) {
            $header['id_purchase_request'] = $idPurchaseRequest;
        }
        if ($this->db->field_exists('nomor_purchase_request', 'tb_logistik_pesanan_pabrik')) {
            $header['nomor_purchase_request'] = trim((string) $this->input->post('nomor_purchase_request'));
        }
        if ($this->db->field_exists('status_po', 'tb_logistik_pesanan_pabrik')) {
            $header['status_po'] = 'APPROVED';
        }

        $supportsPrDetail = $this->db->field_exists('id_purchase_request_detail', 'tb_logistik_pesanan_pabrik_detail');
        $supportsVolumeSnapshot = $this->db->field_exists('volume_planning_snapshot', 'tb_logistik_pesanan_pabrik_detail');
        $outstandingItemMap = $this->MLogistik_Pesanan_Pabrik->getOutstandingPrItemMap($idPurchaseRequest);

        $details = [];
        $selectedDetailIds = [];
        foreach ($selectedItems as $index) {
            if (!isset($detailIds[$index], $kodeItems[$index], $qtyItems[$index])) {
                continue;
            }

            $detailId = trim((string) $detailIds[$index]);
            if ($detailId === '' || !isset($outstandingItemMap[$detailId])) {
                $this->session->set_flashdata('error', 'Ada item PR yang sudah tidak outstanding atau sudah teralokasi penuh ke PO lain. Silakan muat ulang data PR.');
                redirect('Logistik_Pesanan_Pabrik');
                return;
            }

            if (isset($selectedDetailIds[$detailId])) {
                $this->session->set_flashdata('error', 'Terdapat item PR yang dipilih dobel dalam satu PO. Periksa kembali item yang dipilih.');
                redirect('Logistik_Pesanan_Pabrik');
                return;
            }

            $qty = (float) $qtyItems[$index];
            if ($qty <= 0) {
                continue;
            }

            $maxOutstanding = (float) ($outstandingItemMap[$detailId]['qty_outstanding_pr'] ?? 0);
            if ($maxOutstanding <= 0 || $qty > $maxOutstanding) {
                $itemName = (string) ($outstandingItemMap[$detailId]['nama_item'] ?? 'Item');
                $this->session->set_flashdata('error', $itemName . ' hanya memiliki outstanding ' . number_format($maxOutstanding, 0, ',', '.') . '. Nilai PO tidak boleh melebihi outstanding PR.');
                redirect('Logistik_Pesanan_Pabrik');
                return;
            }

            $detailRow = [
                'id_pesanan_pabrik' => $poId,
                'id_kode_item' => (int) $kodeItems[$index],
                'harga_item' => (float) ($hargaItems[$index] ?? 0),
                'qty_item' => $qty,
            ];

            if ($supportsPrDetail) {
                $detailRow['id_purchase_request_detail'] = $detailId;
            }

            if ($supportsVolumeSnapshot) {
                $detailRow['volume_planning_snapshot'] = (float) ($volumeSnapshots[$index] ?? $outstandingItemMap[$detailId]['volume_planning_final'] ?? $qty);
            }

            $details[] = $detailRow;
            $selectedDetailIds[$detailId] = true;
        }

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Tidak ada item valid yang bisa disimpan ke PO.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $isSuccess = $this->MLogistik_Pesanan_Pabrik->createPoFromApprovedPr($header, $details);
        $this->session->set_flashdata($isSuccess ? 'success' : 'error', $isSuccess ? 'PO berhasil dibuat dari PR approved.' : 'Gagal membuat PO dari PR approved.');
        redirect('Logistik_Pesanan_Pabrik');
    }

    private function generatePoId()
    {
        return substr((string) time() . mt_rand(10, 99), 0, 11);
    }

    private function getApprovedPurchaseRequestOptions()
    {
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

            $nodin = $this->MLogistik_Purchase_Request->getLatestNodinByPurchaseRequest((string) $row['id_purchase_request']);
            if (empty($nodin) || empty($nodin['is_fully_approved'])) {
                continue;
            }

            $outstandingItems = $this->MLogistik_Pesanan_Pabrik->getOutstandingPrItemMap((string) $row['id_purchase_request']);
            if (empty($outstandingItems)) {
                continue;
            }

            $row['total_qty_outstanding_pr'] = array_sum(array_map(static function ($item) {
                return (float) ($item['qty_outstanding_pr'] ?? 0);
            }, $outstandingItems));
            $row['nodin_data'] = $nodin;

            $approvedRows[] = $row;
        }

        usort($approvedRows, static function ($left, $right) {
            $leftDate = (string) ($left['tanggal_pembuatan'] ?? '');
            $rightDate = (string) ($right['tanggal_pembuatan'] ?? '');
            if ($leftDate === $rightDate) {
                return strcmp((string) ($right['nomor_purchase_request'] ?? ''), (string) ($left['nomor_purchase_request'] ?? ''));
            }

            return strcmp($rightDate, $leftDate);
        });

        return $approvedRows;
    }
}
