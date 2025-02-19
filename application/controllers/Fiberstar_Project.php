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

            $data['title'] = 'LIST PO';
            $data['judul'] = 'PT. Fiberstar';
            $data['rincian'] = $this->MFiberstar_Project->getData();
            $data['main_data'] = $this->MFiberstar_Project->getMainData();
            $data['data_invoice'] = $this->MFiberstar_Project->getInvoice();
            $data['unique_regional'] = $this->MFiberstar_Project->getUniqueRegional();
            $data['unique_pic'] = $this->MFiberstar_Project->getUniquePic();
            $data['unique_area'] = $this->MFiberstar_Project->getUniqueArea();
            $data['unique_stagging'] = $this->MFiberstar_Project->getUniqueStagging();
            $data['top_area_cleanlist'] = $this->MFiberstar_Project->gettopAreaCleanlist();
            $data['top_area_bak'] = $this->MFiberstar_Project->gettopAreaBAK();
            $data['top_area_rfs'] = $this->MFiberstar_Project->gettopAreaRFS();
            $data['stagging_regional'] = $this->MFiberstar_Project->getStaggingRegional();
            $data['stagging_area'] = $this->MFiberstar_Project->getStaggingArea();
            $data['grafik_by_kota'] = $this->MFiberstar_Project->getGrafikByKota();

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

    public function filterTanggalChart(){

        $dateRange = $this->input->post('date_range');
        list($startDate, $endDate) = explode(" - ", $dateRange);
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));

        $data = $this->MFiberstar_Project->gettopAreaCleanlistFilterTanggalBeda($startDate, $endDate);

        if (!empty($data)) {
            echo json_encode([
                "status" => "success",
                "labels" => array_column($data, 'area_project'),
                "data" => array_column($data, 'hp_bak')
            ]);
        } else {
            echo json_encode(["status" => "error"]);
        }

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

    public function detailImplementasi($primary_access_id_project)
    {
        if (!empty($this->session->userdata('id_user'))) {
            $primary_access_id_project = array('primary_access_id_project' => $primary_access_id_project);

            $now = date('Y-m-d');

            $data['title'] = 'LIST PO';
            $data['judul'] = 'PT. Fiberstar';
            $data['rincian'] = $this->MFiberstar_Project->getData();
            $data['main_data'] = $this->MFiberstar_Project->getMainData();
            $data['data_invoice'] = $this->MFiberstar_Project->getInvoice();
            $data['total_hp_plan'] = $this->MFiberstar_Project->getTotalHpPlan();
            $data['unique_regional'] = $this->MFiberstar_Project->getUniqueRegional();
            $data['unique_pic'] = $this->MFiberstar_Project->getUniquePic();
            $data['unique_area'] = $this->MFiberstar_Project->getUniqueArea();
            $data['unique_stagging'] = $this->MFiberstar_Project->getUniqueStagging();
            $data['top_area_bak'] = $this->MFiberstar_Project->gettopAreaBAK();
            $data['progress_implementasi'] = $this->MFiberstar_Project->getProgressImplementasi();
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
}
