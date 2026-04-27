<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MAuth extends CI_Model
{
    private $_table = 'tb_master_user';
    // public $username_acc;
    // public $password_acc;

    public function login()
    {
        $_POST = $this->input->post();

        $username = trim((string) ($_POST['username'] ?? ''));
        $pass = (string) ($_POST['pass'] ?? '');

        $akun = $this->db->query("select
                                    a.*,
                                    tl.*,
                                    tj.*,
                                    COALESCE(b.nama_user, a.nama_user) as under_sm,
                                    COALESCE(c.nama_user, a.nama_user) as under_pm
                                from
                                    tb_master_user a
                                left join tb_master_user b on
                                    a.under_sm  = b.id_user
                                left join tb_master_user c on
                                    a.under_pm  = c.id_user
                                left join tb_level tl  ON a.id_level = tl.id_level
                                LEFT JOIN tb_jabatan tj ON a.id_jabatan = tj.id_jabatan
                                WHERE a.username_user = " . $this->db->escape($username) . "
                        ")->row_array();
        if ($akun) {
            if($akun['status_user'] !== 'ACTIVE'){
                $this->session->set_flashdata('error_log', 'tidak_ada');
                redirect('Auth');
            } else if ($akun['password_user'] == $pass) {
                $validationRows = $this->db
                    ->select('validation_user')
                    ->from('tb_master_user_child')
                    ->where('id_master_user', (int) $akun['id_user'])
                    ->get()
                    ->result_array();

                $validationList = [];

                if (!empty($akun['validation_user'])) {
                    $validationList[] = trim((string) $akun['validation_user']);
                }

                foreach ($validationRows as $row) {
                    $value = trim((string) ($row['validation_user'] ?? ''));
                    if ($value !== '') {
                        $validationList[] = $value;
                    }
                }

                $validationList = array_values(array_unique(array_filter($validationList)));

                $data =
                    [
                        'id_user' => $akun['id_user'],
                        'nama_user' => $akun['nama_user'],
                        'username_user' => $akun['username_user'],
                        'password_user' => $akun['password_user'],
                        'lokasi_user' => $akun['lokasi_user'],
                        'nama_level' => $akun['nama_level'],
                        'validation' => !empty($validationList) ? implode(', ', $validationList) : 'non',
                        'validation_user' => $validationList,
                        'nama_jabatan' => $akun['nama_jabatan'] ?? ''
                    ];
                $this->session->set_userdata($data);
                
                redirect('Dashboard');
            } else {
                $this->session->set_flashdata('error_log', 'salah');
                redirect('Auth');
            }
        } else {
            $this->session->set_flashdata('error_log', 'tidak_ada');
            redirect('Auth');
        }
        
    }
}
