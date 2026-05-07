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

        $nomor_po_pabrik = $nomor_po_pabrik !== null && $nomor_po_pabrik !== ''
            ? $nomor_po_pabrik
            : trim((string) $this->input->get('nomor_po_pabrik'));

        if ($nomor_po_pabrik === null || $nomor_po_pabrik === '') {
            $this->session->set_flashdata('error', 'Nomor PO tidak ditemukan.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $this->MLogistik_Pesanan_Pabrik->ensurePoDetailIdsByNomor($nomor_po_pabrik);

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
        $data['defaultHoGudang'] = $this->MLogistik_Pesanan_Pabrik->getDefaultHoGudang();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Logistik_Pesanan_Pabrik_Detail/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function print_po($nomor_po_pabrik = null)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $nomor_po_pabrik = $nomor_po_pabrik !== null && $nomor_po_pabrik !== ''
            ? $nomor_po_pabrik
            : trim((string) $this->input->get('nomor_po_pabrik'));

        if ($nomor_po_pabrik === null || $nomor_po_pabrik === '') {
            $this->session->set_flashdata('error', 'Nomor PO tidak ditemukan.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $this->MLogistik_Pesanan_Pabrik->ensurePoDetailIdsByNomor($nomor_po_pabrik);
        $header = $this->MLogistik_Pesanan_Pabrik->getPoPrintHeaderByNomor($nomor_po_pabrik);
        if (empty($header)) {
            $this->session->set_flashdata('error', 'Data PO untuk print tidak ditemukan.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $items = $this->MLogistik_Pesanan_Pabrik->getPoItemsByNomor($nomor_po_pabrik);
        $data = [
            'title' => 'Print PO',
            'poHeader' => $header,
            'poItems' => $items,
        ];

        $this->load->view('format_po_print', $data);
    }

    public function create_delivery()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $nomorPo = trim((string) $this->input->post('nomor_po_pabrik'));
        $this->MLogistik_Pesanan_Pabrik->ensurePoDetailIdsByNomor($nomorPo);
        $poHeader = $this->MLogistik_Pesanan_Pabrik->getPoHeaderByNomor($nomorPo);
        if (empty($poHeader)) {
            $this->session->set_flashdata('error', 'PO tidak ditemukan untuk proses pengiriman.');
            redirect('Logistik_Pesanan_Pabrik');
            return;
        }

        $noSuratJalan = trim((string) $this->input->post('no_surat_jalan'));
        $defaultHoGudang = $this->MLogistik_Pesanan_Pabrik->getDefaultHoGudang();
        $idLokasiGudang = (int) ($defaultHoGudang['id_lokasi_gudang'] ?? $this->input->post('id_lokasi_gudang'));
        $tanggalPengiriman = trim((string) $this->input->post('tanggal_pengiriman_pabrik'));
        $selectedItems = (array) $this->input->post('selected_item');
        $detailIdsByIndex = (array) $this->input->post('id_pesanan_pabrik_detail');
        $qtyKirimItems = (array) $this->input->post('qty_kirim');

        $this->logDeliveryDebug('POST_PAYLOAD', [
            'nomor_po_pabrik' => $nomorPo,
            'no_surat_jalan' => $noSuratJalan,
            'id_lokasi_gudang' => $idLokasiGudang,
            'tanggal_pengiriman_pabrik' => $tanggalPengiriman,
            'selected_item' => $selectedItems,
            'id_pesanan_pabrik_detail' => $detailIdsByIndex,
            'qty_kirim' => $qtyKirimItems,
        ]);

        if ($noSuratJalan === '' || $idLokasiGudang <= 0 || $tanggalPengiriman === '' || empty($selectedItems)) {
            $this->session->set_flashdata('error', 'Data pengiriman belum lengkap. Pastikan nomor surat jalan, gudang tujuan, tanggal kirim, dan item sudah dipilih.');
            redirect($this->buildPoDetailUrl($nomorPo));
            return;
        }

        $uploadedFiles = $this->uploadDeliveryDocuments($nomorPo, $noSuratJalan);
        if ($uploadedFiles === false) {
            redirect($this->buildPoDetailUrl($nomorPo));
            return;
        }

        $suratJalanPabrik = $uploadedFiles['surat_jalan_pabrik'] ?? '';
        $suratJalanHo = $uploadedFiles['surat_jalan_ho'] ?? '';

        $poItems = $this->MLogistik_Pesanan_Pabrik->getPoItemsByNomor($nomorPo);
        $poItemMap = [];
        foreach ($poItems as $item) {
            $detailId = (int) ($item['id_pesanan_pabrik_detail'] ?? 0);
            if ($detailId > 0) {
                $poItemMap[$detailId] = $item;
            }
        }

        $this->logDeliveryDebug('PO_ITEM_MAP', [
            'nomor_po_pabrik' => $nomorPo,
            'po_items_count' => count($poItems),
            'po_item_map_keys' => array_keys($poItemMap),
            'po_items' => $poItems,
        ]);

        $deliveryDetails = [];
        $selectedDetailIds = [];
        $selectedItemMap = [];
        foreach ($selectedItems as $detailIdValue) {
            $detailId = (int) $detailIdValue;
            if ($detailId > 0) {
                $selectedItemMap[$detailId] = true;
            }
        }

        foreach ($qtyKirimItems as $detailIdKey => $qtyKirimValue) {
            $detailId = (int) $detailIdKey;
            if ($detailId <= 0 || !isset($selectedItemMap[$detailId])) {
                $this->logDeliveryDebug('SKIP_QTY_LOOP', [
                    'reason' => 'detail_id_not_selected_or_invalid',
                    'detail_id_key' => $detailIdKey,
                    'detail_id_int' => $detailId,
                    'selected_map_keys' => array_keys($selectedItemMap),
                ]);
                continue;
            }

            if (!isset($poItemMap[$detailId])) {
                $this->logDeliveryDebug('INVALID_PO_ITEM', [
                    'detail_id' => $detailId,
                    'po_item_map_keys' => array_keys($poItemMap),
                ]);
                $this->session->set_flashdata('error', 'Ada item PO yang tidak valid untuk pengiriman. Silakan muat ulang detail PO.');
                redirect($this->buildPoDetailUrl($nomorPo));
                return;
            }

            if (isset($selectedDetailIds[$detailId])) {
                $this->session->set_flashdata('error', 'Terdapat item PO yang dipilih dobel pada form pengiriman.');
                redirect($this->buildPoDetailUrl($nomorPo));
                return;
            }

            $qtyKirim = (float) $qtyKirimValue;
            $maxOutstanding = (float) ($poItemMap[$detailId]['outstanding_pengiriman'] ?? 0);
            if ($qtyKirim <= 0 || $qtyKirim > $maxOutstanding) {
                $this->logDeliveryDebug('INVALID_QTY_PRIMARY', [
                    'detail_id' => $detailId,
                    'qty_kirim' => $qtyKirim,
                    'max_outstanding' => $maxOutstanding,
                    'po_item' => $poItemMap[$detailId] ?? null,
                ]);
                $itemName = (string) ($poItemMap[$detailId]['nama_item'] ?? 'Item');
                $this->session->set_flashdata('error', $itemName . ' hanya memiliki outstanding pengiriman ' . number_format($maxOutstanding, 0, ',', '.') . '. Qty kirim tidak boleh melebihi outstanding.');
                redirect($this->buildPoDetailUrl($nomorPo));
                return;
            }

            $deliveryDetails[] = [
                'id_pesanan_pabrik_detail' => $detailId,
                'qty_item' => $qtyKirim,
            ];
            $selectedDetailIds[$detailId] = true;
        }

        if (empty($deliveryDetails) && !empty($detailIdsByIndex)) {
            foreach ($detailIdsByIndex as $rowIndex => $detailIdValue) {
                $detailId = (int) $detailIdValue;
                if ($detailId <= 0 || !isset($selectedItemMap[$detailId])) {
                    $this->logDeliveryDebug('SKIP_FALLBACK_LOOP', [
                        'row_index' => $rowIndex,
                        'detail_id_value' => $detailIdValue,
                        'detail_id_int' => $detailId,
                        'selected_map_keys' => array_keys($selectedItemMap),
                    ]);
                    continue;
                }

                if (!isset($poItemMap[$detailId])) {
                    $this->logDeliveryDebug('INVALID_PO_ITEM_FALLBACK', [
                        'row_index' => $rowIndex,
                        'detail_id' => $detailId,
                        'po_item_map_keys' => array_keys($poItemMap),
                    ]);
                    $this->session->set_flashdata('error', 'Ada item PO yang tidak valid untuk pengiriman. Silakan muat ulang detail PO.');
                    redirect($this->buildPoDetailUrl($nomorPo));
                    return;
                }

                if (isset($selectedDetailIds[$detailId])) {
                    continue;
                }

                $qtyKirim = 0;
                if (isset($qtyKirimItems[$detailId])) {
                    $qtyKirim = (float) $qtyKirimItems[$detailId];
                } elseif (isset($qtyKirimItems[$rowIndex])) {
                    $qtyKirim = (float) $qtyKirimItems[$rowIndex];
                } else {
                    $qtyKirim = (float) ($poItemMap[$detailId]['outstanding_pengiriman'] ?? 0);
                }

                $maxOutstanding = (float) ($poItemMap[$detailId]['outstanding_pengiriman'] ?? 0);
                if ($qtyKirim <= 0 || $qtyKirim > $maxOutstanding) {
                    $this->logDeliveryDebug('INVALID_QTY_FALLBACK', [
                        'row_index' => $rowIndex,
                        'detail_id' => $detailId,
                        'qty_kirim' => $qtyKirim,
                        'max_outstanding' => $maxOutstanding,
                        'qty_kirim_items' => $qtyKirimItems,
                    ]);
                    $itemName = (string) ($poItemMap[$detailId]['nama_item'] ?? 'Item');
                    $this->session->set_flashdata('error', $itemName . ' hanya memiliki outstanding pengiriman ' . number_format($maxOutstanding, 0, ',', '.') . '. Qty kirim tidak boleh melebihi outstanding.');
                    redirect($this->buildPoDetailUrl($nomorPo));
                    return;
                }

                $deliveryDetails[] = [
                    'id_pesanan_pabrik_detail' => $detailId,
                    'qty_item' => $qtyKirim,
                ];
                $selectedDetailIds[$detailId] = true;
            }
        }

        $this->logDeliveryDebug('DELIVERY_DETAILS_RESULT', [
            'nomor_po_pabrik' => $nomorPo,
            'delivery_details' => $deliveryDetails,
            'selected_detail_ids' => array_keys($selectedDetailIds),
        ]);

        if (empty($deliveryDetails)) {
            $this->session->set_flashdata('error', 'Tidak ada item pengiriman valid yang bisa disimpan.');
            redirect($this->buildPoDetailUrl($nomorPo));
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
        if ($this->db->field_exists('id_pengiriman_pabrik', 'tb_logistik_pengiriman_pabrik')) {
            $headerPayload['id_pengiriman_pabrik'] = $this->getNextIntId('tb_logistik_pengiriman_pabrik', 'id_pengiriman_pabrik');
        }
        if ($this->db->field_exists('nomor_po_pabrik', 'tb_logistik_pengiriman_pabrik')) {
            $headerPayload['nomor_po_pabrik'] = $nomorPo;
        }
        if ($this->db->field_exists('status_penerimaan', 'tb_logistik_pengiriman_pabrik')) {
            $headerPayload['status_penerimaan'] = 'RECEIVED';
        }
        if ($this->db->field_exists('catatan_pengiriman', 'tb_logistik_pengiriman_pabrik')) {
            $headerPayload['catatan_pengiriman'] = 'Dibuat dari detail PO. Material diterima langsung ke gudang HO berdasarkan surat jalan pabrik.';
        }

        $this->db->trans_start();
        $this->db->insert('tb_logistik_pengiriman_pabrik', $headerPayload);
        $idPengiriman = (int) ($headerPayload['id_pengiriman_pabrik'] ?? $this->db->insert_id());

        foreach ($deliveryDetails as $detailIndex => $detail) {
            $detailPayload = [
                'id_pengiriman_pabrik' => $idPengiriman,
                'id_pesanan_pabrik_detail' => $detail['id_pesanan_pabrik_detail'],
                'qty_item' => $detail['qty_item'],
            ];

            if ($this->db->field_exists('id_pengiriman_pabrik_detail', 'tb_logistik_pengiriman_pabrik_detail')) {
                $detailPayload['id_pengiriman_pabrik_detail'] = $this->getNextIntId('tb_logistik_pengiriman_pabrik_detail', 'id_pengiriman_pabrik_detail', $detailIndex);
            }

            if ($this->db->field_exists('qty_diterima', 'tb_logistik_pengiriman_pabrik_detail')) {
                $detailPayload['qty_diterima'] = $detail['qty_item'];
            }

            $this->db->insert('tb_logistik_pengiriman_pabrik_detail', $detailPayload);

            $poItem = $poItemMap[(int) $detail['id_pesanan_pabrik_detail']] ?? null;
            if (!empty($poItem)) {
                $idKodeItem = (int) ($poItem['id_kode_item'] ?? 0);
                $bowheerId = $this->MLogistik_Pesanan_Pabrik->getPreferredBowheerId($idLokasiGudang, $idKodeItem);
                $tanggalFormatted = "{$tanggalPengiriman} " . date('H:i:s');
                $createdAt = date('Y-m-d H:i:s');

                $this->db->insert('tb_logistik_stok', [
                    'no_surat_jalan' => $noSuratJalan,
                    'id_lokasi_gudang' => $idLokasiGudang,
                    'id_bowheer' => $bowheerId,
                    'id_sumber_material' => 7,
                    'id_kode_item' => $idKodeItem,
                    'jumlah_stok' => $detail['qty_item'],
                    'satuan_stok' => (string) ($poItem['satuan_item'] ?? ''),
                    'merk_stok' => '',
                    'no_haspel_stok' => '',
                    'no_ref_stok' => '',
                    'keterangan_stok' => 'Auto penerimaan dari pabrik ke HO. PO: ' . $nomorPo,
                    'tanggal_upload_stok' => $tanggalFormatted,
                    'surat_jalan' => $suratJalanPabrik,
                    'evidence' => $suratJalanHo,
                    'no_po_logistik' => $nomorPo,
                    'no_pr_logistik' => $poItem['nomor_purchase_request'] ?? null,
                    'id_lokasi_gudang_pengiriman' => null,
                    'id_user' => $this->session->userdata('id_user'),
                    'CREATED_AT' => $createdAt,
                ]);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('error', 'Gagal menyimpan pengiriman pabrik.');
        } else {
            $this->session->set_flashdata('success', 'Pengiriman pabrik berhasil disimpan dan stok otomatis masuk ke gudang HO.');
        }

        redirect($this->buildPoDetailUrl($nomorPo));
    }

    private function uploadDeliveryDocuments($nomorPo, $noSuratJalan)
    {
        $fieldConfig = [
            'file_surat_jalan_pabrik' => 'surat_jalan_pabrik',
            'file_evidence_ho' => 'surat_jalan_ho',
        ];

        $uploadPath = './uploads/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $this->load->library('upload');
        $uploadedFiles = [];
        $safeNomorPo = preg_replace('/[^A-Za-z0-9_-]/', '_', $nomorPo);
        $safeNoSuratJalan = preg_replace('/[^A-Za-z0-9_-]/', '_', $noSuratJalan);

        foreach ($fieldConfig as $fieldName => $targetKey) {
            if (!isset($_FILES[$fieldName]) || (int) $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
                $this->session->set_flashdata('error', 'File surat jalan pabrik dan evidence wajib diunggah.');
                return false;
            }

            if ((int) $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
                $this->session->set_flashdata('error', 'Terjadi kesalahan saat upload dokumen pengiriman.');
                return false;
            }

            $extension = strtolower((string) pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
            $prefix = $targetKey === 'surat_jalan_pabrik' ? 'DELIVERY_SJ_PABRIK' : 'DELIVERY_EVIDENCE_HO';
            $fileName = $prefix . '_' . $safeNomorPo . '_' . $safeNoSuratJalan . '_' . date('Ymd_His') . ($extension !== '' ? '.' . $extension : '');
            $config = [
                'upload_path' => $uploadPath,
                'allowed_types' => 'pdf|jpg|jpeg|png',
                'max_size' => 5120,
                'file_name' => $fileName,
            ];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload($fieldName)) {
                $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                return false;
            }

            $uploadedFiles[$targetKey] = './uploads/' . $this->upload->data('file_name');
        }

        return $uploadedFiles;
    }

    private function logDeliveryDebug($label, $payload)
    {
        log_message('error', 'LOGISTIK_DELIVERY_DEBUG ' . $label . ' ' . json_encode($payload));
    }

    private function getNextIntId($table, $column, $offset = 0)
    {
        $row = $this->db
            ->select('MAX(' . $column . ') AS max_id', false)
            ->from($table)
            ->get()
            ->row_array();

        return ((int) ($row['max_id'] ?? 0)) + 1 + (int) $offset;
    }

    private function buildPoDetailUrl($nomorPo)
    {
        return 'Logistik_Pesanan_Pabrik_Detail/detailPesanan?nomor_po_pabrik=' . rawurlencode((string) $nomorPo);
    }
}
