<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MListUser extends CI_Model
{

    public function getData()
    {
        $data = $this->db->query("
            SELECT
                u.id AS id_user,
                u.nik,
                u.nama_karyawan AS nama_user,
                u.username_user,
                u.password_user,
                CASE
                    WHEN COALESCE(TRIM(u.nik), '') <> ''
                        AND COALESCE(TRIM(u.password_user), '') = COALESCE(TRIM(u.nik), '')
                    THEN 'NY LOGIN'
                    ELSE 'DONE'
                END AS status_login,
                u.id_level,
                u.status_user,
                u.jenis_kelamin,
                u.homebase,
                u.divisi,
                u.departemen,
                u.telegram_user_id,
                u.jabatan AS nama_jabatan,
                l.nama_level
            FROM tb_master_user_new u
            LEFT JOIN tb_level l ON u.id_level = l.id_level
            ORDER BY u.id ASC
        ")->result_array();
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

