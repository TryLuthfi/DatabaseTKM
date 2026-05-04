<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logistik_Purchase_Request extends CI_Controller
{
    private $approvalMap = [
        'sm' => 'approved_sm',
        'rpm' => 'approved_rpm',
        'planning' => 'approved_planning',
        'manager_konstruksi' => 'approved_manager_konstruksi',
        'finance' => 'approved_finance',
        'gm' => 'approved_gm',
        'manager_logistik' => 'approved_manager_logistik',
        'direktur' => 'approved_direktur',
    ];

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
            $data['list_purchase_request_area'] = $this->MLogistik_Purchase_Request->decorate_purchase_request_rows(
                $this->MLogistik_Purchase_Request->get_all_purchase_request('area')
            );
            $data['list_purchase_request_ho'] = $this->MLogistik_Purchase_Request->decorate_purchase_request_rows(
                $this->MLogistik_Purchase_Request->get_all_purchase_request('ho')
            );
            $data['list_master_gudang'] = $this->MLogistik_Purchase_Request->get_all_gudang();
            $data['get_master_project'] = $this->MDashboard_Logistik_Stok->getMasterProject();
            $data['available_approval_columns'] = $this->MLogistik_Purchase_Request->resolve_available_approval_columns();

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

    public function get_material_options()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $projectItem = trim((string) $this->input->get('id_bowheer'));
        $idLokasiGudang = $this->input->get('id_lokasi_gudang');

        if ($projectItem === '') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([]));
            return;
        }

        $data = $this->MLogistik_Purchase_Request->get_material_options($projectItem, $idLokasiGudang);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
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
            if (empty($data['detail_purchase_request'])) {
                $this->session->set_flashdata('error', 'Data purchase request tidak ditemukan.');
                redirect('Logistik_Purchase_Request');
                return;
            }
            $data['purchase_request_meta'] = $this->MLogistik_Purchase_Request->decorate_purchase_request_row($data['detail_purchase_request'][0]);

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
            if (!$this->can_manage_planning()) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengedit review planning.');
                redirect('Logistik_Purchase_Request/view_purchase_request/' . $id_purchase_request);
                return;
            }

            $data['title'] = 'Purchase Request';
            $data['type'] = 'edit';
            $data['detail_purchase_request'] = $this->MLogistik_Purchase_Request->get_detail_purchase_request($id_purchase_request);
            if (empty($data['detail_purchase_request'])) {
                $this->session->set_flashdata('error', 'Data purchase request tidak ditemukan.');
                redirect('Logistik_Purchase_Request');
                return;
            }
            $data['purchase_request_meta'] = $this->MLogistik_Purchase_Request->decorate_purchase_request_row($data['detail_purchase_request'][0]);

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
            if (!$this->can_manage_planning()) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk menyimpan review planning.');
                redirect('Logistik_Purchase_Request');
                return;
            }

            $id_purchase_request = $this->input->post('id_purchase_request');
            $id_detail = $this->input->post('id_purchase_request_detail_');
            $volume_planning = $this->input->post('volume_planning_');
            $ket_planning = $this->input->post('keterangan_planning_');
            if (!empty($id_detail) && !empty($volume_planning)) {
                $hasVolumePlanningColumn = $this->db->field_exists('volume_planning', 'tb_logistik_purchase_request_detail');

                foreach ($id_detail as $key => $id) {
                    $data = [
                        'qty_planning' => $volume_planning[$key],
                        'keterangan_planning' => $ket_planning[$key]
                    ];

                    if ($hasVolumePlanningColumn) {
                        $data['volume_planning'] = $volume_planning[$key];
                    }

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

    public function upload_hardcopy()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $this->load->helper('date');
            $this->load->library('upload');

            $upload_path = "./uploads/";
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $id_purchase_request = $this->input->post('id_purchase_request');
            $timestamp = date('Y-m-d_H-i-s');
            $field_name = 'file-hardcopy';

            // Pastikan file ada sebelum lanjut
            if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
                $this->session->set_flashdata('error', 'Tidak ada file yang diunggah atau terjadi kesalahan!');
                redirect('Logistik_Purchase_Request/view_purchase_request/' . $id_purchase_request);
            }

            $file_ext = pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION);
            $Name_files = "PURCHASE_REQUEST_{$timestamp}_{$id_purchase_request}.{$file_ext}";

            $config = [
                'upload_path'   => $upload_path,
                'allowed_types' => 'pdf',
                'max_size'      => 5120,
                'file_name'     => $Name_files
            ];

            $this->upload->initialize($config);

            if (!$this->upload->do_upload($field_name)) {
                $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                redirect('Logistik_Purchase_Request/view_purchase_request/' . $id_purchase_request);
            }

            $uploaded_file = $this->upload->data('file_name');

            // Pastikan file berhasil diunggah sebelum update database
            if (!empty($uploaded_file)) {
                $this->db->where('id_purchase_request', $id_purchase_request);
                $this->db->update('tb_logistik_purchase_request', ['hardcopy_file' => $uploaded_file]);

                $this->session->set_flashdata('success', 'Dokumen berhasil diupload!');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengupload dokumen.');
            }

            redirect('Logistik_Purchase_Request/view_purchase_request/' . $id_purchase_request);
        } else {
            redirect('Auth');
        }
    }

    public function approve_purchase_request()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $id_purchase_request = $this->input->post('id_purchase_request');
            $tipe = strtolower(trim((string) $this->input->post('tipe')));
            $column = isset($this->approvalMap[$tipe]) ? $this->approvalMap[$tipe] : null;
            if ($column && !$this->db->field_exists($column, 'tb_logistik_purchase_request')) {
                $column = null;
            }

            if ($column) {
                if (!$this->can_approve_stage($tipe)) {
                    $this->output
                        ->set_status_header(403)
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'status' => 'error',
                            'message' => 'Anda tidak memiliki akses untuk approval tahap ini.'
                        ]));
                    return;
                }

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
        } else {
            redirect('Auth');
        }
    }

    public function print_purchase_request()
    {
        if (!empty($this->session->userdata('id_user'))) {
            // $data['title'] = 'Purchase Request';
            // $this->load->view('format_purchase_request_print', $data);
            $this->load->library('Mpdf_lib');
            $mpdf = $this->mpdf_lib->load();

            // Load view ke dalam variabel
            $data['title'] = "Laporan Data";
            $html = $this->load->view('laporan', $data, TRUE);

            // Tambahkan HTML ke PDF
            $mpdf->WriteHTML('<h1>tes</h1>');

            // Download PDF langsung
            $mpdf->Output("Laporan.pdf", "D");
        } else {
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

    private function is_super_admin()
    {
        return (string) $this->session->userdata('nama_level') === 'Super Admin';
    }

    private function get_validation_key()
    {
        return strtolower(str_replace(' ', '_', trim((string) $this->session->userdata('validation'))));
    }

    private function can_manage_planning()
    {
        return $this->is_super_admin() || $this->get_validation_key() === 'planning';
    }

    private function can_approve_stage($stageKey)
    {
        if ($this->is_super_admin()) {
            return true;
        }

        return $this->get_validation_key() === strtolower(trim((string) $stageKey));
    }
}
