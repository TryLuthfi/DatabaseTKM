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
        $data['approvedNodins'] = $this->MLogistik_Pesanan_Pabrik->getApprovedNodinOptions();

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

        $idNodin = trim((string) $this->input->get('id_nota_dinas_po'));
        $idPabrik = (int) $this->input->get('id_pabrik');
        if ($idNodin === '' || $idPabrik <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([]));
            return;
        }

        $items = $this->MLogistik_Pesanan_Pabrik->getApprovedNodinItems($idNodin, $idPabrik);
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

        $idNodin = trim((string) $this->input->post('id_nota_dinas_po'));
        $idPabrik = (int) $this->input->post('id_pabrik');
        $nomorPo = trim((string) $this->input->post('nomor_po_pabrik'));
        $tanggalPo = trim((string) $this->input->post('tanggal_po_pabrik'));
        $selectedItems = (array) $this->input->post('selected_item');
        $nodinDetailIds = (array) $this->input->post('id_nota_dinas_po_detail');
        $detailIds = (array) $this->input->post('id_purchase_request_detail');
        $kodeItems = (array) $this->input->post('id_kode_item');
        $qtyItems = (array) $this->input->post('qty_item');
        $hargaItems = (array) $this->input->post('harga_item');
        $volumeSnapshots = (array) $this->input->post('volume_planning_snapshot');
        $nomorNodin = trim((string) $this->input->post('nomor_nota_dinas'));
        $nomorPurchaseRequestRefs = trim((string) $this->input->post('nomor_purchase_request_refs'));

        if ($idNodin === '' || $idPabrik <= 0 || $nomorPo === '' || $tanggalPo === '' || empty($selectedItems)) {
            $this->session->set_flashdata('error', 'Data PO belum lengkap. Pastikan NODIN, pabrik, nomor PO, tanggal PO, dan item sudah dipilih.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $nodin = $this->MLogistik_Purchase_Request->getNodinById($idNodin);
        if (empty($nodin) || empty($nodin['is_fully_approved'])) {
            $this->session->set_flashdata('error', 'NODIN belum approved penuh, sehingga belum bisa dibuatkan PO.');
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

        $nodinPrIds = $this->MLogistik_Purchase_Request->getNodinPurchaseRequestIds($idNodin);
        if ($this->db->field_exists('id_purchase_request', 'tb_logistik_pesanan_pabrik')) {
            $header['id_purchase_request'] = !empty($nodinPrIds) ? $nodinPrIds[0] : null;
        }
        if ($this->db->field_exists('nomor_purchase_request', 'tb_logistik_pesanan_pabrik')) {
            $header['nomor_purchase_request'] = $nomorPurchaseRequestRefs !== '' ? $nomorPurchaseRequestRefs : $nomorNodin;
        }
        if ($this->db->field_exists('status_po', 'tb_logistik_pesanan_pabrik')) {
            $header['status_po'] = 'APPROVED';
        }
        if ($this->db->field_exists('catatan_po', 'tb_logistik_pesanan_pabrik')) {
            $header['catatan_po'] = 'Sumber NODIN: ' . $nomorNodin;
        }

        $supportsPrDetail = $this->db->field_exists('id_purchase_request_detail', 'tb_logistik_pesanan_pabrik_detail');
        $supportsNodinDetail = $this->db->field_exists('id_nota_dinas_po_detail', 'tb_logistik_pesanan_pabrik_detail');
        $supportsVolumeSnapshot = $this->db->field_exists('volume_planning_snapshot', 'tb_logistik_pesanan_pabrik_detail');
        $outstandingItems = $this->MLogistik_Pesanan_Pabrik->getApprovedNodinItems($idNodin, $idPabrik);
        $outstandingItemMap = [];
        foreach ($outstandingItems as $item) {
            $outstandingItemMap[(string) ($item['id_nota_dinas_po_detail'] ?? '')] = $item;
        }

        $details = [];
        $selectedDetailIds = [];
        foreach ($selectedItems as $index) {
            if (!isset($nodinDetailIds[$index], $detailIds[$index], $kodeItems[$index], $qtyItems[$index])) {
                continue;
            }

            $nodinDetailId = trim((string) $nodinDetailIds[$index]);
            $detailId = trim((string) $detailIds[$index]);
            if ($nodinDetailId === '' || !isset($outstandingItemMap[$nodinDetailId])) {
                $this->session->set_flashdata('error', 'Ada detail NODIN yang sudah tidak outstanding atau sudah teralokasi penuh ke PO lain. Silakan muat ulang data NODIN.');
                redirect('Logistik_Pesanan_Pabrik');
                return;
            }

            if (isset($selectedDetailIds[$nodinDetailId])) {
                $this->session->set_flashdata('error', 'Terdapat detail NODIN yang dipilih dobel dalam satu PO. Periksa kembali item yang dipilih.');
                redirect('Logistik_Pesanan_Pabrik');
                return;
            }

            $qty = (float) $qtyItems[$index];
            if ($qty <= 0) {
                continue;
            }

            $maxOutstanding = (float) ($outstandingItemMap[$nodinDetailId]['qty_outstanding_nodin'] ?? 0);
            if ($maxOutstanding <= 0 || $qty > $maxOutstanding) {
                $itemName = (string) ($outstandingItemMap[$nodinDetailId]['nama_item'] ?? 'Item');
                $this->session->set_flashdata('error', $itemName . ' hanya memiliki outstanding NODIN ' . number_format($maxOutstanding, 0, ',', '.') . '. Nilai PO tidak boleh melebihi outstanding detail NODIN.');
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
            if ($supportsNodinDetail) {
                $detailRow['id_nota_dinas_po_detail'] = $nodinDetailId;
            }

            if ($supportsVolumeSnapshot) {
                $detailRow['volume_planning_snapshot'] = (float) ($volumeSnapshots[$index] ?? $outstandingItemMap[$nodinDetailId]['volume_planning_final'] ?? $qty);
            }

            $details[] = $detailRow;
            $selectedDetailIds[$nodinDetailId] = true;
        }

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Tidak ada item valid yang bisa disimpan ke PO.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $isSuccess = $this->MLogistik_Pesanan_Pabrik->createPoFromApprovedPr($header, $details);
        $this->session->set_flashdata($isSuccess ? 'success' : 'error', $isSuccess ? 'PO berhasil dibuat dari detail NODIN approved.' : 'Gagal membuat PO dari detail NODIN approved.');
        redirect('Logistik_Pesanan_Pabrik');
    }

    private function generatePoId()
    {
        return substr((string) time() . mt_rand(10, 99), 0, 11);
    }

}
