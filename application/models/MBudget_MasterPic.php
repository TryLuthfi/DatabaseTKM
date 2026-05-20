<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBudget_MasterPic extends CI_Model
{
    private $table = 'tb_master_user_new';

    public function getAvailableUsers()
    {
        return $this->getPics();
    }

    public function getPics($keyword = '')
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        $this->db
            ->select('id AS id_user, nama_karyawan AS nama_user, status_user')
            ->from($this->table)
            ->where('status_user', 'ACTIVE')
            ->where('nama_karyawan IS NOT NULL', null, false)
            ->where('TRIM(nama_karyawan) !=', '');

        if ($keyword !== '') {
            $this->db->like('nama_karyawan', $keyword);
        }

        $this->db->order_by('nama_karyawan', 'ASC');

        return $this->db->get()->result_array();
    }

    public function existsByName($namaUser, $excludeId = 0)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }

        $this->db->from($this->table);
        $this->db->where('nama_karyawan', trim((string) $namaUser));
        $this->db->where('status_user', 'ACTIVE');
        if ((int) $excludeId > 0) {
            $this->db->where('id !=', (int) $excludeId);
        }

        return $this->db->count_all_results() > 0;
    }

    public function isActiveMasterUser($namaUser)
    {
        $namaUser = trim((string) $namaUser);
        if ($namaUser === '' || !$this->db->table_exists($this->table)) {
            return false;
        }

        return $this->db
            ->from($this->table)
            ->where('nama_karyawan', $namaUser)
            ->where('status_user', 'ACTIVE')
            ->count_all_results() > 0;
    }

    public function savePic($namaUser)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }

        $payload = [
            'nama_karyawan' => trim((string) $namaUser),
            'status_user' => 'ACTIVE',
        ];

        $this->db->insert($this->table, $payload);
        return $this->db->insert_id();
    }

    public function updatePic($id, $namaUser)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }

        $this->db
            ->where('id', (int) $id)
            ->update($this->table, [
                'nama_karyawan' => trim((string) $namaUser),
            ]);

        return $this->db->affected_rows() >= 0;
    }

    public function deletePic($id)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }

        $this->db->delete($this->table, ['id' => (int) $id]);
        return $this->db->affected_rows() > 0;
    }

    public function getPicOptions()
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        return $this->db
            ->distinct()
            ->select('nama_karyawan AS value, nama_karyawan AS label')
            ->from($this->table)
            ->where('status_user', 'ACTIVE')
            ->where('nama_karyawan IS NOT NULL', null, false)
            ->where('TRIM(nama_karyawan) !=', '')
            ->order_by('nama_karyawan', 'ASC')
            ->get()
            ->result_array();
    }
}

