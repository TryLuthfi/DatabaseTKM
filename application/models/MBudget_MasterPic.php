<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBudget_MasterPic extends CI_Model
{
    private $table = 'tb_master_user';

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
            ->select('id_user, nama_user, status_user')
            ->from($this->table)
            ->where('status_user', 'ACTIVE')
            ->where('nama_user IS NOT NULL', null, false)
            ->where('TRIM(nama_user) !=', '');

        if ($keyword !== '') {
            $this->db->like('nama_user', $keyword);
        }

        $this->db->order_by('nama_user', 'ASC');

        return $this->db->get()->result_array();
    }

    public function existsByName($namaUser, $excludeId = 0)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }

        $this->db->from($this->table);
        $this->db->where('nama_user', trim((string) $namaUser));
        $this->db->where('status_user', 'ACTIVE');
        if ((int) $excludeId > 0) {
            $this->db->where('id_user !=', (int) $excludeId);
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
            ->where('nama_user', $namaUser)
            ->where('status_user', 'ACTIVE')
            ->count_all_results() > 0;
    }

    public function savePic($namaUser)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }

        $payload = [
            'nama_user' => trim((string) $namaUser),
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
            ->where('id_user', (int) $id)
            ->update($this->table, [
                'nama_user' => trim((string) $namaUser),
            ]);

        return $this->db->affected_rows() >= 0;
    }

    public function deletePic($id)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }

        $this->db->delete($this->table, ['id_user' => (int) $id]);
        return $this->db->affected_rows() > 0;
    }

    public function getPicOptions()
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        return $this->db
            ->distinct()
            ->select('nama_user AS value, nama_user AS label')
            ->from($this->table)
            ->where('status_user', 'ACTIVE')
            ->where('nama_user IS NOT NULL', null, false)
            ->where('TRIM(nama_user) !=', '')
            ->order_by('nama_user', 'ASC')
            ->get()
            ->result_array();
    }
}
