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

            // Download Report Excel Without Filter
            $data['getReportInOutMaterial'] = $this->MDashboard_Logistik_Stok->getReportInOutMaterial();
            $data['getReportStokMaterial'] = $this->MDashboard_Logistik_Stok->getReportStokMaterial($dateStart);

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Dashboard_Logistik_Stok/index', $data);
            $this->load->view('Templates/03_Footer');
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

            $data = $this->db->query('SELECT * FROM tb_master_logistik_kode_item WHERE project_item = "' . $id_bowheer . '" ')->result_array();

            if ($data === null) {
                exit('Error: Query failed');
            }

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }
    }

    public function tambahReportStokLogistik()
    {

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
                    redirect('Dashboard_Logistik_Stok/index/');
                } else {
                    $uploaded_files[$field_name] = $this->upload->data('file_name');
                }
            }
        }

        if (empty($uploaded_files)) {
            $this->session->set_flashdata('error', 'Gagal mengupload dokumen.');
            redirect('Dashboard_Logistik_Stok/index/');
        }

        $data_insert = [];
        $jumlah_stok = preg_replace('/\D/', '', $this->input->post('jumlah_stok'));

        foreach ($jumlah_stok as $key => $value) {
            $tanggalFormatted = "{$tanggal_upload_stok} " . date('H:i:s');

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
                'id_user' => $this->session->userdata('id_user')
            ];
        }

        $is_success = $this->db->insert_batch('tb_logistik_stok', $data_insert);

        if ($is_success) {
            $this->session->set_flashdata('success', 'Data stok berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data stok. Silakan coba lagi.');
        }

        $this->session->set_flashdata('success', 'Dokumen berhasil diupload!');
        redirect('Dashboard_Logistik_Stok/index/');
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
        $tanggal = $this->input->post('tanggal');

        $data['getDashboardFiltered'] = $this->MDashboard_Logistik_Stok->getDashboardFiltered($lokasi, $bowheer, $item, $tanggal);
        $data['getRincianDashboardFiltered'] = $this->MDashboard_Logistik_Stok->getRincianDashboardFiltered($lokasi, $bowheer, $item, $tanggal);
        $data['getRincianDashboardFilteredBowheer'] = $this->MDashboard_Logistik_Stok->getRincianDashboardFilteredBowheer($lokasi, $bowheer, $item, $tanggal);
        $data['getInOutHistoryFiltered'] = $this->MDashboard_Logistik_Stok->getInOutHistoryFiltered($lokasi, $bowheer, $item, $tanggal);
        $data['getAllStokByKategoryFilterCityFiltered'] = $this->MDashboard_Logistik_Stok->getAllStokByKategoryFilterCityFiltered($tanggal);

        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}
