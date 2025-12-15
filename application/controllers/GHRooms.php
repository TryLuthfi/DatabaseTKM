<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GHRooms extends CI_Controller
{

    public function __construct()
    {

        // error_reporting(0);
        // ini_set('display_errors', 0);

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MGHRooms');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'List Kamar GH';
            $data['judul'] = 'List Kamar GH';
            $data['getAllRooms'] = $this->MGHRooms->getAllRooms();
            $data['types'] = $this->MGHRooms->get_types();
            $data['room_types_summary'] = $this->MGHRooms->getTypesSummary();
            $data['total_rooms'] = count($data['getAllRooms']);

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GHRooms/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function tambahKamar()
    {

        $previousUrl = $this->input->server('HTTP_REFERER');

        $hasil_data = array(
            'code' => $_POST['code'],
            'name' => $_POST['name'],
            'room_type_id' => $_POST['room_type_id'],
            'price' => $_POST['price'],
            'status' => $_POST['status'],
            'notes' => $_POST['notes']
        );

        $res = $this->MGHRooms->tambahKamar($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('statusAlert', 'sukses_tambah');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('statusAlert', 'gagal_tambah');
            redirect($previousUrl);
        }
    }

    public function editKamar($id)
    {
        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $previousUrl = $this->input->server('HTTP_REFERER');

        $hasil_data = array(
            'code' => $_POST['code'],
            'name' => $_POST['name'],
            'room_type_id' => $_POST['room_type_id'],
            'price' => $_POST['price'],
            'status' => $_POST['status'],
            'notes' => $_POST['notes']
        );

        $where = array('id' => $id);
        $res = $this->MGHRooms->editKamar($hasil_data, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('statusAlert', 'sukses_edit');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('statusAlert', 'gagal_edit');
            redirect($previousUrl);
        }
        
    }



    public function hapusKamar($id)
    {
        $previousUrl = $this->input->server('HTTP_REFERER');

        $id = array('id' => $id);
        $res = $this->MGHRooms->hapusKamar($id);

        if ($res >= 1) {
            $this->session->set_flashdata('statusAlert', 'sukses_hapus');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('statusAlert', 'gagal_hapus');
            redirect($previousUrl);
        }
    }

    

}
