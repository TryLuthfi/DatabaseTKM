<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fiberstar_Project extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MFiberstar_Project');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $now = date('Y-m-d');

            $data['title'] = 'Monitoring Project PT. Fiberstar';
            $data['judul'] = 'MONITORING PROJECT PT. FIBERSTAR';
            $data['rincian'] = $this->MFiberstar_Project->getData();
            $data['main_data'] = $this->MFiberstar_Project->getMainData();
            $data['data_invoice'] = $this->MFiberstar_Project->getInvoice();
            $data['unique_regional'] = $this->MFiberstar_Project->getUniqueRegional();
            $data['unique_pic'] = $this->MFiberstar_Project->getUniquePic();
            $data['unique_area'] = $this->MFiberstar_Project->getUniqueArea();
            $data['unique_stagging'] = $this->MFiberstar_Project->getUniqueStagging();
            $data['top_area_bak'] = $this->MFiberstar_Project->gettopAreaBAK();
            $data['gettopAreaSPK'] = $this->MFiberstar_Project->gettopAreaSPK();
            $data['top_area_rfs'] = $this->MFiberstar_Project->gettopAreaRFS();
            $data['stagging_regional'] = $this->MFiberstar_Project->getStaggingRegional();
            $data['stagging_area'] = $this->MFiberstar_Project->getStaggingArea();
            $data['grafik_by_kota'] = $this->MFiberstar_Project->getGrafikByKota();
            $data['gettopAreaBAKDetail'] = $this->MFiberstar_Project->gettopAreaBAKDetail();

            if ($this->session->userdata('lokasi_user') == "HO") {
                $data['progress_implementasi'] = $this->MFiberstar_Project->getProgressImplementasiAll();
                $data['total_hp_plan'] = $this->MFiberstar_Project->getTotalHpPlanAll();
            } else {
                $data['progress_implementasi'] = $this->MFiberstar_Project->getProgressImplementasiArea();
                $data['total_hp_plan'] = $this->MFiberstar_Project->getTotalHpPlanArea();
            }

            $data['kode_akun'] = $this->db->get('tb_project_fiberstar')->result_array();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Fiberstar_Project/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function filterTanggalChart()
    {
        $dateRange = $this->input->post('date_range');
        list($startDate, $endDate) = explode(" - ", $dateRange);
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));

        $data1 = $this->MFiberstar_Project->getFilterTanggalTopAreaAchievBAK($startDate, $endDate);
        $data2 = $this->MFiberstar_Project->getFilterTanggalTopAreaAchievSPK($startDate, $endDate);
        $gettopAreaBAK = $this->MFiberstar_Project->gettopAreaBAKFilter($startDate, $endDate);
        $gettopAreaBAKFilterDetail = $this->MFiberstar_Project->gettopAreaBAKFilterDetail($startDate, $endDate);

        // Cegah NULL sebelum di-encode
        array_walk_recursive($data1, function (&$item) {
            $item = $item ?? ""; // Ubah NULL jadi string kosong
        });

        array_walk_recursive($data2, function (&$item) {
            $item = $item ?? ""; // Ubah NULL jadi string kosong
        });

        header('Content-Type: application/json'); // Pastikan response JSON murni
        echo json_encode([
            "status" => "success",
            "bak" => [
                "labels" => array_column($data1, 'area_project'),
                "data" => array_column($data1, 'achiev_bak'),
                "total_cluster_bak" => array_column($data1, 'total_cluster_bak')
            ],
            "spk" => [
                "labels" => array_column($data2, 'area_project'),
                "data" => array_column($data2, 'achiev_spk'),
                "total_cluster_spk" => array_column($data2, 'total_cluster_spk')
            ],
            "gettopAreaBAK" => $gettopAreaBAK,
            "gettopAreaBAKFilterDetail" => $gettopAreaBAKFilterDetail
        ]);
        exit();
    }

    public function edit()
    {

        $data_array = array(
            'kode_provider' => 1,
            'nomor_po' => $_POST['nomor_po'],
            'nilai_po' => $_POST['nilai_po'],
            'tanggal_po' => $_POST['tanggal_po'],
            'versi_po' => $_POST['versi_po'],
            'kode_po' => $_POST['kode_po'],
            'status_po' => $_POST['status_po']
        );

        $where = array('kode_akun' => $_POST['kode_akun']);

        $res = $this->MFiberstar_Project->updateData($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("Fiberstar_Project");
            $status = $this->session->flashdata('destroy');
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("Fiberstar_Project");
            $status = $this->session->flashdata('destroy');
        }
    }

    public function delete($id)
    {
        $id_kode = array('id_kode' => $id);
        $res = $this->MFiberstar_Project->deleteData($id_kode);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("Fiberstar_Project");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("Fiberstar_Project");
        }
    }


    public function Detail($primary_access_id_project)
    {

        $decoded_url_area = urldecode($primary_access_id_project);

        if (!empty($this->session->userdata('id_user'))) {
            $data['title'] = 'Monitoring Project PT. Fiberstar';
            $data['judul'] = 'DETAIL PROJECT ' . strtoupper($decoded_url_area);

            $data['progress_implementasi'] = $this->MFiberstar_Project->getProgressImplementasiFilter();
            $data['total_hp_plan'] = $this->MFiberstar_Project->getTotalHpPlanFilter();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Fiberstar_Project/indexarea', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function FilterDetail()
    {
        $data['title'] = 'DETAIL FILTER ' . $this->session->userdata('judul_filter_fs');
        $data['judul'] = 'DETAIL FILTER ' . $this->session->userdata('judul_filter_fs');
        $data['periode_tanggal'] = $this->session->userdata('periode_tanggal');

        echo ("<script>console.log('PHP: " . $data['periode_tanggal'] . "');</script>");

        $data['gettopAreaBAKDetail'] = $this->session->userdata('gettopAreaBAKDetail'); // Ambil dari session

        if (empty($data['gettopAreaBAKDetail'])) {
            show_error("Data tidak ditemukan!", 404);
        }


        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Fiberstar_Project/indexfilter', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveDetailToSession()
    {
        $data = $this->input->post('data');
        $judul = $this->input->post('judul');
        $periode_tanggal = $this->input->post('periode_tanggal');

        if (!empty($periode_tanggal)) {
            list($startDate, $endDate) = explode(" - ", $periode_tanggal);

            // Ubah format tanggal ke Indonesia
            function formatTanggalIndonesia($tanggal)
            {
                $dateObj = DateTime::createFromFormat('m/d/Y', $tanggal);
                $bulanIndonesia = [
                    "JANUARI",
                    "FEBRUARI",
                    "MARET",
                    "APRIL",
                    "MEI",
                    "JUNI",
                    "JULI",
                    "AGUSTUS",
                    "SEPTEMBER",
                    "OKTOBER",
                    "NOVEMBER",
                    "DESEMBER"
                ];
                return $dateObj->format('d') . ' ' . $bulanIndonesia[$dateObj->format('n') - 1] . ' ' . $dateObj->format('Y');
            }

            // Format ulang tanggal awal dan akhir
            $tanggalAwal = formatTanggalIndonesia($startDate);
            $tanggalAkhir = formatTanggalIndonesia($endDate);

            // Gabungkan kembali ke format Indonesia
            $periode_tanggal_indonesia = "$tanggalAwal - $tanggalAkhir";
        } else {
            $periode_tanggal_indonesia = "kosong";
        }




        if (!empty($data)) {
            $this->session->set_userdata('gettopAreaBAKDetail', json_decode($data, true)); // Simpan ke session
            $this->session->set_userdata('judul_filter_fs', $judul);
            $this->session->set_userdata('periode_tanggal', $periode_tanggal_indonesia);
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Data kosong!"]);
        }
    }

    
}
