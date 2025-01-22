<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fiberstar_Project_Detail extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $config['upload_path'] = FCPATH . 'folders\\';
        $config['allowed_types'] = 'jpg|png|pdf|docx';
        $config['max_size'] = 2048; // Maksimum 2MB
        $config['encrypt_name'] = TRUE; // Enkripsi nama file

        $this->load->library('upload', $config);

        $this->load->library('form_validation');
        $this->load->model('MFiberstar_Project_Detail');
    }


    public function detailImplementasi($primary_access_id_project)
    {
        $primary_access_id_project = array('primary_access_id_project' => $primary_access_id_project);

        $now = date('Y-m-d');

        $data['title'] = 'LIST PO';
        $data['judul'] = 'PT. Fiberstar';
        $data['progress_implementasi'] = $this->MFiberstar_Project_Detail->getProgressImplementasi();
        $data['detail_progress_implementasi'] = $this->MFiberstar_Project_Detail->getDetailProgressImplementasi();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Fiberstar_Project_Detail/Index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');

    }

    public function addRAB()
    {
        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $post_progress_implementasi = array(
            'primary_access_id_project' => $_POST['primary_access_id_project'],
            'id_user' => $_POST['id_user'],
            'plan_tiang' => $_POST['plan_tiang'],
            'plan_kabel_24' => $_POST['plan_kabel_24'],
            'plan_kabel_48' => $_POST['plan_kabel_48'],
            'plan_fat' => $_POST['plan_fat'],
            'plan_closure' => $_POST['plan_closure'],
            'data_created' => $_POST['data_created'],
            'keterangan_progress' => $_POST['keterangan_progress']
        );

        $res = $this->MFiberstar_Project_Detail->addProgressRAB($post_progress_implementasi);
        $previousUrl = $this->input->server('HTTP_REFERER');

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect($previousUrl);
        }
    }

    public function upload_file()
    {
        $uploadDir = './uploads/';

// Pastikan folder upload ada
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Cek apakah ada file yang diupload
if (isset($_FILES['file'])) {
    $fileName = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileError = $_FILES['file']['error'];

    // Mengecek apakah tidak ada error saat upload
    if ($fileError === UPLOAD_ERR_OK) {
        // Menyimpan file ke folder upload
        if (move_uploaded_file($fileTmp, $uploadDir . $fileName)) {
            echo 'File berhasil diupload!';
        } else {
            echo 'Gagal menyimpan file!';
        }
    } else {
        echo 'Terjadi kesalahan saat upload file!';
    }
} else {
    echo 'Tidak ada file yang diupload!';
}
    }

}
