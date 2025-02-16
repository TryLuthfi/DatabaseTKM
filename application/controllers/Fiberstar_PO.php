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
        $data['list_bowheer'] = $this->db->get('tb_master_bowheer')->result_array();
        if ($this->session->userdata('lokasi_user') == "HO") {
            $data['list_po'] = $this->MFiberstar_PO->getPoAll();
        } else {
            $data['list_po'] = $this->MFiberstar_PO->getPoArea();
        }

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Fiberstar_PO/index', $data);
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
