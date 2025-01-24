<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fiberstar_PO extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MFiberstar_PO');
    }
    
    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

        $data['title'] = 'PT. Fiberstar';
        $data['judul'] = 'PT. Fiberstar';
        $data['list_bowheer'] = $this->db->get('tb_bowheer')->result_array();
        $data['totalInvoiceFiberstar'] = $this->MFiberstar_PO->getInvoiceFiberstar();
        $data['list_po'] = $this->db->query('SELECT * FROM `tb_project_fiberstar_po` join tb_project_fiberstar ON tb_project_fiberstar_po.id_cluster_fiberstar = tb_project_fiberstar.id_cluster_fiberstar JOIN tb_area ON tb_project_fiberstar.id_area = tb_area.id_area LEFT JOIN tb_project_fiberstar_progress ON tb_project_fiberstar_po.id_cluster_fiberstar = tb_project_fiberstar_progress.id_cluster_fiberstar_po LEFT JOIN tb_project_fiberstar_stagging ON tb_project_fiberstar_progress.stagging_pekerjaan_project_fiberstar_progress = tb_project_fiberstar_stagging.id_project_fiberstar_stagging;')->result_array();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Fiberstar_PO/Index', $data);
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

        $hasil_data = array(
            'kode_provider' => 1,
            'nomor_po' => $_POST['nomor_po'],
            'nilai_po' => $_POST['nilai_po'],
            'tanggal_po' => $_POST['tanggal_po'],
            'versi_po' => $_POST['versi_po'],
            'kode_po' => $_POST['kode_po'],
            'status_po' => $_POST['status_po']
        );

        $po = $this->db->get_where('tb_kode', ['nomor_po' => $_POST['nomor_po']])->row_array();
        if ($_POST['nomor_po'] == $po['nomor_po']) {
            $this->session->set_flashdata('status', 'PO sudah ada');
            redirect('Fiberstar_PO');
        } else {
            $res = $this->MFiberstar_PO->addPO($hasil_data);

            if ($res >= 1) {
                $this->session->set_flashdata('status', 'sukses_tambah');
                redirect("Fiberstar_PO");
            } else {
                $this->session->set_flashdata('status', 'gagal_tambah');
                redirect("Fiberstar_PO");
            }
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

        $res = $this->MFiberstar_PO->updateData($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("Fiberstar_PO");
            $status = $this->session->flashdata('destroy');
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("Fiberstar_PO");
            $status = $this->session->flashdata('destroy');
        }
    }

    public function delete($id)
    {
        $id_kode = array('id_kode' => $id);
        $res = $this->MFiberstar_PO->deleteData($id_kode);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("Fiberstar_PO");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("Fiberstar_PO");
        }
    }
}
