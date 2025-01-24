<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MyRepublik_Project extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->model('MMyRepublik_Project');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $now = date('Y-m-d');

            $data['title'] = 'LIST PO';
            $data['judul'] = 'PT. MyRepublik';
            $data['data_invoice'] = $this->MMyRepublik_Project->getInvoice();
            $data['unique_provinsi'] = $this->MMyRepublik_Project->getUniqueProvinsi();
            $data['unique_type_project'] = $this->MMyRepublik_Project->getUniqueTipeProject();
            $data['status_project_tkm'] = $this->MMyRepublik_Project->getUniqueStatusProjectTKM();
            $data['unique_pic'] = $this->MMyRepublik_Project->getUniquePic();
            $data['unique_area'] = $this->MMyRepublik_Project->getUniqueArea();
            $data['unique_stagging'] = $this->MMyRepublik_Project->getUniqueStagging();
            $data['top_area_cleanlist'] = $this->MMyRepublik_Project->gettopAreaCleanlist();
            $data['top_area_bak'] = $this->MMyRepublik_Project->gettopAreaBAK();
            $data['top_area_rfs'] = $this->MMyRepublik_Project->gettopAreaRFS();
            $data['grafik_by_kota'] = $this->MMyRepublik_Project->getGrafikByKota();
            $data['stagging_regional'] = $this->MMyRepublik_Project->getStaggingRegional();

            if ($this->session->userdata('tim_project') == "HO") {
                $data['progress_implementasi'] = $this->MMyRepublik_Project->getProgressImplementasiAll();
                $data['total_hp_plan'] = $this->MMyRepublik_Project->getTotalHpPlanAll();
            } else {
                $data['progress_implementasi'] = $this->MMyRepublik_Project->getProgressImplementasiArea();
                $data['total_hp_plan'] = $this->MMyRepublik_Project->getTotalHpPlanArea();
            }

            $data['kode_akun'] = $this->db->get('tb_project_progress_myrep')->result_array();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('MyRepublik_Project/Index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
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

        $res = $this->MMyRepublik_Project->addProgressImplementasi($post_progress_implementasi);
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

        $res = $this->MMyRepublik_Project->updateData($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("MyRepublik_Project");
            $status = $this->session->flashdata('destroy');
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("MyRepublik_Project");
            $status = $this->session->flashdata('destroy');
        }
    }

    public function delete($id)
    {
        $id_kode = array('id_kode' => $id);
        $res = $this->MMyRepublik_Project->deleteData($id_kode);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("MyRepublik_Project");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("MyRepublik_Project");
        }
    }

    public function detailImplementasi($primary_access_id_project)
    {
        if (!empty($this->session->userdata('id_user'))) {
            $primary_access_id_project = array('primary_access_id_project' => $primary_access_id_project);

            $now = date('Y-m-d');

            $data['title'] = 'LIST PO';
            $data['judul'] = 'PT. MyRep';
            $data['rincian'] = $this->MMyRepublik_Project->getData();
            $data['main_data'] = $this->MMyRepublik_Project->getMainData();
            $data['data_invoice'] = $this->MMyRepublik_Project->getInvoice();
            $data['total_hp_plan'] = $this->MMyRepublik_Project->getTotalHpPlan();
            $data['unique_regional'] = $this->MMyRepublik_Project->getUniqueRegional();
            $data['unique_pic'] = $this->MMyRepublik_Project->getUniquePic();
            $data['unique_area'] = $this->MMyRepublik_Project->getUniqueArea();
            $data['unique_stagging'] = $this->MMyRepublik_Project->getUniqueStagging();
            $data['top_area_bak'] = $this->MMyRepublik_Project->gettopAreaBAK();
            $data['progress_implementasi'] = $this->MMyRepublik_Project->getProgressImplementasi();
            $data['kode_akun'] = $this->db->get('tb_project_progress_myrep')->result_array();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('MyRepublik_Project/Index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }

    }
}
