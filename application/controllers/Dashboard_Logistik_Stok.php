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
        $data['getListGudangLokasiUser'] = $this->MDashboard_Logistik_Stok->getListGudangLokasiUser();
        $data['getMasterProject'] = $this->MDashboard_Logistik_Stok->getMasterProject();
        $data['getMasterSumberMaterial'] = $this->MDashboard_Logistik_Stok->getMasterSumberMaterial();
        $data['getMasterKodeItem'] = $this->MDashboard_Logistik_Stok->getMasterKodeItem();
        $data['getUniqueKotaGudang'] = $this->MDashboard_Logistik_Stok->getUniqueKotaGudang();
        $data['getUniqueProjectLogistik'] = $this->MDashboard_Logistik_Stok->getUniqueProjectLogistik();
        $data['getUniqueItemLogistik'] = $this->MDashboard_Logistik_Stok->getUniqueItemLogistik();
        $data['getUniqueSumberMaterial'] = $this->MDashboard_Logistik_Stok->getUniqueSumberMaterial();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Dashboard_Logistik_Stok/Index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function tambahReportStokLogistik(){

        $id_user = $this->session->userdata('id_user');

        

        $hasil_data = array(
            'id_lokasi_gudang' => $_POST['id_lokasi_gudang'],
            'id_bowheer' => $_POST['id_bowheer'],
            'id_sumber_material' => $_POST['id_sumber_material'],
            'id_kode_item' => $_POST['id_kode_item'],
            'jumlah_stok' => $_POST['jumlah_stok'],
            'satuan_stok' => $_POST['satuan_stok'],
            'merk_stok' => $_POST['merk_stok'],
            'no_haspel_stok' => $_POST['no_haspel_stok'],
            'no_ref_stok' => $_POST['no_ref_stok'],
            'keterangan_stok' => $_POST['keterangan_stok'],
            'keterangan_stok' => $_POST['keterangan_stok'],
            'tanggal_upload_stok' => $_POST['tanggal_upload_stok'],
            'evidence_stok' => $_POST['evidence_stok'],
            'id_user' => $id_user
        );

        $res = $this->MDashboard_Logistik_Stok->tambahReportStokLogistik($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("Dashboard_Logistik_Stok");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("Dashboard_Logistik_Stok");
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
}
