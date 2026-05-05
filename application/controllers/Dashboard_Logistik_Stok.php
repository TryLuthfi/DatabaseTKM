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
                    'allowed_types' => 'pdf',
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

        foreach ($jumlah_stok as $key => $value) {
            $tanggalFormatted = "{$tanggal_upload_stok} " . date('H:i:s');
            $tanggaCreated = "{$tanggal_pembuatan_stok} " . date('H:i:s');

            $data_insert[] = [
                'no_surat_jalan' => $this->input->post('nomor_surat_jalan'),
                'id_lokasi_gudang' => $id_lokasi_gudang,
                'id_bowheer' => $id_bowheer,
                'id_sumber_material' => $id_sumber_material,
                'id_kode_item' => $this->input->post('id_kode_item')[$key],
                'jumlah_stok' => $jumlah_stok[$key],
                'satuan_stok' => $this->input->post('satuan_stok')[$key],
                'merk_stok' => $this->input->post('merk_item')[$key],
                'no_haspel_stok' => $this->input->post('no_haspel_item')[$key],
                'no_ref_stok' => $this->input->post('no_ref_item')[$key],
                'keterangan_stok' => $this->input->post('keterangan_stok'),
                'tanggal_upload_stok' => $tanggalFormatted,
                'surat_jalan' => $upload_path . $uploaded_files['file-sj'],
                'evidence' => $upload_path . $uploaded_files['file-evidence'],
                'no_po_logistik' => $this->input->post('no_po_logistik'),
                'no_pr_logistik' => $this->input->post('no_pr_logistik'),
                'id_lokasi_gudang_pengiriman' => $this->input->post('id_lokasi_gudang_pengiriman'),
                'id_user' => $this->session->userdata('id_user'),
                'CREATED_AT' => $tanggaCreated
            ];
        }

        $is_success = $this->db->insert_batch('tb_logistik_stok', $data_insert);

        if ($is_success) {
            $this->session->set_flashdata('success', 'Data stok berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data stok. Silakan coba lagi.');
        }

        $this->session->set_flashdata('success', 'Dokumen berhasil diupload!');
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
        $no_surat_jalan = $this->input->post('no_surat_jalan');
        $data['getDetailAreaBySJ'] = $this->MDashboard_Logistik_Stok->getDetailAreaBySJ($no_surat_jalan);

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
}
