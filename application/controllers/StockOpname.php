<?php
defined('BASEPATH') or exit('No direct script access allowed');

class StockOpname extends CI_Controller
{

    public function __construct()
    {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MStockOpname');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'STOCK OPNAME LOGISTIK';
            $data['judul'] = 'STOCK OPNAME LOGISTIK';

            $data['getSOPeriode'] = $this->MStockOpname->getSOPeriode();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('StockOpname/soperiode', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function periode($id_sop, $id_lokasi_gudang = null)
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'DETAIL STOCK OPNAME LOGISTIK';
            $data['judul'] = 'DETAIL STOCK OPNAME LOGISTIK';
            $data['id_sop'] = $id_sop;

            if ($id_lokasi_gudang !== null) {
                $data['id_lokasi_gudang'] = $id_lokasi_gudang;
                $data['getSOItem'] = $this->MStockOpname->getSOItem($id_sop, $id_lokasi_gudang);
                $data['getDetailSoPeriode'] = $this->MStockOpname->getDetailSoPeriode($id_sop);

                $this->load->view('Templates/01_Header', $data);
                $this->load->view('Templates/02_Menu');
                $this->load->view('StockOpname/soitem', $data);
                $this->load->view('Templates/03_Footer');
                $this->load->view('Templates/99_JS');
            } else {
                $data['getSOKota'] = $this->MStockOpname->getSOKota($id_sop);

                $this->load->view('Templates/01_Header', $data);
                $this->load->view('Templates/02_Menu');
                $this->load->view('StockOpname/sokota', $data);
                $this->load->view('Templates/03_Footer');
                $this->load->view('Templates/99_JS');
            }
        } else {
            redirect('Auth');
        }
    }

    public function tambahPeriode()
    {

        $hasil_data = array(
            'sop_bulan' => $_POST['so_bulan'],
            'sop_tahun' => $_POST['so_tahun'],
            'sop_status' => 'NOT YET'
        );

        $res = $this->MStockOpname->tambahPeriode($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("StockOpname");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("StockOpname");
        }
    }

    public function hapusPeriode($id_sop)
    {
        $id_sop = array('id_sop' => $id_sop);
        $res = $this->MStockOpname->hapusPeriode($id_sop);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("StockOpname/periode");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("StockOpname/periode");
        }
    }

    public function hapusKota($id_so_kota)
    {
        $id_so_kota = array('id_so_kota' => $id_so_kota);
        $res = $this->MStockOpname->hapusKota($id_so_kota);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("StockOpname");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("StockOpname");
        }
    }

    public function cekPeriode()
    {
        header('Content-Type: application/json'); // Tambahkan ini

        $selectedBulan = $this->input->post('selectedBulan');
        $selectedTahun = $this->input->post('selectedTahun');

        // Debugging: Log input yang masuk
        log_message('error', 'Cek Nomor Surat Jalan - Input: ' . json_encode($_POST));

        if (!$selectedBulan || !$selectedTahun) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap!']);
            return;
        }

        $cek = $this->db->get_where('tb_so_periode', [
            'sop_bulan' => $selectedBulan,
            'sop_tahun' => $selectedTahun
        ])->row_array();

        // Debugging: Log hasil query
        log_message('error', 'Query Cek: ' . $this->db->last_query());

        if ($cek) {
            die(json_encode(['status' => 'exists']));
        } else {
            die(json_encode(['status' => 'available']));
        }
    }
}