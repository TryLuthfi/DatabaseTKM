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

            $data['title'] = 'Dashboard Logistik';
            $data['judul'] = 'Dashboard Logistik';
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

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Dashboard_Logistik_Stok/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function bowheer($kategori_item)
    {
        $this->load->view('Templates/01_Header', $kategori_item);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Dashboard_Logistik_Stok/indexbowheer', $kategori_item);
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
        $config['upload_path'] = "./uploads/";
        $config['allowed_types'] = 'pdf|docx|xlsx|';
        $config['max_size'] = 5120;
        $file_ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $new_filename = "SURAT_JALAN" . "_" . $this->input->post('id_lokasi_gudang') . "_" . $this->input->post('id_bowheer') . "_" . $this->input->post('id_sumber_material') . "_TIME_" . date('d_m_Y_h_i_s') . "." . $file_ext;
        $config['file_name'] = $new_filename;
        $file_path = $config['upload_path'] . $new_filename;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!is_dir('./uploads/')) {
            mkdir('./uploads/', 0777, true);
        }

        if (!$this->upload->do_upload('file')) {
            $error = $this->upload->display_errors(); //TAMPILKAN ERROR
            $this->session->set_flashdata('error', 'Format file tidak sesuai! atau File terlalu besar! ');
            redirect('Dashboard_Logistik_Stok/index/');
        } else {
            $fileData = $this->upload->data();
            $data_insert = [];

            foreach ($this->input->post('jumlah_stok') as $key => $value) {
                $data_insert[] = [
                    'no_surat_jalan' => $this->input->post('nomor_surat_jalan'),
                    'id_lokasi_gudang' => $this->input->post('id_lokasi_gudang'),
                    'id_bowheer' => $this->input->post('id_bowheer'),
                    'id_sumber_material' => $this->input->post('id_sumber_material'),
                    'id_kode_item' => $this->input->post('id_kode_item')[$key],
                    'jumlah_stok' => $this->input->post('jumlah_stok')[$key],
                    'satuan_stok' => $this->input->post('satuan_stok')[$key],
                    'merk_stok' => $this->input->post('merk_item')[$key],
                    'no_haspel_stok' => $this->input->post('no_haspel_item')[$key],
                    'no_ref_stok' => $this->input->post('no_ref_item')[$key],
                    'keterangan_stok' => $this->input->post('keterangan_stok'),
                    'tanggal_upload_stok' => date('Y-m-d'),
                    'evidence_stok' => $file_path,
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
    }

    public function hapusReportStokLogistik($id_logistik_stok)
    {
        $id_logistik_stok = array('id_logistik_stok' => $id_logistik_stok);
        $res = $this->MDashboard_Logistik_Stok->hapusReportStokLogistik($id_logistik_stok);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("Dashboard_Logistik_Stok");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("Dashboard_Logistik_Stok");
        }
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

    $data['getDashboardFiltered'] = $this->MDashboard_Logistik_Stok->getDashboardFiltered($lokasi, $bowheer, $item, $status);

    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;


    }
}
