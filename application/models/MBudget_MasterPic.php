<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBudget_MasterPic extends CI_Model
{
    private $table = 'tb_budget_master_pic';
    private $legacyUserTable = 'tb_master_user_new';

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
            ->select('id_budget_pic, nama_user, "ACTIVE" AS status_user', false)
            ->from($this->table)
            ->where('nama_user IS NOT NULL', null, false)
            ->where('TRIM(nama_user) !=', '');

        if ($keyword !== '') {
            $this->db->like('nama_user', $keyword);
        }

        $this->db->order_by('nama_user', 'ASC');

        return (array) $this->db->get()->result_array();
    }

    public function existsByName($namaUser, $excludeId = 0)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }

        $normalizedName = trim((string) $namaUser);
        if ($normalizedName === '') {
            return false;
        }

        $this->db->from($this->table);
        $this->db->where('LOWER(TRIM(nama_user)) =', strtolower($normalizedName));
        if ((int) $excludeId > 0) {
            $this->db->where('id_budget_pic !=', (int) $excludeId);
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
            ->where('LOWER(TRIM(nama_user)) =', strtolower($namaUser))
            ->count_all_results() > 0;
    }

    public function savePic($namaUser)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }

        $payload = [
            'nama_user' => trim((string) $namaUser),
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
            ->where('id_budget_pic', (int) $id)
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

        $this->db->delete($this->table, ['id_budget_pic' => (int) $id]);
        return $this->db->affected_rows() > 0;
    }

    public function getPicOptions()
    {
        if ($this->db->table_exists($this->table)) {
            $rows = (array) $this->db
                ->distinct()
                ->select('nama_user AS value, nama_user AS label')
                ->from($this->table)
                ->where('nama_user IS NOT NULL', null, false)
                ->where('TRIM(nama_user) !=', '')
                ->order_by('nama_user', 'ASC')
                ->get()
                ->result_array();
            if (!empty($rows)) {
                return $rows;
            }
        }

        // fallback transisi lama agar data existing tidak langsung putus
        if (!$this->db->table_exists($this->legacyUserTable)) {
            return [];
        }

        return (array) $this->db
            ->distinct()
            ->select('nama_karyawan AS value, nama_karyawan AS label')
            ->from($this->legacyUserTable)
            ->where('status_user', 'ACTIVE')
            ->where('nama_karyawan IS NOT NULL', null, false)
            ->where('TRIM(nama_karyawan) !=', '')
            ->order_by('nama_karyawan', 'ASC')
            ->get()
            ->result_array();
    }
}

