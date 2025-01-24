<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fiberstar_Project_Detail extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('form_validation');
        $this->load->model('MFiberstar_Project_Detail');
    }


    public function detailImplementasi($primary_access_id_project)
    {
        if (!empty($this->session->userdata('id_user'))) {
            $primary_access_id_project = array('primary_access_id_project' => $primary_access_id_project);

            $data['title'] = 'LIST PO';
            $data['judul'] = 'PT. Fiberstar';
            $data['progress_implementasi'] = $this->MFiberstar_Project_Detail->getProgressImplementasi();
            $data['detail_progress_implementasi'] = $this->MFiberstar_Project_Detail->getDetailProgressImplementasi();
            $data['dokument_support_approval_cbn'] = $this->MFiberstar_Project_Detail->getDokumentSupportApprovalCBN();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Fiberstar_Project_Detail/Index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }

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

    public function editStatusImplementasi($primary_access_id_project){
        $primary = $primary_access_id_project;
        $previousUrl = $this->input->server('HTTP_REFERER');

        $res = $this->MFiberstar_Project_Detail->editStatusImplementasi($primary);
        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect($previousUrl);
        }
    }

    public function editStatusImplementasiBack($primary_access_id_project){
        $primary = $primary_access_id_project;
        $previousUrl = $this->input->server('HTTP_REFERER');

        $res = $this->MFiberstar_Project_Detail->editStatusImplementasiBack($primary);
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
        $uploadDir = './Doc Control/Kediri/ACCESS0047-11-2024 -  FBEOPA KDR REJOMULYO TK/ds_approval_cbn/sis/';
        $previousUrl = $this->input->server('HTTP_REFERER');

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
                    $this->session->set_flashdata('status', 'sukses_tambah');
                    redirect($previousUrl);
                } else {
                    $this->session->set_flashdata('status', 'gagal_tambah');
                    redirect($previousUrl);
                }
            } else {
                echo 'Terjadi kesalahan saat upload file!';
            }
        } else {
            echo 'Tidak ada file yang diupload!';
        }
    }

    public function download_file()
    {
        // $file_path = base_url('assets')."/files/Kediri/asd.pdf"; // Ganti dengan path file yang ingin Anda buka atau unduh
        $file_path = "http://databasetkm.infinityfreeapp.com/assets/files/Kediri/asd.pdf"; // Ganti dengan path file yang ingin Anda buka atau unduh

        // echo $file_path;
        // Periksa apakah file ada
        if (file_exists($file_path)) {
            // Set header untuk mendownload file
            force_download($file_path, NULL); // function ini memaksa file diunduh
        } else {
            echo $file_path;
            echo "File tidak ditemukan.";
        }
    }

}
