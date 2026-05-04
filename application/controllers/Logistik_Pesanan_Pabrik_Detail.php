<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logistik_Pesanan_Pabrik_Detail extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MLogistik_Pesanan_Pabrik');
    }

    public function detailPesanan($nomor_po_pabrik = null)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if ($nomor_po_pabrik === null || $nomor_po_pabrik === '') {
            $this->session->set_flashdata('error', 'Nomor PO tidak ditemukan.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $header = $this->MLogistik_Pesanan_Pabrik->getPoHeaderByNomor($nomor_po_pabrik);
        if (empty($header)) {
            $this->session->set_flashdata('error', 'Detail PO tidak ditemukan.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $items = $this->MLogistik_Pesanan_Pabrik->getPoItemsByNomor($nomor_po_pabrik);
        $deliveries = $this->MLogistik_Pesanan_Pabrik->getPoDeliveriesByNomor($nomor_po_pabrik);

        $data['title'] = 'Detail PO Pabrik';
        $data['judul'] = 'Detail PO Pabrik';
        $data['poHeader'] = $header;
        $data['poItems'] = $items;
        $data['poDeliveries'] = $deliveries;
        $data['gudangOptions'] = $this->MLogistik_Pesanan_Pabrik->getAllGudang();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Logistik_Pesanan_Pabrik_Detail/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function create_delivery()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $nomorPo = trim((string) $this->input->post('nomor_po_pabrik'));
        $poHeader = $this->MLogistik_Pesanan_Pabrik->getPoHeaderByNomor($nomorPo);
        if (empty($poHeader)) {
            $this->session->set_flashdata('error', 'PO tidak ditemukan untuk proses pengiriman.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $noSuratJalan = trim((string) $this->input->post('no_surat_jalan'));
        $idLokasiGudang = (int) $this->input->post('id_lokasi_gudang');
        $tanggalPengiriman = trim((string) $this->input->post('tanggal_pengiriman_pabrik'));
        $suratJalanPabrik = trim((string) $this->input->post('surat_jalan_pabrik'));
        $suratJalanHo = trim((string) $this->input->post('surat_jalan_ho'));
        $selectedItems = (array) $this->input->post('selected_item');
        $detailIds = (array) $this->input->post('id_pesanan_pabrik_detail');
        $qtyKirimItems = (array) $this->input->post('qty_kirim');

        if ($noSuratJalan === '' || $idLokasiGudang <= 0 || $tanggalPengiriman === '' || empty($selectedItems)) {
            $this->session->set_flashdata('error', 'Data pengiriman belum lengkap. Pastikan nomor surat jalan, gudang tujuan, tanggal kirim, dan item sudah dipilih.');
            redirect('Logistik_Pesanan_Pabrik_Detail/detailPesanan/' . $nomorPo);
            return;
        }

        $poItems = $this->MLogistik_Pesanan_Pabrik->getPoItemsByNomor($nomorPo);
        $poItemMap = [];
        foreach ($poItems as $item) {
            $detailId = (int) ($item['id_pesanan_pabrik_detail'] ?? 0);
            if ($detailId > 0) {
                $poItemMap[$detailId] = $item;
            }
        }

        $sourcePabrik = $this->MLogistik_Pesanan_Pabrik->getMasterSumberMaterialByName('In (dari Pabrik)');
        if (empty($sourcePabrik['id_sumber_material'])) {
            $this->session->set_flashdata('error', 'Master sumber material "In (dari Pabrik)" belum tersedia.');
            redirect('Logistik_Pesanan_Pabrik_Detail/detailPesanan/' . $nomorPo);
            return;
        }

        $deliveryDetails = [];
        $stockRows = [];
        $selectedDetailIds = [];
        $now = date('Y-m-d H:i:s');

        foreach ($selectedItems as $index) {
            if (!isset($detailIds[$index], $qtyKirimItems[$index])) {
                continue;
            }

            $detailId = (int) $detailIds[$index];
            if ($detailId <= 0 || !isset($poItemMap[$detailId])) {
                $this->session->set_flashdata('error', 'Ada item PO yang tidak valid untuk pengiriman. Silakan muat ulang detail PO.');
                redirect('Logistik_Pesanan_Pabrik_Detail/detailPesanan/' . $nomorPo);
                return;
            }

            if (isset($selectedDetailIds[$detailId])) {
                $this->session->set_flashdata('error', 'Terdapat item PO yang dipilih dobel pada form pengiriman.');
                redirect('Logistik_Pesanan_Pabrik_Detail/detailPesanan/' . $nomorPo);
                return;
            }

            $qtyKirim = (float) $qtyKirimItems[$index];
            $maxOutstanding = (float) ($poItemMap[$detailId]['outstanding_pengiriman'] ?? 0);
            if ($qtyKirim <= 0 || $qtyKirim > $maxOutstanding) {
                $itemName = (string) ($poItemMap[$detailId]['nama_item'] ?? 'Item');
                $this->session->set_flashdata('error', $itemName . ' hanya memiliki outstanding pengiriman ' . number_format($maxOutstanding, 0, ',', '.') . '. Qty kirim tidak boleh melebihi outstanding.');
                redirect('Logistik_Pesanan_Pabrik_Detail/detailPesanan/' . $nomorPo);
                return;
            }

            $deliveryDetails[] = [
                'id_pesanan_pabrik_detail' => $detailId,
                'qty_item' => $qtyKirim,
            ];

            $bowheerId = $this->MLogistik_Pesanan_Pabrik->getPreferredBowheerId($idLokasiGudang, (int) $poItemMap[$detailId]['id_kode_item']);
            $stockRow = [
                'no_surat_jalan' => $noSuratJalan,
                'id_lokasi_gudang' => $idLokasiGudang,
                'id_bowheer' => $bowheerId,
                'id_sumber_material' => (int) $sourcePabrik['id_sumber_material'],
                'id_kode_item' => (int) $poItemMap[$detailId]['id_kode_item'],
                'jumlah_stok' => $qtyKirim,
                'satuan_stok' => (string) ($poItemMap[$detailId]['satuan_item'] ?? '-'),
                'merk_stok' => '',
                'no_haspel_stok' => '',
                'no_ref_stok' => '',
                'keterangan_stok' => 'Penerimaan otomatis dari pengiriman pabrik. PO: ' . $nomorPo . ', SJ: ' . $noSuratJalan,
                'tanggal_upload_stok' => $now,
                'id_user' => (int) $this->session->userdata('id_user'),
                'surat_jalan' => $suratJalanPabrik,
                'evidence' => $suratJalanHo,
                'no_pr_logistik' => $poHeader['nomor_purchase_request'] ?? null,
                'no_po_logistik' => $nomorPo,
                'id_lokasi_gudang_pengiriman' => null,
                'status_approve_sm' => 1,
                'CREATED_AT' => $now,
            ];

            if ($this->db->field_exists('ref_module', 'tb_logistik_stok')) {
                $stockRow['ref_module'] = 'PENGIRIMAN_PABRIK';
            }
            if ($this->db->field_exists('ref_number', 'tb_logistik_stok')) {
                $stockRow['ref_number'] = $noSuratJalan;
            }
            if ($this->db->field_exists('is_system_generated', 'tb_logistik_stok')) {
                $stockRow['is_system_generated'] = 1;
            }
            if ($this->db->field_exists('approved_by', 'tb_logistik_stok')) {
                $stockRow['approved_by'] = (int) $this->session->userdata('id_user');
            }
            if ($this->db->field_exists('approved_at', 'tb_logistik_stok')) {
                $stockRow['approved_at'] = $now;
            }

            $stockRows[$detailId] = $stockRow;
            $selectedDetailIds[$detailId] = true;
        }

        if (empty($deliveryDetails)) {
            $this->session->set_flashdata('error', 'Tidak ada item pengiriman valid yang bisa disimpan.');
            redirect('Logistik_Pesanan_Pabrik_Detail/detailPesanan/' . $nomorPo);
            return;
        }

        $headerPayload = [
            'no_surat_jalan' => $noSuratJalan,
            'id_lokasi_gudang' => $idLokasiGudang,
            'tanggal_pengiriman_pabrik' => $tanggalPengiriman,
            'surat_jalan_pabrik' => $suratJalanPabrik,
            'surat_jalan_ho' => $suratJalanHo,
            'id_user' => (int) $this->session->userdata('id_user'),
        ];

        if ($this->db->field_exists('id_pesanan_pabrik', 'tb_logistik_pengiriman_pabrik')) {
            $headerPayload['id_pesanan_pabrik'] = $poHeader['id_pesanan_pabrik'];
        }
        if ($this->db->field_exists('nomor_po_pabrik', 'tb_logistik_pengiriman_pabrik')) {
            $headerPayload['nomor_po_pabrik'] = $nomorPo;
        }
        if ($this->db->field_exists('status_penerimaan', 'tb_logistik_pengiriman_pabrik')) {
            $headerPayload['status_penerimaan'] = 'RECEIVED';
        }
        if ($this->db->field_exists('catatan_pengiriman', 'tb_logistik_pengiriman_pabrik')) {
            $headerPayload['catatan_pengiriman'] = 'Dibuat dari detail PO dan otomatis masuk stok logistik.';
        }

        $this->db->trans_start();
        $this->db->insert('tb_logistik_pengiriman_pabrik', $headerPayload);
        $idPengiriman = (int) $this->db->insert_id();

        foreach ($deliveryDetails as $detailIndex => $detail) {
            $detailPayload = [
                'id_pengiriman_pabrik' => $idPengiriman,
                'id_pesanan_pabrik_detail' => $detail['id_pesanan_pabrik_detail'],
                'qty_item' => $detail['qty_item'],
            ];

            if ($this->db->field_exists('qty_diterima', 'tb_logistik_pengiriman_pabrik_detail')) {
                $detailPayload['qty_diterima'] = $detail['qty_item'];
            }

            $this->db->insert('tb_logistik_pengiriman_pabrik_detail', $detailPayload);
            $insertedDetailId = (int) $this->db->insert_id();

            if ($this->db->field_exists('ref_id', 'tb_logistik_stok')) {
                $stockRows[$detail['id_pesanan_pabrik_detail']]['ref_id'] = $idPengiriman;
            }
            if ($this->db->field_exists('ref_detail_id', 'tb_logistik_stok')) {
                $stockRows[$detail['id_pesanan_pabrik_detail']]['ref_detail_id'] = $insertedDetailId;
            }

            $this->db->insert('tb_logistik_stok', $stockRows[$detail['id_pesanan_pabrik_detail']]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('error', 'Gagal menyimpan pengiriman pabrik dan stok masuk.');
        } else {
            $this->session->set_flashdata('success', 'Pengiriman pabrik berhasil disimpan dan stok logistik masuk otomatis tercatat.');
        }

        redirect('Logistik_Pesanan_Pabrik_Detail/detailPesanan/' . $nomorPo);
    }
}
