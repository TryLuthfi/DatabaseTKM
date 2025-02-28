<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logistik_Purchase_Request extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MLogistik_Purchase_Request');
        $this->load->model('MDashboard_Logistik_Stok');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Purchase Request';
            $data['list_purchase_request_area'] = $this->MLogistik_Purchase_Request->get_all_purchase_request('area');
            $data['list_purchase_request_ho'] = $this->MLogistik_Purchase_Request->get_all_purchase_request('ho');
            $data['list_master_gudang'] = $this->MLogistik_Purchase_Request->get_all_gudang();
            $data['get_master_project'] = $this->MDashboard_Logistik_Stok->getMasterProject();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('logistik_purchase_request', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function add_purchase_request()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $id_purchase_request = $this->generateUniqId('PR');

            $data_head = [
                'id_purchase_request' => $id_purchase_request,
                'nomor_purchase_request' => $this->input->post('nomor_pr'),
                'tanggal_pembuatan' => $this->input->post('tanggal_upload_pr'),
                'id_project' => $this->input->post('nama_bowher'),
                'lokasi_project' => $this->input->post('lokasi_project'),
                'pembuat' => $this->session->userdata('id_user'),
                'tanggal_estimasi_pengiriman' => $this->input->post('tanggal_pengiriman'),
                'nama_project' => $this->input->post('nama_project'),
                'nomer_sp' => $this->input->post('nomor_sp'),
                'tanggal_sp' => $this->input->post('tanggal_sp'),
            ];

            $data_child = [];

            foreach ($this->input->post('id_kode_item_') as $key => $value) {
                $data_child[] = [
                    'id_purchase_request_detail' => $this->generateUniqId('PR' . $key),
                    'id_purchase_request' => $id_purchase_request,
                    'id_kode_item' => $this->input->post('id_kode_item_')[$key],
                    'stok_area' => $this->input->post('stok_area_')[$key],
                    'qty_request' => $this->input->post('stok_request_')[$key],
                    'keterangan' => $this->input->post('keterangan_')[$key],
                ];
            }

            $this->db->trans_start();
            $this->db->insert('tb_logistik_purchase_request', $data_head);
            if (!empty($data_child)) {
                $this->db->insert_batch('tb_logistik_purchase_request_detail', $data_child);
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->session->set_flashdata('error', 'Gagal menyimpan data purchase request. Silakan coba lagi.');
            } else {
                $this->session->set_flashdata('success', 'Berhasil Menyimpan Data!');
            }

            redirect('Logistik_Purchase_Request/index/');
        } else {
            redirect('Auth');
        }
    }

    public function delete_purchase_request($id_purchase_request)
    {
        $this->db->trans_start();
        $this->db->delete('tb_logistik_purchase_request_detail', ['id_purchase_request' => $id_purchase_request]);
        $this->db->delete('tb_logistik_purchase_request', ['id_purchase_request' => $id_purchase_request]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal menghapus data. Silakan coba lagi.');
        } else {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        }

        redirect('Logistik_Purchase_Request/index/');
    }

    public function view_purchase_request($id_purchase_request)
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Purchase Request';
            $data['type'] = 'view';
            $data['detail_purchase_request'] = $this->MLogistik_Purchase_Request->get_detail_purchase_request($id_purchase_request);

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('logistik_purchase_request_detail', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function edit_purchase_request($id_purchase_request)
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Purchase Request';
            $data['type'] = 'edit';
            $data['detail_purchase_request'] = $this->MLogistik_Purchase_Request->get_detail_purchase_request($id_purchase_request);

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('logistik_purchase_request_detail', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }
    public function edit_purchase_request_by_planning()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $id_purchase_request = $this->input->post('id_purchase_request');
            $id_detail = $this->input->post('id_purchase_request_detail_');
            $boq = $this->input->post('boq_');
            $qty_planning = $this->input->post('qty_planning_');
            $ket_planning = $this->input->post('keterangan_planning_');
            if (!empty($id_detail) && !empty($boq) && !empty($qty_planning)) {

                foreach ($id_detail as $key => $id) {
                    $data = [
                        'boq' => $boq[$key],
                        'qty_planning' => $qty_planning[$key],
                        'keterangan_planning' => $ket_planning[$key]
                    ];

                    $this->db->where('id_purchase_request_detail', $id);
                    $this->db->update('tb_logistik_purchase_request_detail', $data);
                }

                $this->db->where('id_purchase_request', $id_purchase_request);
                $this->db->update('tb_logistik_purchase_request', ['approved_planning' => '1']);

                $this->session->set_flashdata('success', 'Data berhasil diperbarui');
            } else {
                $this->session->set_flashdata('error', 'Data tidak boleh kosong');
            }

            redirect('Logistik_Purchase_Request/view_purchase_request/' . $id_purchase_request);
        } else {
            redirect('Auth');
        }
    }

    public function approve_purchase_request(){
        if (!empty($this->session->userdata('id_user'))) {
            $id_purchase_request = $this->input->post('id_purchase_request');
            $tipe = strtolower($this->input->post('tipe')); // Konversi tipe ke lowercase

            // Pastikan tipe valid sebelum update
            $column = ($tipe == 'finance') ? 'approved_finance' : (($tipe == 'direktur') ? 'approved_direktur' : null);

            if ($column) {
                $this->db->where('id_purchase_request', $id_purchase_request);
                $update = $this->db->update('tb_logistik_purchase_request', [$column => 1]);

                if ($update) {
                    $this->session->set_flashdata('success', 'Data berhasil diperbarui.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui data.');
                }
            } else {
                $this->session->set_flashdata('error', 'Tipe approval tidak valid.');
            }
            return true;
        }else{
            redirect('Auth');
        }
    }

    public function generateUniqId($prefix)
    {
        $prefix = substr($prefix, 0, 3);
        $uniqid = uniqid('', true);
        $uniqid = substr(str_replace('.', '', $uniqid), 0, 6);
        $timestamp = time(); // Ambil timestamp saat ini
        return strtoupper($prefix . '_' . $uniqid . '_' . $timestamp);
    }
}
