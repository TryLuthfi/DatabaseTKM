<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GHRoomsAssets extends CI_Controller
{

    public function __construct()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MGHRoomsAssets');
        $this->load->model("MGHRooms");
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'List Kamar GH';
            $data['judul'] = 'List Kamar GH';
            $data['rooms'] = $this->MGHRooms->get_all();
            $data['asset_types'] = $this->MGHRoomsAssets->getAssetTypes();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GHRoomsAssets/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function list_by_room($room_id)
    {
        echo json_encode($this->MGHRoomsAssets->getRoomAssets($room_id));
    }

    public function get($id)
    {
        echo json_encode($this->MGHRoomsAssets->getById($id));
    }

    public function add()
    {
        $data = [
            "room_id"        => $this->input->post("room_id"),
            "asset_type_id"  => $this->input->post("asset_type_id"),
            "description"    => $this->input->post("description"),
            "asset_condition"=> $this->input->post("asset_condition"),
            "note"           => $this->input->post("note")
        ];

        $this->MGHRoomsAssets->insert($data);
        echo json_encode(["status" => "sukses_tambah"]);
    }

    public function update($id)
    {
        $data = [
            "asset_type_id"  => $this->input->post("asset_type_id"),
            "description"    => $this->input->post("description"),
            "asset_condition"=> $this->input->post("asset_condition"),
            "note"           => $this->input->post("note")
        ];

        $this->MGHRoomsAssets->updateData($id, $data);
        echo json_encode(["status" => "sukses_edit"]);
    }

    public function delete($id)
    {
        $delete = $this->MGHRoomsAssets->deleteData($id);
        echo json_encode(["status" => $delete ? "sukses_hapus" : "gagal_hapus"]);
    }

}
