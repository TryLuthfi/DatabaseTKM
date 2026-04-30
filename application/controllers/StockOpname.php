<?php
defined('BASEPATH') or exit('No direct script access allowed');

class StockOpname extends CI_Controller
{

    public function __construct()
    {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MStockOpname');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $data = $this->buildPeriodeData();
            $this->renderStockOpnameView('StockOpname/soperiode', $data);
        } else {
            redirect('Auth');
        }
    }

    public function revamp()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $data = $this->buildPeriodeData();
            $data['title'] = 'STOCK OPNAME LOGISTIK REVAMP';
            $data['judul'] = 'STOCK OPNAME LOGISTIK REVAMP';
            $this->renderStockOpnameView('StockOpname/revamp_periode', $data);
        } else {
            redirect('Auth');
        }
    }

    public function periode($id_sop = null, $id_lokasi_gudang = null)
    {
        if (!empty($this->session->userdata('id_user'))) {
            $data = $this->buildPeriodeDetailData($id_sop, $id_lokasi_gudang);
            $view = ($id_lokasi_gudang !== null) ? 'StockOpname/soitem' : 'StockOpname/sokota';
            $this->renderStockOpnameView($view, $data);
        } else {
            redirect('Auth');
        }
    }

    public function periode_revamp($id_sop = null, $id_lokasi_gudang = null)
    {
        if (!empty($this->session->userdata('id_user'))) {
            $data = $this->buildPeriodeDetailData($id_sop, $id_lokasi_gudang);
            $data['title'] = ($id_lokasi_gudang !== null) ? 'INPUT STOCK OPNAME REVAMP' : 'DETAIL STOCK OPNAME REVAMP';
            $data['judul'] = ($id_lokasi_gudang !== null) ? 'INPUT STOCK OPNAME REVAMP' : 'DETAIL STOCK OPNAME REVAMP';
            $view = ($id_lokasi_gudang !== null) ? 'StockOpname/revamp_soitem' : 'StockOpname/revamp_sokota';
            $this->renderStockOpnameView($view, $data);
        } else {
            redirect('Auth');
        }
    }

    public function inputSO()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $id_sop = $this->input->post('id_sop');
            $id_lokasi_gudang = $this->input->post('id_lokasi_gudang');
            $id_kode_item = $this->input->post('id_kode_item');
            $total_jumlah_stok = $this->input->post('total_jumlah_stok');
            $stok_so = $this->input->post('stok_so');
            $stok_soi_edit = $this->input->post('soi_stok_opname');
            $keterangan = $this->input->post('keterangan');
            $redirectTarget = $this->input->post('redirect_target');
            $userId = (int) $this->session->userdata('id_user');

            if (!$this->validateStockOpnameRemarks($id_kode_item, $total_jumlah_stok, $stok_so, $stok_soi_edit, $keterangan)) {
                $this->session->set_flashdata('error', 'Remarks wajib diisi untuk setiap item yang memiliki selisih stok.');
                redirect($redirectTarget ? $redirectTarget : 'StockOpname/periode/' . $id_sop);
                return;
            }

            $hasDiscrepancy = false;

            if ($this->input->post('is_edit')) {
                $this->db->trans_start();

                foreach ($id_kode_item as $index => $kode_item) {
                    $stokAplikasi = $this->normalizeStockValue($this->input->post('soi_stok_asli')[$index] ?? ($total_jumlah_stok[$index] ?? 0));
                    $stokFisik = $this->normalizeStockValue($stok_soi_edit[$index] ?? 0);
                    if ($stokAplikasi !== $stokFisik) {
                        $hasDiscrepancy = true;
                    }

                    $this->db->where('id_sop', $id_sop);
                    $this->db->where('id_kota_gudang', $id_lokasi_gudang);
                    $this->db->where('id_kode_item', $kode_item);
                    $this->db->update('tb_so_item', [
                        'soi_stok_opname' => $stok_soi_edit[$index] ?? 0,
                        'soi_keterangan'  => $keterangan[$index] ?? '',
                        'soi_remarks' => $keterangan[$index] ?? ''
                    ]);
                }

                $soKotaId = $this->MStockOpname->upsertSoKota($id_sop, $id_lokasi_gudang, [
                    'sok_status' => $hasDiscrepancy ? 'NEED BA' : 'DONE',
                    'sok_tanggal' => date('Y-m-d H:i:s'),
                    'submitted_by' => $userId,
                    'submitted_at' => date('Y-m-d H:i:s'),
                    'has_selisih' => $hasDiscrepancy ? 1 : 0,
                    'needs_ba' => $hasDiscrepancy ? 1 : 0,
                    'is_adjusted' => 0,
                ]);
                $this->MStockOpname->syncSOItemDiscrepancy($id_sop, $id_lokasi_gudang);
                $this->MStockOpname->addSOApprovalLog([
                    'id_so_kota' => $soKotaId,
                    'status_from' => null,
                    'status_to' => $hasDiscrepancy ? 'NEED BA' : 'DONE',
                    'action_by' => $userId,
                    'action_note' => 'Update hasil stock opname.',
                ]);
                $this->MStockOpname->updatePeriodeStatusFromItems($id_sop);

                $this->db->trans_complete();
                $this->session->set_flashdata('success', $hasDiscrepancy
                    ? 'Data stok opname berhasil disimpan. Item selisih wajib ditindaklanjuti dengan BA kronologi.'
                    : 'Data stok opname berhasil disimpan.');
            } else {
                $data_insert = [];
                foreach ($id_kode_item as $index => $kode_item) {
                    $stokAplikasi = $this->normalizeStockValue($total_jumlah_stok[$index] ?? 0);
                    $stokFisik = $this->normalizeStockValue(!empty($stok_so[$index]) ? $stok_so[$index] : (!empty($stok_soi_edit[$index]) ? $stok_soi_edit[$index] : 0));
                    if ($stokAplikasi !== $stokFisik) {
                        $hasDiscrepancy = true;
                    }

                    $data_insert[] = [
                        'id_sop' => $id_sop,
                        'id_kota_gudang' => $id_lokasi_gudang,
                        'id_kode_item' => $kode_item,
                        'soi_stok_asli' => $stokAplikasi,
                        'soi_stok_opname' => $stokFisik,
                        'soi_keterangan' => $keterangan[$index] ?? '',
                        'soi_remarks' => $keterangan[$index] ?? ''
                    ];
                }

                if (!empty($data_insert)) {
                    $this->db->trans_start();
                    $this->MStockOpname->insertBatchSOItem($data_insert);
                    $soKotaId = $this->MStockOpname->upsertSoKota($id_sop, $id_lokasi_gudang, [
                        'sok_status' => $hasDiscrepancy ? 'NEED BA' : 'DONE',
                        'sok_tanggal' => date('Y-m-d H:i:s'),
                        'submitted_by' => $userId,
                        'submitted_at' => date('Y-m-d H:i:s'),
                        'has_selisih' => $hasDiscrepancy ? 1 : 0,
                        'needs_ba' => $hasDiscrepancy ? 1 : 0,
                        'is_adjusted' => 0,
                    ]);
                    $this->MStockOpname->syncSOItemDiscrepancy($id_sop, $id_lokasi_gudang);
                    $this->MStockOpname->addSOApprovalLog([
                        'id_so_kota' => $soKotaId,
                        'status_from' => 'NOT YET',
                        'status_to' => $hasDiscrepancy ? 'NEED BA' : 'DONE',
                        'action_by' => $userId,
                        'action_note' => 'Submit hasil stock opname.',
                    ]);
                    $this->MStockOpname->updatePeriodeStatusFromItems($id_sop);
                    $this->db->trans_complete();
                    $this->session->set_flashdata('success', $hasDiscrepancy
                        ? 'Data stok opname berhasil disimpan. Terdapat selisih stok, siapkan BA kronologi sebelum adjustment.'
                        : 'Data stok opname berhasil disimpan.');
                } else {
                    $this->session->set_flashdata('error', 'Tidak ada data stok opname yang disimpan.');
                }
            }
            redirect($redirectTarget ? $redirectTarget : 'StockOpname/periode/' . $id_sop);
        } else {
            redirect('Auth');
        }
    }

    public function generateBA($id_sop, $id_lokasi_gudang)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
        }

        $context = $this->buildBAContext($id_sop, $id_lokasi_gudang);
        if (empty($context['soKota'])) {
            $this->session->set_flashdata('error', 'Data stock opname area belum tersedia.');
            redirect('StockOpname/revamp/periode/' . $id_sop . '/lokasi/' . $id_lokasi_gudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        if (empty($context['discrepancyItems'])) {
            $this->session->set_flashdata('error', 'Tidak ada item selisih yang perlu dibuatkan BA.');
            redirect('StockOpname/revamp/periode/' . $id_sop . '/lokasi/' . $id_lokasi_gudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        foreach ($context['discrepancyItems'] as $item) {
            $remarks = trim((string) (($item['soi_remarks'] ?? '') !== '' ? $item['soi_remarks'] : ($item['soi_keterangan'] ?? '')));
            if ($remarks === '') {
                $this->session->set_flashdata('error', 'Masih ada item selisih tanpa remarks. Lengkapi dulu sebelum generate BA.');
                redirect('StockOpname/revamp/periode/' . $id_sop . '/lokasi/' . $id_lokasi_gudang . '?mode=de95b43bceeb4b998aed4aed5cef1ae7');
                return;
            }
        }

        $userId = (int) $this->session->userdata('id_user');
        $now = date('Y-m-d H:i:s');
        $periode = $context['periode'];
        $lokasi = $context['lokasi'];
        $nomorBa = sprintf(
            'BA-SO/%s/%s/%s/%04d',
            (int) $id_sop,
            (int) $id_lokasi_gudang,
            date('Ym'),
            (int) ($context['existingBA']['id_so_ba'] ?? 0)
        );

        $payload = [
            'id_so_periode' => (int) $id_sop,
            'id_so_kota' => (int) $context['soKota']['id_so_kota'],
            'nomor_ba' => $nomorBa,
            'ba_tanggal' => date('Y-m-d'),
            'ba_status' => 'GENERATED',
            'ba_generated_at' => $now,
            'ba_submitted_by' => $userId,
        ];

        $this->db->trans_start();
        $baId = $this->MStockOpname->saveBADraft($payload);
        if (empty($context['existingBA'])) {
            $nomorBa = sprintf('BA-SO/%s/%s/%s/%04d', (int) $id_sop, (int) $id_lokasi_gudang, date('Ym'), $baId);
            $this->db->where('id_so_ba', $baId)->update('tb_so_ba', ['nomor_ba' => $nomorBa]);
        }

        $baItems = [];
        foreach ($context['discrepancyItems'] as $item) {
            $remarks = trim((string) (($item['soi_remarks'] ?? '') !== '' ? $item['soi_remarks'] : ($item['soi_keterangan'] ?? '')));
            $baItems[] = [
                'id_so_ba' => $baId,
                'id_so_item' => (int) $item['id_so_item'],
                'id_kode_item' => (int) $item['id_kode_item'],
                'stok_aplikasi' => (int) ($item['soi_stok_asli'] ?? 0),
                'stok_fisik' => (int) ($item['soi_stok_opname'] ?? 0),
                'selisih' => (int) ($item['soi_selisih'] ?? ((int) ($item['soi_stok_asli'] ?? 0) - (int) ($item['soi_stok_opname'] ?? 0))),
                'remarks' => $remarks,
                'kronologi' => $remarks,
            ];
        }
        $this->MStockOpname->replaceBAItems($baId, $baItems);
        $this->MStockOpname->updateSoKotaById((int) $context['soKota']['id_so_kota'], [
            'sok_status' => 'BA DRAFT',
            'needs_ba' => 1,
            'has_selisih' => 1,
        ]);
        $this->MStockOpname->addSOApprovalLog([
            'id_so_kota' => (int) $context['soKota']['id_so_kota'],
            'status_from' => $context['soKota']['sok_status'] ?? 'NEED BA',
            'status_to' => 'BA DRAFT',
            'action_by' => $userId,
            'action_note' => 'Generate BA kronologi untuk ' . ($lokasi['kota_lokasi_gudang'] ?? 'area'),
        ]);
        $this->MStockOpname->updatePeriodeStatusFromItems($id_sop);
        $this->db->trans_complete();

        redirect('StockOpname/print_ba/' . $id_sop . '/' . $id_lokasi_gudang);
    }

    public function print_ba($id_sop, $id_lokasi_gudang)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
        }

        $data = $this->buildBAContext($id_sop, $id_lokasi_gudang);
        if (empty($data['soKota']) || empty($data['existingBA'])) {
            $this->session->set_flashdata('error', 'BA kronologi belum tersedia untuk area ini.');
            redirect('StockOpname/revamp/periode/' . $id_sop . '/lokasi/' . $id_lokasi_gudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        $data['title'] = 'PRINT BA STOCK OPNAME';
        $this->load->view('StockOpname/print_ba', $data);
    }

    public function uploadSignedBA()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
        }

        $idSoBa = (int) $this->input->post('id_so_ba');
        $idSop = (int) $this->input->post('id_sop');
        $idLokasiGudang = (int) $this->input->post('id_lokasi_gudang');
        $redirectTarget = $this->input->post('redirect_target');
        $ba = $this->db->get_where('tb_so_ba', ['id_so_ba' => $idSoBa])->row_array();

        if (empty($ba)) {
            $this->session->set_flashdata('error', 'BA tidak ditemukan.');
            redirect($redirectTarget ? $redirectTarget : 'StockOpname/revamp/periode/' . $idSop . '/lokasi/' . $idLokasiGudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        if (empty($_FILES['ba_file_signed']['name'])) {
            $this->session->set_flashdata('error', 'File BA signed wajib dipilih.');
            redirect($redirectTarget ? $redirectTarget : 'StockOpname/revamp/periode/' . $idSop . '/lokasi/' . $idLokasiGudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        $uploadDir = './uploads/stockopname_ba/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $config['upload_path'] = $uploadDir;
        $config['allowed_types'] = 'pdf|jpg|jpeg|png';
        $config['max_size'] = 5120;
        $config['file_name'] = 'BA_SIGNED_' . $idSoBa . '_' . date('YmdHis');
        $config['overwrite'] = false;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('ba_file_signed')) {
            $this->session->set_flashdata('error', strip_tags($this->upload->display_errors('', '')));
            redirect($redirectTarget ? $redirectTarget : 'StockOpname/revamp/periode/' . $idSop . '/lokasi/' . $idLokasiGudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        $fileData = $this->upload->data();
        $userId = (int) $this->session->userdata('id_user');

        $this->db->trans_start();
        $this->db->where('id_so_ba', $idSoBa)->update('tb_so_ba', [
            'ba_file_signed' => 'uploads/stockopname_ba/' . $fileData['file_name'],
            'ba_status' => 'UPLOADED',
            'ba_uploaded_at' => date('Y-m-d H:i:s'),
        ]);
        $soKota = $this->MStockOpname->getSoKotaByPeriodeLokasi($idSop, $idLokasiGudang);
        if ($soKota) {
            $this->MStockOpname->updateSoKotaById((int) $soKota['id_so_kota'], [
                'sok_status' => 'WAITING APPROVAL',
            ]);
            $this->MStockOpname->addSOApprovalLog([
                'id_so_kota' => (int) $soKota['id_so_kota'],
                'status_from' => $soKota['sok_status'] ?? 'BA DRAFT',
                'status_to' => 'WAITING APPROVAL',
                'action_by' => $userId,
                'action_note' => 'Upload BA signed.',
            ]);
        }
        $this->db->trans_complete();

        $this->session->set_flashdata('success', 'BA signed berhasil diupload dan menunggu approval.');
        redirect($redirectTarget ? $redirectTarget : 'StockOpname/revamp/periode/' . $idSop . '/lokasi/' . $idLokasiGudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
    }

    public function approveBA()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
        }

        $idSoBa = (int) $this->input->post('id_so_ba');
        $idSop = (int) $this->input->post('id_sop');
        $idLokasiGudang = (int) $this->input->post('id_lokasi_gudang');
        $redirectTarget = $this->input->post('redirect_target');
        $approvalNote = trim((string) $this->input->post('approval_note'));
        $userId = (int) $this->session->userdata('id_user');

        $context = $this->buildBAContext($idSop, $idLokasiGudang);
        $ba = $context['existingBA'];
        $soKota = $context['soKota'];

        if (empty($ba) || (int) ($ba['id_so_ba'] ?? 0) !== $idSoBa) {
            $this->session->set_flashdata('error', 'BA tidak ditemukan untuk diproses approval.');
            redirect($redirectTarget ? $redirectTarget : 'StockOpname/revamp/periode/' . $idSop . '/lokasi/' . $idLokasiGudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        if (empty($ba['ba_file_signed'])) {
            $this->session->set_flashdata('error', 'BA signed belum diupload.');
            redirect($redirectTarget ? $redirectTarget : 'StockOpname/revamp/periode/' . $idSop . '/lokasi/' . $idLokasiGudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        $baItems = $this->MStockOpname->getSOBAItems($idSoBa);
        if (empty($baItems)) {
            $this->session->set_flashdata('error', 'Item BA tidak ditemukan.');
            redirect($redirectTarget ? $redirectTarget : 'StockOpname/revamp/periode/' . $idSop . '/lokasi/' . $idLokasiGudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        $sourceIn = $this->MStockOpname->getMasterSumberMaterialByName('SO Adjustment In');
        $sourceOut = $this->MStockOpname->getMasterSumberMaterialByName('SO Adjustment Out');
        if (empty($sourceIn) || empty($sourceOut)) {
            $this->session->set_flashdata('error', 'Master sumber material SO Adjustment belum tersedia.');
            redirect($redirectTarget ? $redirectTarget : 'StockOpname/revamp/periode/' . $idSop . '/lokasi/' . $idLokasiGudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        $existingAdjustments = $this->db
            ->from('tb_logistik_stok')
            ->where('ref_module', 'STOCK_OPNAME')
            ->where('ref_id', (int) $soKota['id_so_kota'])
            ->count_all_results();

        if ($existingAdjustments > 0) {
            $this->session->set_flashdata('error', 'Adjustment untuk BA ini sudah pernah dibuat.');
            redirect($redirectTarget ? $redirectTarget : 'StockOpname/revamp/periode/' . $idSop . '/lokasi/' . $idLokasiGudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        $adjustmentRows = [];
        $now = date('Y-m-d H:i:s');
        foreach ($baItems as $item) {
            $selisih = (int) ($item['selisih'] ?? 0);
            if ($selisih === 0) {
                continue;
            }

            $itemMeta = $this->MStockOpname->getKodeItemById((int) $item['id_kode_item']);
            $bowheerId = $this->MStockOpname->getPreferredBowheerId($idLokasiGudang, (int) $item['id_kode_item']);
            $sourceMaterialId = $selisih > 0 ? (int) $sourceOut['id_sumber_material'] : (int) $sourceIn['id_sumber_material'];
            $qtyAdjustment = abs($selisih);
            $remarks = trim((string) ($item['remarks'] ?? $approvalNote));

            $adjustmentRows[] = [
                'no_surat_jalan' => 'SO-ADJ-' . $idSoBa,
                'id_lokasi_gudang' => (int) $idLokasiGudang,
                'id_bowheer' => $bowheerId,
                'id_sumber_material' => $sourceMaterialId,
                'id_kode_item' => (int) $item['id_kode_item'],
                'jumlah_stok' => $qtyAdjustment,
                'satuan_stok' => $itemMeta['satuan_item'] ?? '-',
                'merk_stok' => '',
                'no_haspel_stok' => '',
                'no_ref_stok' => '',
                'keterangan_stok' => 'Adjustment otomatis Stock Opname. BA: ' . ($ba['nomor_ba'] ?? '-') . '. ' . $remarks,
                'tanggal_upload_stok' => $now,
                'id_user' => $userId,
                'surat_jalan' => '',
                'evidence' => $ba['ba_file_signed'],
                'no_pr_logistik' => null,
                'no_po_logistik' => null,
                'id_lokasi_gudang_pengiriman' => null,
                'status_approve_sm' => 1,
                'CREATED_AT' => $now,
                'ref_module' => 'STOCK_OPNAME',
                'ref_id' => (int) $soKota['id_so_kota'],
                'ref_detail_id' => (int) $item['id_so_item'],
                'ref_number' => $ba['nomor_ba'] ?? null,
                'is_system_generated' => 1,
                'approved_by' => $userId,
                'approved_at' => $now,
                'adjustment_reason' => $approvalNote !== '' ? $approvalNote : $remarks,
            ];
        }

        if (empty($adjustmentRows)) {
            $this->session->set_flashdata('error', 'Tidak ada adjustment yang perlu dibuat.');
            redirect($redirectTarget ? $redirectTarget : 'StockOpname/revamp/periode/' . $idSop . '/lokasi/' . $idLokasiGudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
            return;
        }

        $this->db->trans_start();
        $this->db->insert_batch('tb_logistik_stok', $adjustmentRows);
        $this->db->where('id_so_ba', $idSoBa)->update('tb_so_ba', [
            'ba_status' => 'APPROVED',
            'ba_approved_by' => $userId,
            'ba_approved_at' => $now,
            'ba_approval_notes' => $approvalNote,
        ]);
        foreach ($baItems as $item) {
            $selisih = (int) ($item['selisih'] ?? 0);
            $this->db->where('id_so_item', (int) $item['id_so_item'])->update('tb_so_item', [
                'soi_adjustment_status' => $selisih === 0 ? 'SYNC' : 'ADJUSTED',
                'soi_adjusted_qty' => abs($selisih),
                'soi_adjusted_at' => $now,
                'soi_adjusted_by' => $userId,
            ]);
        }
        $this->MStockOpname->updateSoKotaById((int) $soKota['id_so_kota'], [
            'sok_status' => 'ADJUSTED',
            'approved_by' => $userId,
            'approved_at' => $now,
            'is_adjusted' => 1,
            'adjusted_at' => $now,
            'needs_ba' => 0,
        ]);
        $this->MStockOpname->addSOApprovalLog([
            'id_so_kota' => (int) $soKota['id_so_kota'],
            'status_from' => $soKota['sok_status'] ?? 'WAITING APPROVAL',
            'status_to' => 'ADJUSTED',
            'action_by' => $userId,
            'action_note' => $approvalNote !== '' ? $approvalNote : 'BA disetujui dan adjustment otomatis dijalankan.',
        ]);
        $this->MStockOpname->updatePeriodeStatusFromItems($idSop);
        $this->db->trans_complete();

        $this->session->set_flashdata('success', 'BA disetujui dan adjustment otomatis berhasil dibuat.');
        redirect($redirectTarget ? $redirectTarget : 'StockOpname/revamp/periode/' . $idSop . '/lokasi/' . $idLokasiGudang . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
    }

    public function editSO()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $id_sop = $this->input->post('id_sop');
            $id_lokasi_gudang = $this->input->post('id_lokasi_gudang');

            $id_kode_item = $this->input->post('id_kode_item');
            $soi_stok_opname = $this->input->post('soi_stok_opname');
            $keterangan = $this->input->post('keterangan');

            foreach ($id_kode_item as $index => $kode_item) {
                $data_update = [
                    'soi_stok_opname' => $soi_stok_opname[$index] ?? 0,
                    'soi_keterangan' => $keterangan[$index] ?? ''
                ];

                $this->MStockOpname->updateSOItem($id_sop, $kode_item, $data_update);
            }

            $this->session->set_flashdata('success', 'Data stok opname berhasil diperbarui.');

            // Redirect kembali ke halaman periode
            redirect('StockOpname/periode/' . $id_sop);
        } else {
            redirect('Auth');
        }
    }

    public function tambahPeriode()
    {

        $hasil_data = array(
            'sop_bulan' => $_POST['so_bulan'],
            'sop_tahun' => $_POST['so_tahun'],
            'sop_status' => 'NOT YET'
        );

        $res = $this->MStockOpname->tambahPeriode($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("StockOpname");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("StockOpname");
        }
    }

    public function hapusPeriode($id_sop)
    {
        $id_sop = array('id_sop' => $id_sop);
        $res = $this->MStockOpname->hapusPeriode($id_sop);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("StockOpname");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("StockOpname");
        }
    }

    public function hapusKota($id_so_kota)
    {
        $data_kota = $this->MStockOpname->getDataSoKota($id_so_kota);

        if ($data_kota) {
            $id_sop = $data_kota['id_so_periode'];
            $id_lokasi_gudang = $data_kota['id_kota'];

            // Hapus item stok opname sebelum hapus kota
            $this->MStockOpname->hapusItemSO($id_sop, $id_lokasi_gudang);

            // Hapus kota dengan fungsi yang sudah diperbaiki
            $res = $this->MStockOpname->hapusKotaById($id_so_kota);

            if ($res) {
                $this->session->set_flashdata('status', 'sukses_hapus');
            } else {
                $this->session->set_flashdata('status', 'gagal_hapus');
            }
        } else {
            $this->session->set_flashdata('status', 'data_tidak_ditemukan');
        }

        redirect("StockOpname/periode/" . $id_sop);
    }

    public function cekPeriode()
    {
        header('Content-Type: application/json'); // Tambahkan ini

        $selectedBulan = $this->input->post('selectedBulan');
        $selectedTahun = $this->input->post('selectedTahun');

        // Debugging: Log input yang masuk
        log_message('error', 'Cek Nomor Surat Jalan - Input: ' . json_encode($_POST));

        if (!$selectedBulan || !$selectedTahun) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap!']);
            return;
        }

        $cek = $this->db->get_where('tb_so_periode', [
            'sop_bulan' => $selectedBulan,
            'sop_tahun' => $selectedTahun
        ])->row_array();

        // Debugging: Log hasil query
        log_message('error', 'Query Cek: ' . $this->db->last_query());

        if ($cek) {
            die(json_encode(['status' => 'exists']));
        } else {
            die(json_encode(['status' => 'available']));
        }
    }

    private function buildPeriodeData()
    {
        $data['title'] = 'STOCK OPNAME LOGISTIK';
        $data['judul'] = 'STOCK OPNAME LOGISTIK';
        $data['getSOPeriode'] = $this->MStockOpname->getSOPeriode();
        return $data;
    }

    private function buildPeriodeDetailData($id_sop, $id_lokasi_gudang = null)
    {
        $bulan_mapping = [
            'JANUARI' => '01',
            'FEBRUARI' => '02',
            'MARET' => '03',
            'APRIL' => '04',
            'MEI' => '05',
            'JUNI' => '06',
            'JULI' => '07',
            'AGUSTUS' => '08',
            'SEPTEMBER' => '09',
            'OKTOBER' => '10',
            'NOVEMBER' => '11',
            'DESEMBER' => '12'
        ];

        $mode = $this->input->get('mode');
        $data['title'] = 'DETAIL STOCK OPNAME LOGISTIK';
        $data['judul'] = 'DETAIL STOCK OPNAME LOGISTIK';
        $data['id_sop'] = $id_sop;
        $data['mode'] = $mode;

        if ($id_lokasi_gudang !== null) {
            $data['id_lokasi_gudang'] = $id_lokasi_gudang;
            $data['getDetailSoPeriode'] = $this->MStockOpname->getDetailSoPeriode($id_sop);
            $bulan = $data['getDetailSoPeriode'][0]['sop_bulan'];
            $tahun = $data['getDetailSoPeriode'][0]['sop_tahun'];
            $bulan_angka = isset($bulan_mapping[$bulan]) ? $bulan_mapping[$bulan] : '01';
            $tanggal_sekarang = date('Y-m-d');
            $jam_menit = date('H:i:s');
            $tanggal_format = "{$tanggal_sekarang} {$jam_menit}";
            $data['snapshot_tanggal'] = "{$tahun}-{$bulan_angka}-01";
            $data['getSOItem'] = $this->MStockOpname->getSOItem($id_lokasi_gudang, $tanggal_format);
            $data['getDetailSoItem'] = $this->MStockOpname->getDetailSoItem($id_sop, $id_lokasi_gudang);
            $data = array_merge($data, $this->buildBAContext($id_sop, $id_lokasi_gudang));
        } else {
            $data['getSOKota'] = $this->MStockOpname->getSOKota($id_sop);
            $data['getDetailSoPeriode'] = $this->MStockOpname->getDetailSoPeriode($id_sop);
        }

        return $data;
    }

    private function renderStockOpnameView($view, $data)
    {
        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view($view, $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    private function normalizeStockValue($value)
    {
        return (int) preg_replace('/[^\d\-]/', '', (string) $value);
    }

    private function validateStockOpnameRemarks($idKodeItem, $stokAplikasi, $stokSo, $stokSoEdit, $keterangan)
    {
        if (empty($idKodeItem)) {
            return true;
        }

        foreach ($idKodeItem as $index => $kodeItem) {
            $appValue = $this->normalizeStockValue($stokAplikasi[$index] ?? ($this->input->post('soi_stok_asli')[$index] ?? 0));
            $soValue = $this->normalizeStockValue($stokSo[$index] ?? ($stokSoEdit[$index] ?? 0));
            $remarks = trim((string) ($keterangan[$index] ?? ''));

            if ($appValue !== $soValue && $remarks === '') {
                return false;
            }
        }

        return true;
    }

    private function buildBAContext($idSop, $idLokasiGudang)
    {
        $soKota = $this->MStockOpname->getSoKotaByPeriodeLokasi($idSop, $idLokasiGudang);
        $existingBA = !empty($soKota) ? $this->MStockOpname->getSOBABySoKota((int) $soKota['id_so_kota']) : null;
        $baItems = !empty($existingBA) ? $this->MStockOpname->getSOBAItems((int) $existingBA['id_so_ba']) : [];
        return [
            'soKota' => $soKota,
            'existingBA' => $existingBA,
            'existingBAItems' => $baItems,
            'discrepancyItems' => $this->MStockOpname->getSOItemDiscrepancies($idSop, $idLokasiGudang),
            'approvalLogs' => !empty($soKota) ? $this->MStockOpname->getSOApprovalLogs((int) $soKota['id_so_kota']) : [],
            'periode' => $this->MStockOpname->getPeriodeById($idSop),
            'lokasi' => $this->MStockOpname->getLokasiGudangById($idLokasiGudang),
        ];
    }
}
