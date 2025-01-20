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

        if ($this->session->userdata('tim_project') == "HO") {
            $data['progress_implementasi'] = $this->MFiberstar_Project->getProgressImplementasiAll();
            $data['total_hp_plan'] = $this->MFiberstar_Project->getTotalHpPlanAll();
        } else {
            $data['progress_implementasi'] = $this->MFiberstar_Project->getProgressImplementasiArea();
            $data['total_hp_plan'] = $this->MFiberstar_Project->getTotalHpPlanArea();
        }

        $data['kode_akun'] = $this->db->get('tb_project_fiberstar')->result_array();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Fiberstar_Project/Index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');

        // $total_nilai_po = 0;
        // foreach ($rincian as $data) :
        //   $total_nilai_po +=
        // endforeach
    }


    public function add()
    {
        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $post_progress_implementasi = array(
            'primary_access_id_project' => $_POST['primary_access_id_project'],
            'id_user' => $_POST['id_user'],
            'achiev_tiang' => $_POST['achiev_tiang'],
            'achiev_kabel_24' => $_POST['achiev_kabel_24'],
            'achiev_kabel_48' => $_POST['achiev_kabel_48'],
            'achiev_fat' => $_POST['achiev_fat'],
            'achiev_closure' => $_POST['achiev_closure'],
            'data_created' => $_POST['data_created'],
            'keterangan_progress' => $_POST['keterangan_progress']
        );

        $res = $this->MFiberstar_Project->addProgressImplementasi($post_progress_implementasi);
        $previousUrl = $this->input->server('HTTP_REFERER');

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect($previousUrl);
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
        $this->load->view('Fiberstar_Project/Index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');

    }
}
