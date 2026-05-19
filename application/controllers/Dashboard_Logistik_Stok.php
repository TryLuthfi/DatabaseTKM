<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_Logistik_Stok extends CI_Controller
{

    public function __construct()
    {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MDashboard_Logistik_Stok');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {
            redirect('Dashboard_Logistik_Stok/revamp');
        } else {
            redirect('Auth');
        }
    }

    public function revamp()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $data = $this->buildDashboardData();
            $data['title'] = 'DASHBOARD LOGISTIK REVAMP';
            $data['judul'] = 'DASHBOARD LOGISTIK REVAMP';

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Dashboard_Logistik_Stok/revamp', $data);
            // $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function transit_history()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $shipmentRows = $this->MDashboard_Logistik_Stok->getTransitShipmentRows();
        $transitRows = array_values(array_filter($shipmentRows, static function ($row) {
            return (float) ($row['total_qty_outstanding'] ?? 0) > 0;
        }));

        $data = [
            'title' => 'PENGIRIMAN HO TRANSIT & HISTORY',
            'judul' => 'PENGIRIMAN HO TRANSIT & HISTORY',
            'shipmentRows' => $shipmentRows,
            'transitRows' => $transitRows,
            'transitCategoryCards' => $this->MDashboard_Logistik_Stok->getTransitShipmentCategoryCards(),
            'historyCategoryCards' => $this->MDashboard_Logistik_Stok->getHistoryShipmentCategoryCards(),
        ];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Dashboard_Logistik_Stok/transit_history', $data);
        $this->load->view('Templates/99_JS');
    }

    public function getTransitHistoryDetail()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $noSuratJalan = trim((string) $this->input->get('nomor_surat_jalan'));
        if ($noSuratJalan === '') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'items' => [],
                    'message' => 'Nomor surat jalan belum diisi.',
                ]));
            return;
        }

        $items = $this->MDashboard_Logistik_Stok->getHoShipmentDetailBySuratJalan($noSuratJalan);
        $header = !empty($items) ? $items[0] : null;

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'header' => $header,
                'items' => $items,
                'message' => empty($items) ? 'Detail pengiriman HO tidak ditemukan.' : '',
            ]));
    }

    public function getReportStokByData()
    {
        $dateStart = $this->input->get('dateStart');
        $data = $this->MDashboard_Logistik_Stok->getReportStokMaterial($dateStart);
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
    }

    public function bowheer($kategori_item)
    {
        $this->load->view('Templates/01_Header', $kategori_item);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Dashboard_Logistik_Stok/indexkategori', $kategori_item);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function getProjectByBowheer()
    {
        if (isset($_GET['id_bowheer']) && $_GET['id_bowheer'] !== '') {
            $id_bowheer = $_GET['id_bowheer'];
            if ($id_bowheer === null) {
                exit('Error: Variable $id_bowheer is null');
            }

            $data = $this->db->query('SELECT * FROM tb_master_logistik_kode_item join tb_master_bowheer ON tb_master_logistik_kode_item.id_bowheer_pemilik_item = tb_master_bowheer.id_bowheer WHERE project_item = "' . $id_bowheer . '"')->result_array();

            if ($data === null) {
                exit('Error: Query failed');
            }

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }
    }

    public function getStockItemsByGudang()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idLokasiGudang = (int) $this->input->get('id_lokasi_gudang');
        $projectItem = trim((string) $this->input->get('project_item'));

        $items = [];
        if ($idLokasiGudang > 0) {
            $items = $this->MDashboard_Logistik_Stok->getAvailableStockItemsByGudang($idLokasiGudang, $projectItem);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($items));
    }

    public function getPreviewSuratJalanNumber()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idLokasiGudang = (int) $this->input->get('id_lokasi_gudang');
        $tanggalDokumen = trim((string) $this->input->get('tanggal_upload_stok'));
        $preview = [];

        if ($idLokasiGudang > 0) {
            $preview = $this->MDashboard_Logistik_Stok->previewSuratJalanNumber($idLokasiGudang, $tanggalDokumen);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'nomor_surat_jalan' => (string) ($preview['nomor_surat_jalan'] ?? ''),
                'nomor_surat_jalan_seq' => (int) ($preview['nomor_surat_jalan_seq'] ?? 0),
                'nomor_surat_jalan_year' => (int) ($preview['nomor_surat_jalan_year'] ?? 0),
            ]));
    }

    public function tambahReportStokLogistik()
    {
        $id_sumber_material = $this->input->post('id_sumber_material');
        if ((string) $id_sumber_material === '1') {
            $this->handleHoReceipt();
            return;
        }
        if ((string) $id_sumber_material === '7') {
            $this->handlePabrikReceipt();
            return;
        }

        $this->load->helper('date');
        $this->load->library('upload');
        $upload_path = "./uploads/";
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $id_lokasi_gudang = $this->input->post('id_lokasi_gudang');
        $id_bowheer = $this->input->post('id_bowheer');
        $id_sumber_material = $this->input->post('id_sumber_material');
        $sourceRule = $this->MDashboard_Logistik_Stok->getSumberMaterialRuleById($id_sumber_material);
        $nomor_spk = trim((string) $this->input->post('nomor_spk'));
        $nomor_polisi = trim((string) $this->input->post('nomor_polisi'));
        $nama_mitra = trim((string) $this->input->post('nama_mitra'));
        $pic_mitra = trim((string) $this->input->post('pic_mitra'));
        $tanggal_estimasi_sampai = trim((string) $this->input->post('tanggal_estimasi_sampai'));
        $nama_ekspedisi = trim((string) $this->input->post('nama_ekspedisi'));
        $pic_ekspedisi = trim((string) $this->input->post('pic_ekspedisi'));
        $tanggal_upload_stok = $this->input->post('tanggal_upload_stok');
        $tanggal_pembuatan_stok = $this->input->post('tanggal_pembuatan_stok');
        $timestamp = date('_h_i_s');
        $uploaded_files = [];
        $files = [
            'file-sj' => "SURAT_JALAN_{$id_lokasi_gudang}_{$id_bowheer}_{$id_sumber_material}_TIME_{$tanggal_upload_stok}{$timestamp}",
            'file-evidence' => "EVIDENCE_{$id_lokasi_gudang}_{$id_bowheer}_{$id_sumber_material}_TIME_{$tanggal_upload_stok}{$timestamp}"
        ];

        foreach ($files as $field_name => $new_filename) {
            if (!empty($_FILES[$field_name]['name'])) {
                $file_ext = pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION);
                $config = [
                    'upload_path' => $upload_path,
                    'allowed_types' => 'pdf|jpg|jpeg|png',
                    'max_size' => 5120,
                    'file_name' => "{$new_filename}.{$file_ext}"
                ];

                $this->upload->initialize($config);

                if (!$this->upload->do_upload($field_name)) {
                    $this->session->set_flashdata('error', 'Format file tidak sesuai atau file terlalu besar!');
                    redirect('Dashboard_Logistik_Stok/revamp');
                } else {
                    $uploaded_files[$field_name] = $this->upload->data('file_name');
                }
            }
        }

        if (empty($uploaded_files)) {
            $this->session->set_flashdata('error', 'Gagal mengupload dokumen.');
            redirect('Dashboard_Logistik_Stok/revamp');
        }

        $data_insert = [];
        $jumlah_stok = preg_replace('/\D/', '', $this->input->post('jumlah_stok'));
        $idKodeItems = (array) $this->input->post('id_kode_item');
        $sumberMaterial = $this->MDashboard_Logistik_Stok->getSumberMaterialById($id_sumber_material);
        $isOutMaterial = strtoupper((string) ($sumberMaterial['status_sumber_material'] ?? '')) === 'OUT';
        $nomorSuratJalanInput = trim((string) $this->input->post('nomor_surat_jalan'));
        $modeSuratJalan = strtoupper((string) ($sourceRule['mode_surat_jalan'] ?? ''));
        $referenceMode = strtoupper((string) ($sourceRule['reference_mode'] ?? ''));
        $isMitraReturnMode = (string) $id_sumber_material === '12';

        if ((string) $id_sumber_material === '9' && $nomor_spk === '') {
            $this->session->set_flashdata('error', 'Nomor SPK wajib diisi untuk Out (ke Project).');
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }
        if ((string) $id_sumber_material === '9' && $nama_mitra === '') {
            $this->session->set_flashdata('error', 'PIC Pengambil wajib diisi untuk Out (ke Project).');
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }
        if ((string) $id_sumber_material === '9' && $pic_mitra === '') {
            $this->session->set_flashdata('error', 'Telp Pengambil wajib diisi untuk Out (ke Project).');
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }
        if ((string) $id_sumber_material === '9' && $nomor_polisi === '') {
            $this->session->set_flashdata('error', 'NOPOL Kendaraan wajib diisi untuk Out (ke Project).');
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }

        if (in_array($modeSuratJalan, ['REFERENCE_DROPDOWN', 'REFERENCE_MANUAL', 'AUTO_WITH_REFERENCE'], true) && $nomorSuratJalanInput === '') {
            $this->session->set_flashdata('error', 'Nomor surat jalan / referensi surat jalan wajib diisi untuk sumber material ini.');
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }

        if ((string) $id_sumber_material === '10') {
            if ($tanggal_estimasi_sampai === '' || $nama_ekspedisi === '' || $pic_ekspedisi === '') {
                $this->session->set_flashdata('error', 'Tanggal estimasi sampai, nama ekspedisi, dan PIC ekspedisi wajib diisi untuk pengiriman dari HO.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }
        }

        if (!empty($sourceRule)) {
            if (!empty($sourceRule['require_nomor_spk']) && $nomor_spk === '') {
                $this->session->set_flashdata('error', 'Nomor SPK wajib diisi untuk sumber material ini.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }
            if (!empty($sourceRule['require_tanggal_estimasi']) && $tanggal_estimasi_sampai === '') {
                $this->session->set_flashdata('error', 'Tanggal estimasi sampai wajib diisi untuk sumber material ini.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }
            if (!empty($sourceRule['require_nama_ekspedisi']) && $nama_ekspedisi === '') {
                $this->session->set_flashdata('error', 'Nama ekspedisi wajib diisi untuk sumber material ini.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }
            if (!empty($sourceRule['require_pic_ekspedisi']) && $pic_ekspedisi === '') {
                $this->session->set_flashdata('error', 'PIC ekspedisi wajib diisi untuk sumber material ini.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }
            if (!empty($sourceRule['require_nomor_polisi']) && $nomor_polisi === '') {
                $this->session->set_flashdata('error', 'Nomor polisi wajib diisi untuk sumber material ini.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }
            if (!empty($sourceRule['require_nama_mitra']) && $nama_mitra === '') {
                $this->session->set_flashdata('error', 'Nama mitra wajib diisi untuk sumber material ini.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }
            if (!empty($sourceRule['require_pic_mitra']) && $pic_mitra === '') {
                $this->session->set_flashdata('error', 'PIC mitra wajib diisi untuk sumber material ini.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }
        }

        $suratJalanGenerated = [];
        $nomorSuratJalanFinal = $nomorSuratJalanInput;
        $manualOutProjectCounter = [];
        $isOutProjectAutoMode = (string) $id_sumber_material === '9' && in_array($modeSuratJalan, ['AUTO', 'AUTO_WITH_REFERENCE'], true);

        if ($isOutProjectAutoMode) {
            $preview = $this->MDashboard_Logistik_Stok->previewSuratJalanNumber((int) $id_lokasi_gudang, (string) $tanggal_upload_stok);
            $parsedNomorSurat = $this->parseAutoSuratJalanNumber($nomorSuratJalanInput);
            $confirmGap = (int) $this->input->post('confirm_sj_gap') === 1;

            if ($nomorSuratJalanInput === '' || empty($parsedNomorSurat)) {
                $this->session->set_flashdata('error', 'Nomor surat jalan wajib diisi dengan format auto generator (contoh: TEC.003/TKM-01/SJ/V/2026).');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }

            if ((int) $parsedNomorSurat['id_lokasi_gudang'] !== (int) $id_lokasi_gudang) {
                $this->session->set_flashdata('error', 'Nomor surat jalan tidak sesuai dengan area gudang yang dipilih.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }

            if (!empty($preview['nomor_surat_jalan_year']) && (int) $parsedNomorSurat['nomor_surat_jalan_year'] !== (int) $preview['nomor_surat_jalan_year']) {
                $this->session->set_flashdata('error', 'Tahun pada nomor surat jalan tidak sesuai dengan tanggal surat.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }

            $cekNomorSudahDipakai = $this->db
                ->get_where('tb_logistik_stok', [
                    'no_surat_jalan' => $nomorSuratJalanInput,
                    'id_lokasi_gudang' => (int) $id_lokasi_gudang,
                ])
                ->row_array();

            if (!empty($cekNomorSudahDipakai)) {
                $this->session->set_flashdata('error', 'Nomor surat jalan tersebut sudah dipakai.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }

            $expectedSequence = (int) ($preview['nomor_surat_jalan_seq'] ?? 0);
            $manualSequence = (int) ($parsedNomorSurat['nomor_surat_jalan_seq'] ?? 0);
            if ($manualSequence > $expectedSequence && !$confirmGap) {
                $this->session->set_flashdata('error', 'Nomor ' . str_pad((string) $expectedSequence, 3, '0', STR_PAD_LEFT) . ' belum dipakai. Konfirmasi diperlukan untuk melanjutkan.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }

            $nomorSuratJalanFinal = $nomorSuratJalanInput;
            $suratJalanGenerated = [
                'nomor_surat_jalan' => $nomorSuratJalanFinal,
                'nomor_surat_jalan_year' => (int) $parsedNomorSurat['nomor_surat_jalan_year'],
                'nomor_surat_jalan_seq' => (int) $parsedNomorSurat['nomor_surat_jalan_seq'],
            ];
            $manualOutProjectCounter = [
                'id_lokasi_gudang' => (int) $id_lokasi_gudang,
                'tahun_counter' => (int) $parsedNomorSurat['nomor_surat_jalan_year'],
                'last_sequence' => (int) $parsedNomorSurat['nomor_surat_jalan_seq'],
            ];
        } elseif (in_array($modeSuratJalan, ['AUTO', 'AUTO_WITH_REFERENCE'], true)) {
            $suratJalanGenerated = $this->MDashboard_Logistik_Stok->generateSuratJalanNumber((int) $id_lokasi_gudang, (string) $tanggal_upload_stok);
            if (empty($suratJalanGenerated['nomor_surat_jalan'])) {
                $this->session->set_flashdata('error', 'Generator nomor surat jalan belum siap. Pastikan tabel counter surat jalan sudah dibuat.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }
            $nomorSuratJalanFinal = (string) $suratJalanGenerated['nomor_surat_jalan'];
        }

        if ($isOutMaterial) {
            $qtyPerItem = [];
            foreach ($jumlah_stok as $key => $value) {
                $qtyRequest = (float) ($jumlah_stok[$key] ?? 0);
                $idKodeItem = (int) ($idKodeItems[$key] ?? 0);
                if ($idKodeItem <= 0 || $qtyRequest <= 0) {
                    continue;
                }

                if (!isset($qtyPerItem[$idKodeItem])) {
                    $qtyPerItem[$idKodeItem] = 0;
                }
                $qtyPerItem[$idKodeItem] += $qtyRequest;
            }

            foreach ($qtyPerItem as $idKodeItem => $totalQtyRequest) {
                $availableStock = $this->MDashboard_Logistik_Stok->getCurrentStockByGudangItem($id_lokasi_gudang, (int) $idKodeItem);
                if ($totalQtyRequest > $availableStock) {
                    $this->session->set_flashdata('error', 'Qty out material tidak boleh melebihi stok tersedia di gudang karena hasil perhitungan akan membuat stok minus.');
                    redirect('Dashboard_Logistik_Stok/revamp');
                    return;
                }
            }
        }

        $peminjamanMap = [];
        if ($isMitraReturnMode) {
            $idStockPeminjamanItems = (array) $this->input->post('id_stock_peminjaman');
            $peminjamanSjRows = $this->MDashboard_Logistik_Stok->getOpenPeminjamanMitraBySjGudang($nomorSuratJalanInput, (int) $id_lokasi_gudang);
            foreach ($peminjamanSjRows as $row) {
                $peminjamanMap[(int) ($row['id_stock_peminjaman'] ?? 0)] = $row;
            }

            if (empty($peminjamanMap)) {
                $this->session->set_flashdata('error', 'SJ peminjaman mitra tidak ditemukan atau seluruh qty sudah dikembalikan.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }

            foreach ($jumlah_stok as $key => $value) {
                $qtyRequest = (float) ($jumlah_stok[$key] ?? 0);
                $idStockPeminjaman = (int) ($idStockPeminjamanItems[$key] ?? 0);
                if ($idStockPeminjaman <= 0 || !isset($peminjamanMap[$idStockPeminjaman])) {
                    $this->session->set_flashdata('error', 'Referensi item SJ peminjaman belum valid. Pilih ulang SJ peminjaman mitra.');
                    redirect('Dashboard_Logistik_Stok/revamp');
                    return;
                }

                $qtyOutstanding = (float) ($peminjamanMap[$idStockPeminjaman]['qty_outstanding_pengembalian'] ?? 0);
                if ($qtyRequest <= 0 || $qtyRequest > $qtyOutstanding) {
                    $this->session->set_flashdata('error', 'Qty pengembalian ke mitra tidak boleh melebihi outstanding qty peminjaman.');
                    redirect('Dashboard_Logistik_Stok/revamp');
                    return;
                }
            }
        }

        $tanggalFormatted = "{$tanggal_upload_stok} " . date('H:i:s');
        $tanggaCreated = "{$tanggal_pembuatan_stok} " . date('H:i:s');
        $hasRincianTable = $this->MDashboard_Logistik_Stok->fieldExists('tb_logistik_stok_rincian', 'id_logistik_stok_rincian');

        $this->db->trans_start();

        foreach ($jumlah_stok as $key => $value) {
            $keteranganStok = trim((string) $this->input->post('keterangan_stok'));
            if ((string) $id_sumber_material === '9' && $nomor_spk !== '') {
                $keteranganOutProject = trim(
                    'Nomor SPK: ' . $nomor_spk .
                    ' | PIC Pengambil: ' . $nama_mitra .
                    ' | Telp Pengambil: ' . $pic_mitra .
                    ' | NOPOL Kendaraan: ' . $nomor_polisi
                );
                $keteranganStok = trim($keteranganOutProject . ($keteranganStok !== '' ? ' | ' . $keteranganStok : ''));
            }
            if ((string) $id_sumber_material === '10') {
                $keteranganTambahanHo = trim(
                    'Estimasi sampai: ' . $tanggal_estimasi_sampai .
                    ' | Ekspedisi: ' . $nama_ekspedisi .
                    ' | PIC Ekspedisi: ' . $pic_ekspedisi
                );
                $keteranganStok = trim($keteranganTambahanHo . ($keteranganStok !== '' ? ' | ' . $keteranganStok : ''));
            }

            $rowInsert = [
                'no_surat_jalan' => $nomorSuratJalanFinal,
                'id_lokasi_gudang' => $id_lokasi_gudang,
                'id_bowheer' => $id_bowheer,
                'id_sumber_material' => $id_sumber_material,
                'id_kode_item' => $this->input->post('id_kode_item')[$key],
                'jumlah_stok' => $jumlah_stok[$key],
                'satuan_stok' => $this->input->post('satuan_stok')[$key],
                'merk_stok' => $this->input->post('merk_item')[$key],
                'no_haspel_stok' => $this->input->post('no_haspel_item')[$key],
                'no_ref_stok' => $this->input->post('no_ref_item')[$key],
                'keterangan_stok' => $keteranganStok,
                'tanggal_upload_stok' => $tanggalFormatted,
                'surat_jalan' => $upload_path . $uploaded_files['file-sj'],
                'evidence' => $upload_path . $uploaded_files['file-evidence'],
                'no_po_logistik' => $this->input->post('no_po_logistik'),
                'no_pr_logistik' => $this->input->post('no_pr_logistik'),
                'id_lokasi_gudang_pengiriman' => $this->input->post('id_lokasi_gudang_pengiriman'),
                'id_user' => $this->session->userdata('id_user'),
                'CREATED_AT' => $tanggaCreated
            ];

            if (!empty($suratJalanGenerated['nomor_surat_jalan_year']) && $this->MDashboard_Logistik_Stok->fieldExists('tb_logistik_stok', 'nomor_surat_jalan_year')) {
                $rowInsert['nomor_surat_jalan_year'] = $suratJalanGenerated['nomor_surat_jalan_year'];
            }
            if (!empty($suratJalanGenerated['nomor_surat_jalan_seq']) && $this->MDashboard_Logistik_Stok->fieldExists('tb_logistik_stok', 'nomor_surat_jalan_seq')) {
                $rowInsert['nomor_surat_jalan_seq'] = $suratJalanGenerated['nomor_surat_jalan_seq'];
            }
            if ($nomor_spk !== '' && $this->MDashboard_Logistik_Stok->fieldExists('tb_logistik_stok', 'nomor_spk')) {
                $rowInsert['nomor_spk'] = $nomor_spk;
            }

            if ($this->db->field_exists('tanggal_estimasi_sampai', 'tb_logistik_stok')) {
                $rowInsert['tanggal_estimasi_sampai'] = $tanggal_estimasi_sampai !== '' ? $tanggal_estimasi_sampai : null;
            }
            if ($this->db->field_exists('nama_ekspedisi', 'tb_logistik_stok')) {
                $rowInsert['nama_ekspedisi'] = $nama_ekspedisi !== '' ? $nama_ekspedisi : null;
            }
            if ($this->db->field_exists('pic_ekspedisi', 'tb_logistik_stok')) {
                $rowInsert['pic_ekspedisi'] = $pic_ekspedisi !== '' ? $pic_ekspedisi : null;
            }

            $this->db->insert('tb_logistik_stok', $rowInsert);
            $insertId = (int) $this->db->insert_id();

            if ($hasRincianTable && $insertId > 0) {
                $rincianInsert = [
                    'id_logistik_stok' => $insertId,
                    'id_sumber_material' => (int) $id_sumber_material,
                    'nomor_surat_jalan_asal' => in_array($referenceMode, ['MANUAL', 'DROPDOWN'], true) ? $nomorSuratJalanInput : null,
                    'nomor_spk' => $nomor_spk !== '' ? $nomor_spk : null,
                    'tanggal_estimasi_sampai' => $tanggal_estimasi_sampai !== '' ? $tanggal_estimasi_sampai : null,
                    'nama_ekspedisi' => $nama_ekspedisi !== '' ? $nama_ekspedisi : null,
                    'pic_ekspedisi' => $pic_ekspedisi !== '' ? $pic_ekspedisi : null,
                    'created_at' => $tanggaCreated,
                    'updated_at' => $tanggaCreated,
                ];

                if (((string) $id_sumber_material === '9' || !empty($sourceRule['require_nama_mitra'])) && $this->input->post('nama_mitra')) {
                    $rincianInsert['nama_mitra'] = $nama_mitra;
                }
                if (((string) $id_sumber_material === '9' || !empty($sourceRule['require_pic_mitra'])) && $this->input->post('pic_mitra')) {
                    $rincianInsert['pic_mitra'] = $pic_mitra;
                }
                if (((string) $id_sumber_material === '9' || !empty($sourceRule['require_nomor_polisi'])) && $this->input->post('nomor_polisi')) {
                    $rincianInsert['nomor_polisi'] = $nomor_polisi;
                }
                if ($isMitraReturnMode) {
                    $idStockPeminjamanItems = (array) $this->input->post('id_stock_peminjaman');
                    $idStockPeminjaman = (int) ($idStockPeminjamanItems[$key] ?? 0);
                    if ($idStockPeminjaman > 0) {
                        $rincianInsert['id_logistik_stok_asal'] = $idStockPeminjaman;
                        $rincianInsert['id_sumber_material_asal'] = 4;
                        $rincianInsert['nomor_surat_jalan_asal'] = $nomorSuratJalanInput;
                    }
                }

                $this->db->insert('tb_logistik_stok_rincian', $rincianInsert);
            }
        }

        if (!empty($manualOutProjectCounter)) {
            $this->syncSuratJalanCounterSequence(
                (int) $manualOutProjectCounter['id_lokasi_gudang'],
                (int) $manualOutProjectCounter['tahun_counter'],
                (int) $manualOutProjectCounter['last_sequence']
            );
        }

        $this->db->trans_complete();
        $is_success = $this->db->trans_status();

        if ($is_success) {
            $this->session->set_flashdata('success', 'Data stok berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data stok. Silakan coba lagi.');
        }

        redirect('Dashboard_Logistik_Stok/revamp');
    }

    public function getPengirimanPabrikBySuratJalan()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $noSuratJalan = trim((string) $this->input->get('nomor_surat_jalan'));
        $idLokasiGudang = (int) $this->input->get('id_lokasi_gudang');
        if ($noSuratJalan === '' || $idLokasiGudang <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'items' => [],
                    'po_refs' => '',
                    'message' => 'Lengkapi nomor surat jalan pabrik dan area gudang terlebih dahulu.',
                ]));
            return;
        }

        $rows = $this->MDashboard_Logistik_Stok->getOpenPengirimanPabrikBySjGudang($noSuratJalan, $idLokasiGudang);
        $items = [];
        $poRefs = [];
        foreach ($rows as $row) {
            $outstanding = (float) ($row['qty_outstanding_terima'] ?? 0);
            if ($outstanding <= 0) {
                continue;
            }

            $items[] = $row;
            if (!empty($row['nomor_po_pabrik'])) {
                $poRefs[] = $row['nomor_po_pabrik'];
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'items' => array_values($items),
                'po_refs' => implode(', ', array_values(array_unique($poRefs))),
                'message' => empty($items) ? 'Tidak ada surat jalan yang cocok.' : '',
            ]));
    }

    public function getPengirimanHoBySuratJalan()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $noSuratJalan = trim((string) $this->input->get('nomor_surat_jalan'));
        $idLokasiGudang = (int) $this->input->get('id_lokasi_gudang');
        if ($noSuratJalan === '' || $idLokasiGudang <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'items' => [],
                    'po_refs' => '',
                    'message' => 'Lengkapi surat jalan internal HO dan area gudang terlebih dahulu.',
                ]));
            return;
        }

        $rows = $this->MDashboard_Logistik_Stok->getOpenPengirimanHoBySjGudang($noSuratJalan, $idLokasiGudang);
        $items = [];
        $poRefs = [];
        foreach ($rows as $row) {
            $outstanding = (float) ($row['qty_outstanding_terima'] ?? 0);
            if ($outstanding <= 0) {
                continue;
            }

            $items[] = $row;
            if (!empty($row['no_po_logistik'])) {
                $poRefs[] = $row['no_po_logistik'];
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'items' => array_values($items),
                'po_refs' => implode(', ', array_values(array_unique($poRefs))),
                'message' => empty($items) ? 'Tidak ada surat jalan yang cocok.' : '',
            ]));
    }

    public function getPengirimanHoOptions()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idLokasiGudang = (int) $this->input->get('id_lokasi_gudang');
        if ($idLokasiGudang <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'items' => [],
                    'message' => 'Pilih area gudang terlebih dahulu.',
                ]));
            return;
        }

        $rows = $this->MDashboard_Logistik_Stok->getOpenPengirimanHoOptionsByGudang($idLokasiGudang);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'items' => array_values($rows),
                'message' => empty($rows) ? 'Tidak ada surat jalan internal HO yang masih proses pengiriman ke area ini.' : '',
            ]));
    }

    public function getPengirimanPabrikOptions()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idLokasiGudang = (int) $this->input->get('id_lokasi_gudang');
        if ($idLokasiGudang <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'items' => [],
                    'message' => 'Pilih area gudang terlebih dahulu.',
                ]));
            return;
        }

        $rows = $this->MDashboard_Logistik_Stok->getOpenPengirimanPabrikOptionsByGudang($idLokasiGudang);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'items' => array_values($rows),
                'message' => empty($rows) ? 'Tidak ada surat jalan pabrik yang masih outstanding ke gudang ini.' : '',
            ]));
    }

    public function getPeminjamanMitraOptions()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idLokasiGudang = (int) $this->input->get('id_lokasi_gudang');
        if ($idLokasiGudang <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'items' => [],
                    'message' => 'Pilih area gudang terlebih dahulu.',
                ]));
            return;
        }

        $rows = $this->MDashboard_Logistik_Stok->getOpenPeminjamanMitraOptionsByGudang($idLokasiGudang);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'items' => array_values($rows),
                'message' => empty($rows) ? 'Tidak ada SJ peminjaman mitra yang masih outstanding di gudang ini.' : '',
            ]));
    }

    public function getPeminjamanMitraBySuratJalan()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $noSuratJalan = trim((string) $this->input->get('nomor_surat_jalan'));
        $idLokasiGudang = (int) $this->input->get('id_lokasi_gudang');
        if ($noSuratJalan === '' || $idLokasiGudang <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'items' => [],
                    'po_refs' => '',
                    'message' => 'Lengkapi SJ peminjaman mitra dan area gudang terlebih dahulu.',
                ]));
            return;
        }

        $rows = $this->MDashboard_Logistik_Stok->getOpenPeminjamanMitraBySjGudang($noSuratJalan, $idLokasiGudang);
        $items = [];
        foreach ($rows as $row) {
            $outstanding = (float) ($row['qty_outstanding_pengembalian'] ?? 0);
            if ($outstanding <= 0) {
                continue;
            }
            $items[] = $row;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'items' => array_values($items),
                'po_refs' => '',
                'message' => empty($items) ? 'Tidak ada SJ peminjaman mitra yang cocok.' : '',
            ]));
    }

    public function cekNomorSuratJalan()
    {
        header('Content-Type: application/json'); // Tambahkan ini

        $nomor_surat_jalan = $this->input->post('nomor_surat_jalan');
        $id_lokasi_gudang = $this->input->post('id_lokasi_gudang');

        // Debugging: Log input yang masuk
        log_message('error', 'Cek Nomor Surat Jalan - Input: ' . json_encode($_POST));

        if (!$nomor_surat_jalan || !$id_lokasi_gudang) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap!']);
            return;
        }

        $cek = $this->db->get_where('tb_logistik_stok', [
            'no_surat_jalan' => $nomor_surat_jalan,
            'id_lokasi_gudang' => $id_lokasi_gudang
        ])->row_array();

        // Debugging: Log hasil query
        log_message('error', 'Query Cek: ' . $this->db->last_query());

        if ($cek) {
            die(json_encode(['status' => 'exists']));
        } else {
            die(json_encode(['status' => 'available']));
        }
    }

    public function validateManualSuratJalanOutProject()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idLokasiGudang = (int) $this->input->post('id_lokasi_gudang');
        $idSumberMaterial = (int) $this->input->post('id_sumber_material');
        $tanggalDokumen = trim((string) $this->input->post('tanggal_upload_stok'));
        $nomorSuratJalan = trim((string) $this->input->post('nomor_surat_jalan'));

        $response = [
            'status' => 'error',
            'message' => 'Validasi nomor surat jalan gagal diproses.',
            'expected_sequence' => 0,
            'entered_sequence' => 0,
            'next_sequence_after_submit' => 0,
        ];

        if ($idLokasiGudang <= 0 || $idSumberMaterial <= 0 || $nomorSuratJalan === '') {
            $response['message'] = 'Data validasi belum lengkap.';
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        $sourceRule = $this->MDashboard_Logistik_Stok->getSumberMaterialRuleById($idSumberMaterial);
        $modeSuratJalan = strtoupper((string) ($sourceRule['mode_surat_jalan'] ?? ''));
        $isOutProjectAutoMode = $idSumberMaterial === 9 && in_array($modeSuratJalan, ['AUTO', 'AUTO_WITH_REFERENCE'], true);
        if (!$isOutProjectAutoMode) {
            $response['status'] = 'ok';
            $response['message'] = 'Sumber material ini tidak memakai validasi khusus Out Project.';
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        $preview = $this->MDashboard_Logistik_Stok->previewSuratJalanNumber($idLokasiGudang, $tanggalDokumen);
        $parsed = $this->parseAutoSuratJalanNumber($nomorSuratJalan);
        if (empty($parsed)) {
            $response['status'] = 'invalid';
            $response['message'] = 'Format nomor surat jalan tidak valid. Gunakan format TEC.xxx/TKM-xx/SJ/BULAN/TAHUN.';
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        if ((int) $parsed['id_lokasi_gudang'] !== $idLokasiGudang) {
            $response['status'] = 'invalid';
            $response['message'] = 'Nomor surat jalan tidak sesuai dengan area gudang.';
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        $expectedSequence = (int) ($preview['nomor_surat_jalan_seq'] ?? 0);
        $enteredSequence = (int) ($parsed['nomor_surat_jalan_seq'] ?? 0);
        $response['expected_sequence'] = $expectedSequence;
        $response['entered_sequence'] = $enteredSequence;
        $response['next_sequence_after_submit'] = $enteredSequence > 0 ? ($enteredSequence + 1) : 0;

        if (!empty($preview['nomor_surat_jalan_year']) && (int) $parsed['nomor_surat_jalan_year'] !== (int) $preview['nomor_surat_jalan_year']) {
            $response['status'] = 'invalid';
            $response['message'] = 'Tahun pada nomor surat jalan tidak sesuai dengan tanggal surat.';
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        $existing = $this->db->get_where('tb_logistik_stok', [
            'no_surat_jalan' => $nomorSuratJalan,
            'id_lokasi_gudang' => $idLokasiGudang,
        ])->row_array();

        if (!empty($existing)) {
            $response['status'] = 'exists';
            $response['message'] = 'Nomor tersebut sudah dipakai.';
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        if ($expectedSequence > 0 && $enteredSequence > $expectedSequence) {
            $response['status'] = 'gap';
            $response['message'] = 'Nomor ' . str_pad((string) $expectedSequence, 3, '0', STR_PAD_LEFT) . ' belum dipakai, apakah anda yakin?';
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        $response['status'] = 'ok';
        $response['message'] = 'Nomor surat jalan valid.';
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function hapusReportStokLogistik($no_surat_jalan)
    {
        $no_surat_jalan = urldecode($no_surat_jalan); // Decode dari URL
        $id_lokasi_gudang = $this->input->get('id_lokasi_gudang'); // Ambil ID dari URL (GET)
        $lokasi = !empty($this->input->get('lokasi')) ? $this->input->get('lokasi') : null;

        // Pastikan parameter tidak kosong
        if (!empty($no_surat_jalan) && !empty($id_lokasi_gudang)) {
            $this->db->where([
                'no_surat_jalan' => $no_surat_jalan,
                'id_lokasi_gudang' => $id_lokasi_gudang
            ]);

            $res = $this->db->delete("tb_logistik_stok");

            if ($res) {
                $this->session->set_flashdata('success', 'Data berhasil dihapus.');
            } else {
                $this->session->set_flashdata('error', 'Gagal menghapus data.');
            }
        } else {
            $this->session->set_flashdata('error', 'Parameter tidak lengkap.');
        }

        redirect($lokasi == null ? 'Dashboard_Logistik_Stok' : 'Logistik_Stok_Detail/detail/' . $lokasi);
    }

    public function filterDetailSuratJalan()
    {
        $noSuratJalan = trim((string) $this->input->post('no_surat_jalan'));
        $suratJalanPath = trim((string) $this->input->post('surat_jalan'));
        $idLokasiGudang = (int) $this->input->post('id_lokasi_gudang');
        $idSumberMaterial = (int) $this->input->post('id_sumber_material');

        $data['getDetailAreaBySJ'] = $this->MDashboard_Logistik_Stok->getDetailAreaBySJ(
            $noSuratJalan,
            $idLokasiGudang,
            $idSumberMaterial,
            $suratJalanPath
        );

        echo json_encode($data);
        die();
    }

    public function filterDashboardLogistik()
    {
        header('Content-Type: application/json'); // Pastikan respons dalam JSON

        $lokasi = json_decode($this->input->post('lokasi'), true);
        $bowheer = json_decode($this->input->post('bowheer'), true);
        $item = json_decode($this->input->post('item'), true);
        $status = json_decode($this->input->post('status'), true);
        $tanggal = $this->input->post('tanggal');

        $data['getDashboardFiltered'] = $this->MDashboard_Logistik_Stok->getDashboardFiltered($lokasi, $bowheer, $item, $tanggal, $status);
        $data['getRincianDashboardFiltered'] = $this->MDashboard_Logistik_Stok->getRincianDashboardFiltered($lokasi, $bowheer, $item, $tanggal, $status);
        $data['getRincianDashboardFilteredBowheer'] = $this->MDashboard_Logistik_Stok->getRincianDashboardFilteredBowheer($lokasi, $bowheer, $item, $tanggal, $status);
        $data['getInOutHistoryFiltered'] = $this->MDashboard_Logistik_Stok->getInOutHistoryFiltered($lokasi, $bowheer, $item, $tanggal, $status);
        $data['getAllStokByKategoryFilterCityFiltered'] = $this->MDashboard_Logistik_Stok->getAllStokByKategoryFilterCityFiltered($tanggal, $status, $lokasi, $bowheer, $item);

        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    private function buildDashboardData()
    {
        $dateStart = $this->input->get('dateStart');

        $data['title'] = 'DASHBOARD LOGISTIK';
        $data['judul'] = 'DASHBOARD LOGISTIK';
        $data['getAllStokLogistik'] = $this->MDashboard_Logistik_Stok->getAllStokLogistik();
        $data['getAllStokByKategory'] = $this->MDashboard_Logistik_Stok->getAllStokByKategory();
        $data['getAllStokByKategoryFilterCity'] = $this->MDashboard_Logistik_Stok->getAllStokByKategoryFilterCity();
        $data['getAllStokByKategoryFilterRegional'] = $this->MDashboard_Logistik_Stok->getAllStokByKategoryFilterRegional();
        if ($this->session->userdata('lokasi_user') == "HO") {
            $data['getListGudangLokasiUser'] = $this->MDashboard_Logistik_Stok->getListGudangLokasiUserAll();
        } else {
            $data['getListGudangLokasiUser'] = $this->MDashboard_Logistik_Stok->getListGudangLokasiUser();
        }
        $data['getMasterProject'] = $this->MDashboard_Logistik_Stok->getMasterProject();
        $data['getMasterSumberMaterial'] = $this->MDashboard_Logistik_Stok->getMasterSumberMaterial();
        $data['sourceMaterialRuleMap'] = $this->MDashboard_Logistik_Stok->getSumberMaterialRuleMap();
        $data['getMasterKodeItem'] = $this->MDashboard_Logistik_Stok->getMasterKodeItem();
        $data['getUniqueKotaGudang'] = $this->MDashboard_Logistik_Stok->getUniqueKotaGudang();
        $data['getUniqueProjectLogistik'] = $this->MDashboard_Logistik_Stok->getUniqueProjectLogistik();
        $data['getUniqueItemLogistik'] = $this->MDashboard_Logistik_Stok->getUniqueItemLogistik();
        $data['getUniqueSumberMaterial'] = $this->MDashboard_Logistik_Stok->getUniqueSumberMaterial();
        $data['getReportInOutMaterial'] = $this->MDashboard_Logistik_Stok->getReportInOutMaterial();
        $data['getReportStokMaterial'] = $this->MDashboard_Logistik_Stok->getReportStokMaterial($dateStart);

        return $data;
    }

    private function handlePabrikReceipt()
    {
        $this->load->helper('date');
        $this->load->library('upload');

        $upload_path = "./uploads/";
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $idLokasiGudang = (int) $this->input->post('id_lokasi_gudang');
        $nomorSuratJalan = trim((string) $this->input->post('nomor_surat_jalan'));
        $tanggalUploadStok = trim((string) $this->input->post('tanggal_upload_stok'));
        $tanggalPembuatanStok = trim((string) $this->input->post('tanggal_pembuatan_stok'));
        $nomorBaSelisih = trim((string) $this->input->post('nomor_ba_selisih'));
        $keteranganStok = trim((string) $this->input->post('keterangan_stok'));
        $qtyTerimaItems = preg_replace('/\D/', '', (array) $this->input->post('jumlah_stok'));
        $qtySelisihItems = preg_replace('/\D/', '', (array) $this->input->post('qty_selisih'));
        $idPengirimanDetailItems = (array) $this->input->post('id_pengiriman_pabrik_detail');
        $idKodeItemItems = (array) $this->input->post('id_kode_item');
        $idBowheerItems = (array) $this->input->post('id_bowheer_item');
        $satuanItems = (array) $this->input->post('satuan_stok');
        $merkItems = (array) $this->input->post('merk_item');
        $noHaspelItems = (array) $this->input->post('no_haspel_item');
        $noRefItems = (array) $this->input->post('no_ref_item');
        $noPoLogistik = trim((string) $this->input->post('no_po_logistik'));

        if ($idLokasiGudang <= 0 || $nomorSuratJalan === '' || $tanggalUploadStok === '' || empty($idPengirimanDetailItems)) {
            $this->session->set_flashdata('error', 'Data penerimaan dari pabrik belum lengkap.');
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }

        $shipmentRows = $this->MDashboard_Logistik_Stok->getOpenPengirimanPabrikBySjGudang($nomorSuratJalan, $idLokasiGudang);
        $shipmentMap = [];
        foreach ($shipmentRows as $row) {
            $shipmentMap[(int) $row['id_pengiriman_pabrik_detail']] = $row;
        }

        if (empty($shipmentMap)) {
            $this->session->set_flashdata('error', 'Pengiriman pabrik untuk surat jalan dan gudang tersebut tidak ditemukan atau sudah selesai diterima.');
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }

        $uploadedFiles = $this->uploadStockDocuments($upload_path, $idLokasiGudang, 0, 7, $tanggalUploadStok);
        if ($uploadedFiles === false) {
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }

        $dataInsert = [];
        $qtySelisihTotal = 0;
        $touchedPengirimanHeaders = [];
        $tanggaCreated = "{$tanggalPembuatanStok} " . date('H:i:s');

        $this->db->trans_start();

        foreach ($idPengirimanDetailItems as $key => $idPengirimanDetail) {
            $shipmentDetailId = (int) $idPengirimanDetail;
            if ($shipmentDetailId <= 0 || !isset($shipmentMap[$shipmentDetailId])) {
                continue;
            }

            $shipment = $shipmentMap[$shipmentDetailId];
            $qtyTerima = (int) ($qtyTerimaItems[$key] ?? 0);
            $qtySelisih = (int) ($qtySelisihItems[$key] ?? 0);
            $outstanding = (int) round((float) ($shipment['qty_outstanding_terima'] ?? 0));

            if ($qtyTerima < 0 || $qtySelisih < 0 || ($qtyTerima + $qtySelisih) <= 0 || ($qtyTerima + $qtySelisih) > $outstanding) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', 'Qty terima dan qty selisih harus valid serta tidak boleh melebihi outstanding pengiriman pabrik.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }

            if ($qtySelisih > 0 && $nomorBaSelisih === '') {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', 'Nomor BA selisih wajib diisi jika ada material rusak / selisih penerimaan.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }

            if ($qtyTerima > 0) {
                $tanggalFormatted = "{$tanggalUploadStok} " . date('H:i:s');
                $dataInsert[] = [
                    'no_surat_jalan' => $nomorSuratJalan,
                    'id_lokasi_gudang' => $idLokasiGudang,
                    'id_bowheer' => (int) ($idBowheerItems[$key] ?? $shipment['id_bowheer_pemilik_item'] ?? 0),
                    'id_sumber_material' => 7,
                    'id_kode_item' => (int) ($idKodeItemItems[$key] ?? $shipment['id_kode_item']),
                    'jumlah_stok' => $qtyTerima,
                    'satuan_stok' => (string) ($satuanItems[$key] ?? $shipment['satuan_item'] ?? ''),
                    'merk_stok' => (string) ($merkItems[$key] ?? ''),
                    'no_haspel_stok' => (string) ($noHaspelItems[$key] ?? ''),
                    'no_ref_stok' => (string) ($noRefItems[$key] ?? ''),
                    'keterangan_stok' => trim('Penerimaan dari pabrik. PO: ' . ($shipment['nomor_po_pabrik'] ?? $noPoLogistik) . '. ' . $keteranganStok . ($qtySelisih > 0 ? ' | Selisih: ' . $qtySelisih . ' | BA: ' . $nomorBaSelisih : '')),
                    'tanggal_upload_stok' => $tanggalFormatted,
                    'surat_jalan' => $upload_path . $uploadedFiles['file-sj'],
                    'evidence' => $upload_path . $uploadedFiles['file-evidence'],
                    'no_po_logistik' => $shipment['nomor_po_pabrik'] ?? $noPoLogistik,
                    'no_pr_logistik' => $shipment['nomor_purchase_request'] ?? null,
                    'id_lokasi_gudang_pengiriman' => null,
                    'id_user' => $this->session->userdata('id_user'),
                    'CREATED_AT' => $tanggaCreated
                ];
            }

            $this->db->set('qty_diterima', 'COALESCE(qty_diterima, 0) + ' . $qtyTerima, false);
            $this->db->where('id_pengiriman_pabrik_detail', $shipmentDetailId);
            $this->db->update('tb_logistik_pengiriman_pabrik_detail');

            if ($qtySelisih > 0 && $this->db->field_exists('qty_closed_manual', 'tb_logistik_pesanan_pabrik_detail')) {
                $this->db->set('qty_closed_manual', 'COALESCE(qty_closed_manual, 0) + ' . $qtySelisih, false);
                if ($this->db->field_exists('alasan_close_detail', 'tb_logistik_pesanan_pabrik_detail')) {
                    $closeReason = 'Selisih / rusak saat penerimaan gudang. BA: ' . $nomorBaSelisih;
                    $this->db->set('alasan_close_detail', $closeReason);
                }
                $this->db->where('id_pesanan_pabrik_detail', $shipment['id_pesanan_pabrik_detail']);
                $this->db->update('tb_logistik_pesanan_pabrik_detail');
            }

            if ($qtySelisih > 0 && $this->db->field_exists('catatan_pengiriman', 'tb_logistik_pengiriman_pabrik')) {
                $this->db->set('catatan_pengiriman', 'CONCAT(COALESCE(catatan_pengiriman, \'\'), ' . $this->db->escape(' | Selisih penerimaan ' . $qtySelisih . ' dengan BA ' . $nomorBaSelisih) . ')', false);
                $this->db->where('id_pengiriman_pabrik', $shipment['id_pengiriman_pabrik']);
                $this->db->update('tb_logistik_pengiriman_pabrik');
            }

            if ($this->db->field_exists('status_penerimaan', 'tb_logistik_pengiriman_pabrik')) {
                $this->db->where('id_pengiriman_pabrik', $shipment['id_pengiriman_pabrik']);
                $this->db->update('tb_logistik_pengiriman_pabrik', ['status_penerimaan' => 'RECEIVED']);
            }

            $qtySelisihTotal += $qtySelisih;
            $touchedPengirimanHeaders[(int) $shipment['id_pengiriman_pabrik']] = true;
        }

        if (!empty($dataInsert)) {
            $this->db->insert_batch('tb_logistik_stok', $dataInsert);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('error', 'Gagal menyimpan penerimaan stok dari pabrik.');
        } else {
            $message = 'Penerimaan stok dari pabrik berhasil disimpan.';
            if ($qtySelisihTotal > 0) {
                $message .= ' Selisih ' . number_format($qtySelisihTotal, 0, ',', '.') . ' diproses sebagai BA / material rusak.';
            }
            $this->session->set_flashdata('success', $message);
        }

        redirect('Dashboard_Logistik_Stok/revamp');
    }

    private function handleHoReceipt()
    {
        $this->load->helper('date');
        $this->load->library('upload');

        $upload_path = "./uploads/";
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $idLokasiGudang = (int) $this->input->post('id_lokasi_gudang');
        $nomorSuratJalan = trim((string) $this->input->post('nomor_surat_jalan'));
        $tanggalUploadStok = trim((string) $this->input->post('tanggal_upload_stok'));
        $tanggalPembuatanStok = trim((string) $this->input->post('tanggal_pembuatan_stok'));
        $keteranganStok = trim((string) $this->input->post('keterangan_stok'));
        $qtyTerimaItems = preg_replace('/\D/', '', (array) $this->input->post('jumlah_stok'));
        $qtySelisihItems = preg_replace('/\D/', '', (array) $this->input->post('qty_selisih'));
        $stockPengirimanIds = (array) $this->input->post('id_stock_pengiriman');
        $idKodeItemItems = (array) $this->input->post('id_kode_item');
        $idBowheerItems = (array) $this->input->post('id_bowheer_item');
        $satuanItems = (array) $this->input->post('satuan_stok');
        $merkItems = (array) $this->input->post('merk_item');
        $noHaspelItems = (array) $this->input->post('no_haspel_item');
        $noRefItems = (array) $this->input->post('no_ref_item');
        $noPoLogistik = trim((string) $this->input->post('no_po_logistik'));

        if ($idLokasiGudang <= 0 || $nomorSuratJalan === '' || $tanggalUploadStok === '' || empty($stockPengirimanIds)) {
            $this->session->set_flashdata('error', 'Data penerimaan dari HO belum lengkap.');
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }

        $shipmentRows = $this->MDashboard_Logistik_Stok->getOpenPengirimanHoBySjGudang($nomorSuratJalan, $idLokasiGudang);
        $shipmentMap = [];
        foreach ($shipmentRows as $row) {
            $shipmentMap[(int) $row['id_stock_pengiriman']] = $row;
        }

        if (empty($shipmentMap)) {
            $this->session->set_flashdata('error', 'Pengiriman HO untuk surat jalan dan gudang tersebut tidak ditemukan atau sudah selesai diterima.');
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }

        $uploadedFiles = $this->uploadStockDocuments($upload_path, $idLokasiGudang, 0, 1, $tanggalUploadStok);
        if ($uploadedFiles === false) {
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }

        $dataInsert = [];
        $tanggaCreated = "{$tanggalPembuatanStok} " . date('H:i:s');

        foreach ($stockPengirimanIds as $key => $stockPengirimanId) {
            $shipmentId = (int) $stockPengirimanId;
            if ($shipmentId <= 0 || !isset($shipmentMap[$shipmentId])) {
                continue;
            }

            $shipment = $shipmentMap[$shipmentId];
            $qtyTerima = (int) ($qtyTerimaItems[$key] ?? 0);
            $qtySelisih = (int) ($qtySelisihItems[$key] ?? 0);
            $outstanding = (int) round((float) ($shipment['qty_outstanding_terima'] ?? 0));

            if ($qtySelisih > 0) {
                $this->session->set_flashdata('error', 'Selisih untuk penerimaan dari HO belum didukung pada flow transit ini. Terima penuh atau sesuaikan pengiriman HO lebih dulu.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }

            if ($qtyTerima <= 0 || $qtyTerima > $outstanding) {
                $this->session->set_flashdata('error', 'Qty diterima dari HO harus lebih dari 0 dan tidak boleh melebihi outstanding pengiriman.');
                redirect('Dashboard_Logistik_Stok/revamp');
                return;
            }

            $tanggalFormatted = "{$tanggalUploadStok} " . date('H:i:s');
            $dataInsert[] = [
                'no_surat_jalan' => $nomorSuratJalan,
                'id_lokasi_gudang' => $idLokasiGudang,
                'id_bowheer' => (int) ($idBowheerItems[$key] ?? $shipment['id_bowheer_pemilik_item'] ?? 0),
                'id_sumber_material' => 1,
                'id_kode_item' => (int) ($idKodeItemItems[$key] ?? $shipment['id_kode_item']),
                'jumlah_stok' => $qtyTerima,
                'satuan_stok' => (string) ($satuanItems[$key] ?? $shipment['satuan_stok'] ?? ''),
                'merk_stok' => (string) ($merkItems[$key] ?? $shipment['merk_stok'] ?? ''),
                'no_haspel_stok' => (string) ($noHaspelItems[$key] ?? $shipment['no_haspel_stok'] ?? ''),
                'no_ref_stok' => (string) ($noRefItems[$key] ?? $shipment['no_ref_stok'] ?? ''),
                'keterangan_stok' => trim('Penerimaan dari HO. Surat jalan internal: ' . $nomorSuratJalan . '. ' . $keteranganStok),
                'tanggal_upload_stok' => $tanggalFormatted,
                'surat_jalan' => $upload_path . $uploadedFiles['file-sj'],
                'evidence' => $upload_path . $uploadedFiles['file-evidence'],
                'no_po_logistik' => $shipment['no_po_logistik'] ?? $noPoLogistik,
                'no_pr_logistik' => $shipment['no_pr_logistik'] ?? null,
                'id_lokasi_gudang_pengiriman' => (int) ($shipment['id_lokasi_gudang_asal'] ?? 0),
                'id_user' => $this->session->userdata('id_user'),
                'CREATED_AT' => $tanggaCreated
            ];

        }

        if (empty($dataInsert)) {
            $this->session->set_flashdata('error', 'Tidak ada item pengiriman HO yang valid untuk diterima.');
            redirect('Dashboard_Logistik_Stok/revamp');
            return;
        }

        $isSuccess = $this->db->insert_batch('tb_logistik_stok', $dataInsert);
        $this->session->set_flashdata($isSuccess ? 'success' : 'error', $isSuccess ? 'Penerimaan dari HO berhasil disimpan.' : 'Gagal menyimpan penerimaan dari HO.');
        redirect('Dashboard_Logistik_Stok/revamp');
    }

    private function uploadStockDocuments($uploadPath, $idLokasiGudang, $idBowheer, $idSumberMaterial, $tanggalUploadStok)
    {
        $timestamp = date('_h_i_s');
        $uploadedFiles = [];
        $files = [
            'file-sj' => "SURAT_JALAN_{$idLokasiGudang}_{$idBowheer}_{$idSumberMaterial}_TIME_{$tanggalUploadStok}{$timestamp}",
            'file-evidence' => "EVIDENCE_{$idLokasiGudang}_{$idBowheer}_{$idSumberMaterial}_TIME_{$tanggalUploadStok}{$timestamp}"
        ];

        foreach ($files as $fieldName => $newFilename) {
            if (empty($_FILES[$fieldName]['name'])) {
                $this->session->set_flashdata('error', 'Dokumen surat jalan dan evidence wajib diunggah.');
                return false;
            }

            $fileExt = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
            $config = [
                'upload_path' => $uploadPath,
                'allowed_types' => 'pdf|jpg|jpeg|png',
                'max_size' => 5120,
                'file_name' => "{$newFilename}.{$fileExt}"
            ];

            $this->upload->initialize($config);

            if (!$this->upload->do_upload($fieldName)) {
                $this->session->set_flashdata('error', 'Format file tidak sesuai atau file terlalu besar!');
                return false;
            }

            $uploadedFiles[$fieldName] = $this->upload->data('file_name');
        }

        return $uploadedFiles;
    }

    private function parseAutoSuratJalanNumber($nomorSuratJalan): array
    {
        $nomorSuratJalan = trim((string) $nomorSuratJalan);
        if ($nomorSuratJalan === '') {
            return [];
        }

        if (!preg_match('/^TEC\.(\d+)\/TKM-(\d{1,2})\/SJ\/([IVXLCDM]+)\/(\d{4})$/i', $nomorSuratJalan, $matches)) {
            return [];
        }

        return [
            'nomor_surat_jalan_seq' => (int) $matches[1],
            'id_lokasi_gudang' => (int) $matches[2],
            'bulan_romawi' => strtoupper((string) $matches[3]),
            'nomor_surat_jalan_year' => (int) $matches[4],
        ];
    }

    private function syncSuratJalanCounterSequence($idLokasiGudang, $tahunCounter, $lastSequence): void
    {
        $idLokasiGudang = (int) $idLokasiGudang;
        $tahunCounter = (int) $tahunCounter;
        $lastSequence = (int) $lastSequence;

        if ($idLokasiGudang <= 0 || $tahunCounter <= 0 || $lastSequence <= 0) {
            return;
        }

        $this->db->query("
            INSERT INTO tb_logistik_surat_jalan_counter (id_lokasi_gudang, tahun_counter, last_sequence)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE last_sequence = VALUES(last_sequence), updated_at = CURRENT_TIMESTAMP
        ", [$idLokasiGudang, $tahunCounter, $lastSequence]);
    }
}
