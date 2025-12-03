<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GHTenantsRooms extends CI_Controller
{

    public function __construct()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MGHTenantsRooms');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'List Kamar GH';
            $data['judul'] = 'List Kamar GH';
            $data['tenants_rooms'] = $this->MGHTenantsRooms->get_all();
            $data['tenants'] = $this->MGHTenantsRooms->get_tenants();
            $data['rooms'] = $this->MGHTenantsRooms->get_rooms();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GHTenantsRooms/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function filter()
    {
        $tenants = $this->input->post('tenants');
        $rooms = $this->input->post('rooms');
        $status = $this->input->post('status');

        // ambil data table
        $rows = $this->MGHTenantsRooms->get_filtered($tenants, $rooms, $status);

        // ambil dropdown dynamic
        $filterData = $this->MGHTenantsRooms->get_available_filters($tenants, $rooms, $status);

        echo json_encode([
            'rows' => $rows,
            'filters' => $filterData
        ]);
    }

    public function get($id)
    {
        echo json_encode($this->MGHTenantsRooms->getById($id));
    }

    public function add()
    {
        $data = [
            "tenant_id" => $this->input->post("tenant_id"),
            "room_id" => $this->input->post("room_id"),
            "contract_start" => $this->input->post("contract_start"),
            "contract_end" => $this->input->post("contract_end"),
            "billing_day" => $this->input->post("billing_day"),
            "active" => 1
        ];

        $this->MGHTenantsRooms->insert($data);

        echo json_encode(["status" => "sukses_tambah"]);
    }

    public function update($id)
    {
        $data = [
            "contract_start" => $this->input->post("contract_start"),
            "contract_end" => $this->input->post("contract_end"),
            "billing_day" => $this->input->post("billing_day"),
            "active" => $this->input->post("active"),
            "notes" => $this->input->post("notes"),
            "ended_at" => $this->input->post("active") == 0 ? date("Y-m-d") : null
        ];

        $this->MGHTenantsRooms->updateData($id, $data);

        echo json_encode(["status" => "sukses_edit"]);
    }

    public function delete($id)
    {
        $delete = $this->MGHTenantsRooms->deleteData($id);
        echo json_encode(["status" => $delete ? "sukses_hapus" : "gagal_hapus"]);
    }

}
