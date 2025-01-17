<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MAuth extends CI_Model
{
    private $_table = 'tb_user';
    // public $username_acc;
    // public $password_acc;

    public function login()
    {
        $_POST = $this->input->post();

        $username = $_POST['username'];
        $pass = $_POST['pass'];

        $akun = $this->db->get_where($this->_table, ['username_user' => $username])->row_array();
        $akun = $this->db->query("select * from tb_user join tb_level on tb_user.id_level = tb_level.id_level where tb_user.username_user = '".$username."'")->row_array();
        if ($akun) {
            if ($akun['password_user'] == $pass) {
                $data =
                    [
                        'id_user' => $akun['id_user'],
                        'nama_user' => $akun['nama_user'],
                        'username_user' => $akun['username_user'],
                        'password_user' => $akun['password_user'],
                        'tim_project' => $akun['tim_project'],
                        'nama_level' => $akun['nama_level']
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
