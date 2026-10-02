<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MListArea extends CI_Model
{
    const MAX_TABLE_ROWS = 500;

    public function wilayahTablesReady()
    {
        foreach (['md_provinsi_indonesia', 'md_kokab_indonesia', 'md_kec_indonesia', 'md_dusun_indonesia'] as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function getSummary()
    {
        if (!$this->wilayahTablesReady()) {
            return [];
        }

        return [
            'province' => (int) $this->db->count_all('md_provinsi_indonesia'),
            'regency' => (int) $this->db->count_all('md_kokab_indonesia'),
            'district' => (int) $this->db->count_all('md_kec_indonesia'),
            'village' => (int) $this->db->count_all('md_dusun_indonesia'),
        ];
    }

    public function getRegionalOptions()
    {
        if (!$this->db->table_exists('tb_rfs_myrep_monthly_target')) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('regional_name')
            ->from('tb_rfs_myrep_monthly_target')
            ->where('regional_name IS NOT NULL', null, false)
            ->where("TRIM(regional_name) !=", '')
            ->order_by('regional_name', 'ASC')
            ->get()
            ->result_array();

        return array_values(array_filter(array_map(static function ($row) {
            return strtoupper(trim((string) ($row['regional_name'] ?? '')));
        }, $rows)));
    }

    public function getProvinceOptions($keyword = '', $regional = '', $limit = 100)
    {
        if (!$this->db->table_exists('md_provinsi_indonesia')) {
            return [];
        }

        $this->db
            ->distinct()
            ->select('p.id, p.name')
            ->from('md_provinsi_indonesia p');

        $this->applyRegionalProvinceJoin($regional);
        $this->applyKeyword('p.name', $keyword);

        return $this->mapOptionRows($this->db
            ->order_by('p.name', 'ASC')
            ->limit((int) $limit)
            ->get()
            ->result_array());
    }

    public function getRegencyOptions($provinceId = '', $keyword = '', $regional = '', $limit = 250)
    {
        if (!$this->db->table_exists('md_kokab_indonesia')) {
            return [];
        }

        $this->db
            ->distinct()
            ->select('r.id, r.name')
            ->from('md_kokab_indonesia r')
            ->join('md_provinsi_indonesia p', 'p.id = r.province_id', 'left');

        $provinceId = trim((string) $provinceId);
        if ($provinceId !== '') {
            $this->db->where('r.province_id', $provinceId);
        }

        $this->applyRegionalRegencyFilter($regional);
        $this->applyKeyword('r.name', $keyword);

        return $this->mapOptionRows($this->db
            ->order_by('r.name', 'ASC')
            ->limit((int) $limit)
            ->get()
            ->result_array());
    }

    public function getDistrictOptions($regencyId = '', $keyword = '', $limit = 250)
    {
        if (!$this->db->table_exists('md_kec_indonesia')) {
            return [];
        }

        $this->db
            ->select('d.id, d.name')
            ->from('md_kec_indonesia d');

        $regencyId = trim((string) $regencyId);
        if ($regencyId !== '') {
            $this->db->where('d.regency_id', $regencyId);
        }

        $this->applyKeyword('d.name', $keyword);

        return $this->mapOptionRows($this->db
            ->order_by('d.name', 'ASC')
            ->limit((int) $limit)
            ->get()
            ->result_array());
    }

    public function getVillageOptions($districtId = '', $keyword = '', $limit = 250)
    {
        if (!$this->db->table_exists('md_dusun_indonesia')) {
            return [];
        }

        $this->db
            ->select('v.id, v.name')
            ->from('md_dusun_indonesia v');

        $districtId = trim((string) $districtId);
        if ($districtId !== '') {
            $this->db->where('v.district_id', $districtId);
        }

        $this->applyKeyword('v.name', $keyword);

        return $this->mapOptionRows($this->db
            ->order_by('v.name', 'ASC')
            ->limit((int) $limit)
            ->get()
            ->result_array());
    }

    public function getProvinceRows(array $filters)
    {
        $this->db
            ->distinct()
            ->select('p.id, p.name')
            ->from('md_provinsi_indonesia p');

        $this->applyRegionalProvinceJoin($filters['regional'] ?? '');
        if (!empty($filters['province_id'])) {
            $this->db->where('p.id', $filters['province_id']);
        }
        $this->applyKeyword('p.name', $filters['keyword'] ?? '');

        return $this->db
            ->order_by('p.name', 'ASC')
            ->limit(self::MAX_TABLE_ROWS)
            ->get()
            ->result_array();
    }

    public function getRegencyRows(array $filters)
    {
        $this->db
            ->distinct()
            ->select('r.id, r.name, r.province_id, p.name AS province_name')
            ->from('md_kokab_indonesia r')
            ->join('md_provinsi_indonesia p', 'p.id = r.province_id', 'left');

        $this->applyRegionalRegencyFilter($filters['regional'] ?? '');
        if (!empty($filters['province_id'])) {
            $this->db->where('r.province_id', $filters['province_id']);
        }
        if (!empty($filters['regency_id'])) {
            $this->db->where('r.id', $filters['regency_id']);
        }
        $this->applyKeywordGroup(['r.name', 'p.name'], $filters['keyword'] ?? '');

        return $this->db
            ->order_by('p.name', 'ASC')
            ->order_by('r.name', 'ASC')
            ->limit(self::MAX_TABLE_ROWS)
            ->get()
            ->result_array();
    }

    public function getDistrictRows(array $filters)
    {
        $this->db
            ->distinct()
            ->select('d.id, d.name, d.regency_id, r.name AS regency_name, p.id AS province_id, p.name AS province_name')
            ->from('md_kec_indonesia d')
            ->join('md_kokab_indonesia r', 'r.id = d.regency_id', 'left')
            ->join('md_provinsi_indonesia p', 'p.id = r.province_id', 'left');

        $this->applyRegionalRegencyFilter($filters['regional'] ?? '');
        if (!empty($filters['province_id'])) {
            $this->db->where('p.id', $filters['province_id']);
        }
        if (!empty($filters['regency_id'])) {
            $this->db->where('d.regency_id', $filters['regency_id']);
        }
        if (!empty($filters['district_id'])) {
            $this->db->where('d.id', $filters['district_id']);
        }
        $this->applyKeywordGroup(['d.name', 'r.name', 'p.name'], $filters['keyword'] ?? '');

        return $this->db
            ->order_by('p.name', 'ASC')
            ->order_by('r.name', 'ASC')
            ->order_by('d.name', 'ASC')
            ->limit(self::MAX_TABLE_ROWS)
            ->get()
            ->result_array();
    }

    public function getVillageRows(array $filters)
    {
        $this->db
            ->distinct()
            ->select('v.id, v.name, v.district_id, d.name AS district_name, r.id AS regency_id, r.name AS regency_name, p.id AS province_id, p.name AS province_name')
            ->from('md_dusun_indonesia v')
            ->join('md_kec_indonesia d', 'd.id = v.district_id', 'left')
            ->join('md_kokab_indonesia r', 'r.id = d.regency_id', 'left')
            ->join('md_provinsi_indonesia p', 'p.id = r.province_id', 'left');

        $this->applyRegionalRegencyFilter($filters['regional'] ?? '');
        if (!empty($filters['province_id'])) {
            $this->db->where('p.id', $filters['province_id']);
        }
        if (!empty($filters['regency_id'])) {
            $this->db->where('r.id', $filters['regency_id']);
        }
        if (!empty($filters['district_id'])) {
            $this->db->where('v.district_id', $filters['district_id']);
        }
        if (!empty($filters['village_id'])) {
            $this->db->where('v.id', $filters['village_id']);
        }
        $this->applyKeywordGroup(['v.name', 'd.name', 'r.name', 'p.name'], $filters['keyword'] ?? '');

        return $this->db
            ->order_by('p.name', 'ASC')
            ->order_by('r.name', 'ASC')
            ->order_by('d.name', 'ASC')
            ->order_by('v.name', 'ASC')
            ->limit(self::MAX_TABLE_ROWS)
            ->get()
            ->result_array();
    }

    public function insertProvince(array $payload)
    {
        return $this->safeWrite(function () use ($payload) {
            return $this->db->insert('md_provinsi_indonesia', $payload);
        });
    }

    public function updateProvince($id, array $payload)
    {
        return $this->safeWrite(function () use ($id, $payload) {
            return $this->db->where('id', $id)->update('md_provinsi_indonesia', $payload);
        });
    }

    public function deleteProvince($id)
    {
        return $this->safeWrite(function () use ($id) {
            return $this->db->delete('md_provinsi_indonesia', ['id' => $id]);
        });
    }

    public function insertRegency(array $payload)
    {
        return $this->safeWrite(function () use ($payload) {
            return $this->db->insert('md_kokab_indonesia', $payload);
        });
    }

    public function updateRegency($id, array $payload)
    {
        return $this->safeWrite(function () use ($id, $payload) {
            return $this->db->where('id', $id)->update('md_kokab_indonesia', $payload);
        });
    }

    public function deleteRegency($id)
    {
        return $this->safeWrite(function () use ($id) {
            return $this->db->delete('md_kokab_indonesia', ['id' => $id]);
        });
    }

    public function insertDistrict(array $payload)
    {
        return $this->safeWrite(function () use ($payload) {
            return $this->db->insert('md_kec_indonesia', $payload);
        });
    }

    public function updateDistrict($id, array $payload)
    {
        return $this->safeWrite(function () use ($id, $payload) {
            return $this->db->where('id', $id)->update('md_kec_indonesia', $payload);
        });
    }

    public function deleteDistrict($id)
    {
        return $this->safeWrite(function () use ($id) {
            return $this->db->delete('md_kec_indonesia', ['id' => $id]);
        });
    }

    public function insertVillage(array $payload)
    {
        return $this->safeWrite(function () use ($payload) {
            return $this->db->insert('md_dusun_indonesia', $payload);
        });
    }

    public function updateVillage($id, array $payload)
    {
        return $this->safeWrite(function () use ($id, $payload) {
            return $this->db->where('id', $id)->update('md_dusun_indonesia', $payload);
        });
    }

    public function deleteVillage($id)
    {
        return $this->safeWrite(function () use ($id) {
            return $this->db->delete('md_dusun_indonesia', ['id' => $id]);
        });
    }

    private function safeWrite(callable $callback)
    {
        $this->db->db_debug = false;
        $result = (bool) $callback();
        $error = $this->db->error();
        $this->db->db_debug = true;

        return $result && empty($error['code']);
    }

    private function mapOptionRows(array $rows)
    {
        return array_map(static function ($row) {
            return [
                'id' => (string) ($row['id'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'text' => (string) ($row['name'] ?? ''),
            ];
        }, $rows);
    }

    private function applyKeyword($column, $keyword)
    {
        $keyword = trim((string) $keyword);
        if ($keyword !== '') {
            $this->db->like($column, $keyword);
        }
    }

    private function applyKeywordGroup(array $columns, $keyword)
    {
        $keyword = trim((string) $keyword);
        if ($keyword === '') {
            return;
        }

        $this->db->group_start();
        foreach ($columns as $index => $column) {
            if ($index === 0) {
                $this->db->like($column, $keyword);
            } else {
                $this->db->or_like($column, $keyword);
            }
        }
        $this->db->group_end();
    }

    private function applyRegionalProvinceJoin($regional)
    {
        $regional = strtoupper(trim((string) $regional));
        if ($regional === '' || !$this->db->table_exists('tb_rfs_myrep_monthly_target')) {
            return;
        }

        $this->db->join(
            'tb_rfs_myrep_monthly_target t',
            'UPPER(t.province_name) = UPPER(p.name) AND UPPER(t.regional_name) = ' . $this->db->escape($regional),
            'inner',
            false
        );
    }

    private function applyRegionalRegencyFilter($regional)
    {
        $regional = strtoupper(trim((string) $regional));
        if ($regional === '' || !$this->db->table_exists('tb_rfs_myrep_monthly_target')) {
            return;
        }

        $this->db->join(
            'tb_rfs_myrep_monthly_target t',
            'UPPER(t.province_name) = UPPER(p.name) AND UPPER(t.regional_name) = ' . $this->db->escape($regional),
            'inner',
            false
        );
    }
}
