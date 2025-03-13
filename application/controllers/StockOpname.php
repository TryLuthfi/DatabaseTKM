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

            $bulan_mapping = [
                'JANUARI' => '01', 'FEBRUARI' => '02', 'MARET' => '03', 'APRIL' => '04',
                'MEI' => '05', 'JUNI' => '06', 'JULI' => '07', 'AGUSTUS' => '08',
                'SEPTEMBER' => '09', 'OKTOBER' => '10', 'NOVEMBER' => '11', 'DESEMBER' => '12'
            ];
            $mode = $this->input->get('mode');

            $data['title'] = 'DETAIL STOCK OPNAME LOGISTIK';
            $data['judul'] = 'DETAIL STOCK OPNAME LOGISTIK';
            $data['id_sop'] = $id_sop;
            $data['mode'] = $mode;

            if ($id_lokasi_gudang !== null) {
                $data['id_lokasi_gudang'] = $id_lokasi_gudang;

                $data['getDetailSoPeriode'] = $this->MStockOpname->getDetailSoPeriode($id_sop);
                $bulan = $data['getDetailSoPeriode'][0]['sop_bulan'];
                $tahun = $data['getDetailSoPeriode'][0]['sop_tahun'];

                $bulan_angka = isset($bulan_mapping[$bulan]) ? $bulan_mapping[$bulan] : '01';
                $tanggal_input = "{$tahun}-{$bulan_angka}-01";
                $tanggal_sekarang = date('Y-m-d');

                if ($tanggal_sekarang > $tanggal_input) {
                    $jam_menit = '23:59:59'; // Jika lebih dari tanggal yang dihasilkan
                } else {
                    $jam_menit = date('H:i:s'); // Jika hari ini sama
                }

                $tanggal_format = "{$tanggal_input} {$jam_menit}";

                echo("<script>console.log('PHP: " . $tanggal_format . "');</script>");


                $data['getSOItem'] = $this->MStockOpname->getSOItem($id_lokasi_gudang, $tanggal_format);
                $data['getDetailSoItem'] = $this->MStockOpname->getDetailSoItem($id_sop,$id_lokasi_gudang);

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

    public function inputSO()
    {
        if (!empty($this->session->userdata('id_user'))) {
            // Ambil ID Periode & Lokasi dari form
            $id_sop = $this->input->post('id_sop');
            $id_lokasi_gudang = $this->input->post('id_lokasi_gudang');

            // Ambil data yang dikirim dalam bentuk array
            $id_kode_item = $this->input->post('id_kode_item');
            $total_jumlah_stok = $this->input->post('total_jumlah_stok');
            $stok_so = $this->input->post('stok_so');
            $keterangan = $this->input->post('keterangan');

            // Looping untuk menyimpan setiap item ke database
            $data_insert = [];
            foreach ($id_kode_item as $index => $kode_item) {
                // Cek apakah stok SO ada isinya atau tidak (hindari insert data kosong)
                $data_insert[] = [
                    'id_sop' => $id_sop,
                    'id_kota_gudang' => $id_lokasi_gudang,
                    'id_kode_item' => $kode_item,
                    'soi_stok_asli' => $total_jumlah_stok[$index] ?? 0, // Default 0 jika null
                    'soi_stok_opname' => $stok_so[$index] ?? 0, // Default 0 jika kosong
                    'soi_keterangan' => $keterangan[$index] ?? ''
                ];
            }

            $hasil_data = array(
                'id_so_periode' => $id_sop,
                'id_kota' => $id_lokasi_gudang,
                'sok_status' => 'DONE',
                'sok_tanggal' => date('Y-m-d H:i:s')
            );

            // Jika ada data yang akan disimpan
            if (!empty($data_insert)) {
                $this->MStockOpname->insertBatchSOItem($data_insert);
                $this->MStockOpname->tambahSoKota($hasil_data);
                $this->session->set_flashdata('success', 'Data stok opname berhasil disimpan.');
            } else {
                $this->session->set_flashdata('error', 'Tidak ada data stok opname yang disimpan.');
            }

            redirect('StockOpname/periode/' . $id_sop);
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