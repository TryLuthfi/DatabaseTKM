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


    public function detailImplementasi()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'LIST PO';
            $data['judul'] = 'PT. Fiberstar';
            $data['progress_implementasi'] = $this->MFiberstar_Project_Detail->getProgressImplementasi();
            $data['getMasterUser'] = $this->MFiberstar_Project_Detail->getMasterUser();
            $data['detail_progress_implementasi'] = $this->MFiberstar_Project_Detail->getDetailProgressImplementasi();
            $data['master_data_dokument'] = $this->MFiberstar_Project_Detail->getMasterDataDokument();
            $data['master_data_stagging'] = array_unique(array_column($data['master_data_dokument'], 'stagging_document_support'));
            $data['data_document_support_approval'] = $this->MFiberstar_Project_Detail->getDataDocumentSupportApproval($data['progress_implementasi'][0]["primary_access_id_project"]);
            $data['count_document_support_approval'] = $this->MFiberstar_Project_Detail->getCountDocumentSupportApproval($data['progress_implementasi'][0]["primary_access_id_project"]);
            $data['dokument_support_approval_cbn'] = $this->MFiberstar_Project_Detail->getDokumentSupportApprovalCBN();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Fiberstar_Project_Detail/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }
    public function upload_document()
    {
        $this->load->helper('date');
        $config['upload_path'] = "./uploads/";
        $config['allowed_types'] = 'pdf|docx|xlsx|';
        $config['max_size'] = 5120;
        $file_ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $original_name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
        $new_filename = str_replace(" ", "_", $this->input->post('name_document_support')) . "_" . $this->input->post('primary_access_id_project') . "_" . date('Y-m-d') . "." . $file_ext;
        $config['file_name'] = $new_filename;
        $file_path = $config['upload_path'] . $new_filename;

        if (!is_dir('./uploads/')) {
            mkdir('./uploads/', 0777, true);
        }

        if (file_exists($file_path)) {
            unlink($file_path); // Hapus file lama
        }

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file')) {
            $error = $this->upload->display_errors(); //TAMPILKAN ERROR
            $this->session->set_flashdata('error', 'Format file tidak sesuai! atau File terlalu besar! ');
            redirect('Fiberstar_Project_Detail/detailImplementasi/' . $this->input->post('access_id_project'));
        } else {
            $fileData = $this->upload->data();
            $data = [
                'primary_access_id_project' => $this->input->post('primary_access_id_project'),
                'access_id_project' => $this->input->post('access_id_project'),
                'id_document_support_fiberstar' => $this->input->post('id_document_support'),
                'status_document_support' => 1, // 0 = NOT YET, 1 = ON REVIEW, 2 = APPROVED, 3 = REJECTED
                'id_user' => $this->input->post('id_user'),
                'document_support_location' => $file_path,
                'last_update' => date('Y-m-d'),
            ];

            if ($this->input->post('id_document_support_approval') != "") {
                // UPDATE 
                $this->db->where('id_document_support_approval', $this->input->post('id_document_support_approval'));
                $this->db->update('tb_ds_approval_cbn', $data);
            } else {
                // INSERT
                $this->db->insert('tb_ds_approval_cbn', $data);
            }

            $this->session->set_flashdata('success', 'Dokumen berhasil diupload!');
            redirect('Fiberstar_Project_Detail/detailImplementasi/' . $this->input->post('primary_access_id_project'));
        }
    }

    public function approve_dokumen($id_document_support_approval = null, $primary_access_id_project = null)
    {
        $this->db->where('id_document_support_approval', $id_document_support_approval)
            ->update('tb_ds_approval_cbn', ['status_document_support' => '2', 'remark' => '']);
        $this->session->set_flashdata('success', 'Dokumen berhasil di approve!');

        redirect('Fiberstar_Project_Detail/detailImplementasi/' . $primary_access_id_project);
    }

    public function reject_dokumen()
    {
        $this->db->where('id_document_support_approval', $this->input->post('id_document_support_approval_reject'))
            ->update('tb_ds_approval_cbn', ['status_document_support' => '3', 'remark' => $this->input->post('remark')]);
        $this->session->set_flashdata('success', 'Dokumen berhasil di reject!');

        redirect('Fiberstar_Project_Detail/detailImplementasi/' . $this->input->post('primary_access_id_project'));
    }

    public function addBoq()
    {
        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $post_progress_implementasi = array(
            'primary_access_id_project' => $_POST['primary_access_id_project'],
            'access_id_project' => $_POST['access_id_project'],
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

    public function addImplementasi()
    {
        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $post_progress_implementasi = array(
            'primary_access_id_project' => $_POST['primary_access_id_project'],
            'access_id_project' => $_POST['access_id_project'],
            'id_user' => $_POST['id_user'],
            'achiev_tiang' => $_POST['achiev_tiang'],
            'achiev_kabel_24' => $_POST['achiev_kabel_24'],
            'achiev_kabel_48' => $_POST['achiev_kabel_48'],
            'achiev_fat' => $_POST['achiev_fat'],
            'achiev_closure' => $_POST['achiev_closure'],
            'data_created' => $_POST['data_created'],
            'keterangan_progress' => $_POST['keterangan_progress']
        );

        $res = $this->MFiberstar_Project_Detail->addProgressImplementasi($post_progress_implementasi);
        $previousUrl = $this->input->server('HTTP_REFERER');

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect($previousUrl);
        }
    }

    public function editStatusImplementasi($primary_access_id_project)
    {
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

    public function editStatusImplementasiBack($primary_access_id_project)
    {
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

    public function editDataCluster()
    {

        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $data_array = array(
            'access_id_project' => $_POST['access_id_project'],
            'access_name_project' => $_POST['access_name_project'],
            'hpplan_project' => preg_replace('/[^0-9]/', '', $_POST['hpplan_project']),
            'pic_project' => $_POST['pic_project'],
            'regional_project' => $_POST['regional_project'],
            'provinsi_project' => $_POST['provinsi_project'],
            'area_project' => $_POST['area_project'],
            'number_po' => $_POST['number_po'],
            'tanggal_po' => $_POST['tanggal_po'],
            'nilai_awal_po' => preg_replace('/[^0-9]/', '', $_POST['nilai_awal_po']), // hanya angka
            'hp_po' => preg_replace('/[^0-9]/', '', $_POST['hp_po']), // hanya angka
            'tgl_canvasing' => $_POST['tgl_canvasing'],
            'status_bak' => $_POST['status_bak'],
            'tanggal_bak' => $_POST['tanggal_bak'],
            'hp_bak' => preg_replace('/[^0-9]/', '', $_POST['hp_bak']), // hanya angka
            'status_cbn' => $_POST['status_cbn'],
            'tgl_submite_cbn' => $_POST['tgl_submite_cbn'],
            'tgl_approve_cbn' => $_POST['tgl_approve_cbn'],
            'spk_nomor' => $_POST['spk_nomor'],
            'spk_tanggal' => $_POST['spk_tanggal'],
            'spk_hp' => preg_replace('/[^0-9]/', '', $_POST['spk_hp']), // hanya angka
            'status_hld' => $_POST['status_hld'],
            'hp_hld' => preg_replace('/[^0-9]/', '', $_POST['hp_hld']), // hanya angka
            'tgl_submit_hld' => $_POST['tgl_submit_hld'],
            'tgl_approve_hld' => $_POST['tgl_approve_hld'],
            'status_lld' => $_POST['status_lld'],
            'hp_lld' => preg_replace('/[^0-9]/', '', $_POST['hp_lld']), // hanya angka
            'tgl_submite_lld' => $_POST['tgl_submite_lld'],
            'tgl_approve_lld' => $_POST['tgl_approve_lld'],
            'tgl_kom' => $_POST['tgl_kom'],
            'tgl_pks' => $_POST['tgl_pks'],
            'tiang_implementasi' => $_POST['tiang_implementasi'],
            'kabel_implementasi' => $_POST['kabel_implementasi'],
            'dpfo_implementasi' => $_POST['dpfo_implementasi'],
            'cortiang_implementasi' => $_POST['cortiang_implementasi'],
            'atp_implementasi' => $_POST['atp_implementasi'],
            'status_implementasi' => $_POST['status_implementasi'],
            'tanggal_rfs' => $_POST['tanggal_rfs'],
            'hp_rfs' => preg_replace('/[^0-9]/', '', $_POST['hp_rfs']), // hanya angka
            'tanggal_atp' => $_POST['tanggal_atp'],
            'hp_atp' => preg_replace('/[^0-9]/', '', $_POST['hp_atp']), // hanya angka
            'tanggal_bast' => $_POST['tanggal_bast'],
            'main_status' => $_POST['main_status'],
            'remarks_status' => $_POST['remarks_status']
        );

        $where = array('primary_access_id_project' => $_POST['primary_access_id_project']);

        $res = $this->MFiberstar_Project_Detail->editDataCluster($data_array, $where);
        $previousUrl = $this->input->server('HTTP_REFERER');

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect($previousUrl);
            $status = $this->session->flashdata('destroy');
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect($previousUrl);
            $status = $this->session->flashdata('destroy');
        }

    }
}
