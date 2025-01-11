<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ListBowheer extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MListBowheer');
    }

    public function index()
    {
        $now = date('Y-m-d');

        $data['title'] = 'List Target Bowheer';
        $data['judul'] = 'List Target Bowheer TKM';
        $data['rincian_bowheer'] = $this->MListBowheer->getData();
        $data['bowheer'] = $this->db->get('tb_bowheer')->result_array();
        $data['list_user'] = $this->db->get('tb_user')->result_array();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('ListBowheer/Index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');

    }

    public function add()
    {

        $rincian_bowheer = $this->db->get('tb_bowheer')->row_array();
        // echo "<script>console.log('".$this->session->userdata('id_akun'). "');</script>";

        $target_bowheer = preg_replace('/[^0-9]/', '', $_POST['target_bowheer']);

        $hasil_data = array(
            'nama_bowheer' => $_POST['nama_bowheer'],
            'target_bowheer' => $target_bowheer,
            'id_user' => $_POST['id_user']
            // 'id_user' => $this->session->userdata('id_akun')
        );

        if ($_POST['nama_bowheer'] == $rincian_bowheer['nama_bowheer']) {
            $this->session->set_flashdata('status', 'Bowheer sudah ada');
            redirect('ListBowheer');
        } else {
            $res = $this->MListBowheer->addBowheer($hasil_data);

            if ($res >= 1) {
                $this->session->set_flashdata('status', 'sukses_tambah');
                redirect("ListBowheer");
            } else {
                $this->session->set_flashdata('status', 'gagal_tambah');
                redirect("ListBowheer");
            }
        }
    }

    public function edit($id)
    {

      $data_array = array(
          'nama_bowheer' => $_POST['nama_bowheer'],
          'id_user' => $_POST['id_user'],
          'target_bowheer' => preg_replace('/[^0-9]/', '', $_POST['target_bowheer'])
      );

        $where = array('id_bowheer' => $id);

        $res = $this->MListBowheer->updateBowheer($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("ListBowheer");
            $status = $this->session->flashdata('destroy');
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("ListBowheer");
            $status = $this->session->flashdata('destroy');
        }
    }

    public function delete($id)
    {
        $id_bowheer = array('id_bowheer' => $id);
        $res = $this->MListBowheer->deleteBowheer($id_bowheer);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("ListBowheer");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("ListBowheer");
        }
    }
}
