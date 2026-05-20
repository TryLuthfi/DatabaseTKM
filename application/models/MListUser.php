<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MListUser extends CI_Model
{

    public function getData()
    {
        $data = $this->db->query('SELECT tb_master_user_new.id AS id_user, tb_master_user_new.nama_karyawan AS nama_user, tb_master_user_new.username_user, tb_master_user_new.password_user, tb_master_user_new.id_level, tb_master_user_new.status_user, tb_master_user_new.telegram_user_id, tb_master_user_new.jabatan AS nama_jabatan, tb_level.* from tb_master_user_new left join tb_level on tb_master_user_new.id_level = tb_level.id_level order by tb_master_user_new.id ASC;')->result_array();
        return $data;
    }

    public function getJabatan()
    {
        $data = $this->db->query('SELECT * from tb_jabatan;')->result_array();
        return $data;
    }

    public function getLevel()
    {
        $data = $this->db->query('SELECT * from tb_level;')->result_array();
        return $data;
    }

    public function getCountJabatan()
    {
        $data = $this->db->query('SELECT COALESCE(NULLIF(tb_master_user_new.jabatan, \'\'), \'TANPA JABATAN\') AS nama_jabatan, COUNT(tb_master_user_new.id) AS jumlah_jabatan from tb_master_user_new GROUP BY COALESCE(NULLIF(tb_master_user_new.jabatan, \'\'), \'TANPA JABATAN\') ORDER BY nama_jabatan ASC;')->result_array();
        return $data;
    }

    public function getCountActiveUser()
    {
        $data = $this->db->query('SELECT status_user, COUNT(status_user) as jumlahActiveUser FROM `tb_master_user_new` GROUP BY status_user;')->result_array();
        return $data;
    }

    public function addUser($data_array)
    {
        $res = $this->db->insert("tb_master_user_new", $data_array);
        return $res;
    }

    public function deleteUser($id)
    {
        $res = $this->db->delete("tb_master_user_new", $id);
        return $res;
    }

    public function updateUser($data_array, $id)
    {
        $res = $this->db->update("tb_master_user_new", $data_array, $id);
        return $res;
    }
}

