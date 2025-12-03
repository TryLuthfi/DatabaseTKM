<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GHRooms extends CI_Controller
{

    public function __construct()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MGHRooms');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'List Kamar GH';
            $data['judul'] = 'List Kamar GH';
            $data['rooms'] = $this->MGHRooms->get_all();
            $data['types'] = $this->MGHRooms->get_types();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GHRooms/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function add()
{
    $data = [
        'room_type_id' => $this->input->post('room_type_id'),
        'code' => $this->input->post('code'),
        'name' => $this->input->post('name'),
        'price' => str_replace(".", "", $this->input->post('price')),
        'status' => $this->input->post('status'),
        'notes' => $this->input->post('notes')
    ];

    $insert = $this->MGHRooms->insert($data);

    echo json_encode([
        'status' => $insert ? 'sukses_tambah' : 'gagal_tambah'
    ]);
}


    public function edit($id)
    {
        $data = $this->MGHRooms->get_by_id($id);
        echo json_encode($data);
    }

    public function update($id)
{
    $data = [
        'room_type_id' => $this->input->post('room_type_id'),
        'code' => $this->input->post('code'),
        'name' => $this->input->post('name'),
        'price' => str_replace(".", "", $this->input->post('price')),
        'status' => $this->input->post('status'),
        'notes' => $this->input->post('notes')
    ];

    $update = $this->MGHRooms->update($id, $data);

    echo json_encode([
        'status' => $update ? 'sukses_edit' : 'gagal_edit'
    ]);
}

    public function delete($id)
{
    $delete = $this->MGHRooms->delete($id);

    echo json_encode([
        'status' => $delete ? 'sukses_hapus' : 'gagal_hapus'
    ]);
}

}
