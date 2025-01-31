<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MListUser extends CI_Model
{

    public function getData()
    {
        $data = $this->db->query('SELECT * from tb_master_user left join tb_level on tb_master_user.id_level = tb_level.id_level left join tb_jabatan ON tb_master_user.id_jabatan = tb_jabatan.id_jabatan order by id_user ASC;')->result_array();
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
        $data = $this->db->query('SELECT tb_jabatan.nama_jabatan, COUNT(tb_master_user.id_jabatan) as jumlah_jabatan from tb_master_user right join tb_jabatan on tb_master_user.id_jabatan = tb_jabatan.id_jabatan GROUP BY tb_jabatan.nama_jabatan ORDER BY tb_jabatan.id_jabatan ASC;')->result_array();
        return $data;
    }

    public function getCountActiveUser()
    {
        $data = $this->db->query('SELECT status_user, COUNT(status_user) as jumlahActiveUser FROM `tb_master_user` GROUP BY status_user;')->result_array();
        return $data;
    }

    public function addUser($data_array)
    {
        $res = $this->db->insert("tb_master_user", $data_array);
        return $res;
    }

    public function deleteUser($id)
    {
        $res = $this->db->delete("tb_master_user", $id);
        return $res;
    }

    public function updateUser($data_array, $id)
    {
        $res = $this->db->update("tb_master_user", $data_array, $id);
        return $res;
    }
}
