<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBudget_Cashflow extends CI_Model
{
    private function applyHeaderFilters(array $filters = [], $ignoreField = '')
    {
        $year = isset($filters['year']) ? (int) $filters['year'] : 0;
        $month = isset($filters['month']) ? (int) $filters['month'] : 0;
        $startDate = trim((string) ($filters['start_date'] ?? ''));
        $endDate = trim((string) ($filters['end_date'] ?? ''));
        $projectName = trim((string) ($filters['project_name'] ?? ''));
        $bowheerId = (int) ($filters['id_bowheer'] ?? 0);
        $regional = trim((string) ($filters['regional'] ?? ''));
        $kota = trim((string) ($filters['kota'] ?? ''));
        $picProject = trim((string) ($filters['pic_project'] ?? ''));

        if ($year > 0 && $ignoreField !== 'year') {
            $this->db->where('YEAR(h.tanggal_cashflow)', $year);
        }

        if ($month > 0 && $ignoreField !== 'month') {
            $this->db->where('MONTH(h.tanggal_cashflow)', $month);
        }

        if ($startDate !== '' && $ignoreField !== 'start_date') {
            $this->db->where('DATE(h.tanggal_cashflow) >=', $startDate);
        }

        if ($endDate !== '' && $ignoreField !== 'end_date') {
            $this->db->where('DATE(h.tanggal_cashflow) <=', $endDate);
        }

        if ($projectName !== '' && $ignoreField !== 'project_name') {
            $this->db->where('h.project_name', $projectName);
        }

        if ($bowheerId > 0 && $ignoreField !== 'id_bowheer') {
            $this->db->where('h.id_bowheer', $bowheerId);
        }

        if ($regional !== '' && $ignoreField !== 'regional') {
            $this->db->where('h.regional', $regional);
        }

        if ($kota !== '' && $ignoreField !== 'kota') {
            $this->db->where('h.kota', $kota);
        }

        if ($picProject !== '' && $ignoreField !== 'pic_project') {
            $this->db->where('h.pic_project', $picProject);
        }
    }

    public function getBowheers()
    {
        return $this->db
            ->order_by('nama_bowheer', 'ASC')
            ->get('tb_master_bowheer')
            ->result_array();
    }

    public function getItems()
    {
        return $this->db
            ->order_by('item_name', 'ASC')
            ->get_where('tb_budget_items', ['is_active' => 1])
            ->result_array();
    }

    public function getActivePicUsers()
    {
        if ($this->db->table_exists('tb_budget_master_pic')) {
            $rows = (array) $this->db
                ->distinct()
                ->select('nama_user AS value, nama_user AS label')
                ->from('tb_budget_master_pic')
                ->where('nama_user IS NOT NULL', null, false)
                ->where('TRIM(nama_user) !=', '')
                ->order_by('nama_user', 'ASC')
                ->get()
                ->result_array();
            if (!empty($rows)) {
                return $rows;
            }
        }

        // fallback transisi lama jika master PIC budget belum terisi
        if (!$this->db->table_exists('tb_master_user_new')) {
            return [];
        }

        return (array) $this->db
            ->distinct()
            ->select('nama_karyawan AS value, nama_karyawan AS label')
            ->from('tb_master_user_new')
            ->where('status_user', 'ACTIVE')
            ->where('nama_karyawan IS NOT NULL', null, false)
            ->where('TRIM(nama_karyawan) !=', '')
            ->order_by('nama_karyawan', 'ASC')
            ->get()
            ->result_array();
    }

    public function findActivePicUserByName($namaUser)
    {
        $namaUser = trim((string) $namaUser);
        if ($namaUser === '') {
            return [];
        }

        if ($this->db->table_exists('tb_budget_master_pic')) {
            $totalPic = (int) $this->db->count_all('tb_budget_master_pic');
            $row = (array) $this->db
                ->select('nama_user')
                ->from('tb_budget_master_pic')
                ->where('LOWER(TRIM(nama_user)) =', strtolower($namaUser))
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($row)) {
                return ['nama_user' => (string) ($row['nama_user'] ?? '')];
            }

            // Jika master PIC budget sudah terisi, validasi hanya dari master PIC
            if ($totalPic > 0) {
                return [];
            }
        }

        // fallback transisi lama jika master PIC budget belum terisi
        if (!$this->db->table_exists('tb_master_user_new')) {
            return [];
        }

        return (array) $this->db
            ->select('nama_karyawan AS nama_user')
            ->from('tb_master_user_new')
            ->where('nama_karyawan', $namaUser)
            ->where('status_user', 'ACTIVE')
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function getHeaders($year, $month = 0, $startDate = '', $endDate = '')
    {
        return $this->getHeaderSummaries([
            'year' => (int) $year,
            'month' => (int) $month,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function getHeaderSummaries(array $filters = [], array $headerIds = [])
    {
        $this->db->select('
            h.*,
            b.nama_bowheer,
            COUNT(d.id_cashflow_detail) AS total_items,
            COALESCE(SUM(CASE WHEN d.direction = "DEBIT" THEN d.nominal ELSE 0 END), 0) AS total_debit,
            COALESCE(SUM(CASE WHEN d.direction = "KREDIT" THEN d.nominal ELSE 0 END), 0) AS total_kredit
        ');
        $this->db->from('tb_budget_cashflow_header h');
        $this->db->join('tb_master_bowheer b', 'b.id_bowheer = h.id_bowheer', 'left');
        $this->db->join('tb_budget_cashflow_detail d', 'd.id_cashflow_header = h.id_cashflow_header', 'left');
        $this->applyHeaderFilters($filters);
        if (!empty($headerIds)) {
            $headerIds = array_values(array_unique(array_filter(array_map('intval', $headerIds))));
            if (!empty($headerIds)) {
                $this->db->where_in('h.id_cashflow_header', $headerIds);
            }
        }
        $this->db->group_by('h.id_cashflow_header');
        $this->db->order_by('h.tanggal_cashflow', 'DESC');
        $this->db->order_by('h.nomor_tec', 'DESC');

        return $this->db->get()->result_array();
    }

    public function getReportFilterOptions(array $filters = [])
    {
        return [
            'projects' => $this->getDistinctHeaderOptions('h.project_name', 'project_name', $filters),
            'bowheers' => $this->getDistinctHeaderOptions('h.id_bowheer', 'id_bowheer', $filters, 'b.nama_bowheer'),
            'regionals' => $this->getDistinctHeaderOptions('h.regional', 'regional', $filters),
            'cities' => $this->getDistinctHeaderOptions('h.kota', 'kota', $filters),
            'pics' => $this->getDistinctHeaderOptions('h.pic_project', 'pic_project', $filters),
        ];
    }

    private function getDistinctHeaderOptions($valueField, $fieldKey, array $filters = [], $labelField = '')
    {
        $labelField = $labelField !== '' ? $labelField : $valueField;

        $this->db->distinct();
        $this->db->select($valueField . ' AS value, ' . $labelField . ' AS label', false);
        $this->db->from('tb_budget_cashflow_header h');
        $this->db->join('tb_master_bowheer b', 'b.id_bowheer = h.id_bowheer', 'left');
        $this->applyHeaderFilters($filters, $fieldKey);
        $this->db->where($valueField . ' IS NOT NULL', null, false);
        $this->db->where('TRIM(COALESCE(' . $valueField . ', "")) !=', '');
        $this->db->order_by('label', 'ASC');

        return $this->db->get()->result_array();
    }

    public function getHeaderDetails($headerId)
    {
        return $this->db
            ->select('d.*, i.item_code, i.item_name')
            ->from('tb_budget_cashflow_detail d')
            ->join('tb_budget_items i', 'i.id_budget_item = d.id_budget_item')
            ->where('d.id_cashflow_header', (int) $headerId)
            ->order_by('d.id_cashflow_detail', 'ASC')
            ->get()
            ->result_array();
    }

    public function getHeaderById($headerId)
    {
        return $this->db
            ->select('h.*, b.nama_bowheer')
            ->from('tb_budget_cashflow_header h')
            ->join('tb_master_bowheer b', 'b.id_bowheer = h.id_bowheer', 'left')
            ->where('h.id_cashflow_header', (int) $headerId)
            ->get()
            ->row_array();
    }

    public function isDuplicateHeader($nomorTec, $projectName, $excludeHeaderId = 0)
    {
        $this->db->from('tb_budget_cashflow_header');
        $this->db->where('nomor_tec', trim($nomorTec));
        $this->db->where('project_name', trim($projectName));

        if ((int) $excludeHeaderId > 0) {
            $this->db->where('id_cashflow_header !=', (int) $excludeHeaderId);
        }

        return $this->db->count_all_results() > 0;
    }

    public function getBudgetSnapshotByItems($year, $month, array $itemIds)
    {
        if (empty($itemIds)) {
            return [];
        }

        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));

        $this->db->select("
            i.id_budget_item,
            i.item_code,
            i.item_name,
            COALESCE(a.annual_budget, 0) AS annual_budget,
            COALESCE(m.monthly_budget, 0) AS monthly_budget,
            COALESCE(r.real_annual, 0) AS real_annual,
            COALESCE(rm.real_monthly, 0) AS real_monthly
        ");
        $this->db->from('tb_budget_items i');
        $this->db->join(
            'tb_budget_annual a',
            'a.id_budget_item = i.id_budget_item AND a.budget_year = ' . (int) $year,
            'left',
            false
        );
        $this->db->join(
            'tb_budget_monthly m',
            'm.id_budget_annual = a.id_budget_annual AND m.month_no = ' . (int) $month,
            'left',
            false
        );
        $this->db->join(
            "(SELECT d.id_budget_item, SUM(CASE WHEN d.direction = 'DEBIT' THEN d.nominal ELSE -d.nominal END) AS real_annual
              FROM tb_budget_cashflow_detail d
              JOIN tb_budget_cashflow_header h ON h.id_cashflow_header = d.id_cashflow_header
              WHERE YEAR(h.tanggal_cashflow) = " . (int) $year . "
              GROUP BY d.id_budget_item) r",
            'r.id_budget_item = i.id_budget_item',
            'left',
            false
        );
        $this->db->join(
            "(SELECT d.id_budget_item, SUM(CASE WHEN d.direction = 'DEBIT' THEN d.nominal ELSE -d.nominal END) AS real_monthly
              FROM tb_budget_cashflow_detail d
              JOIN tb_budget_cashflow_header h ON h.id_cashflow_header = d.id_cashflow_header
              WHERE YEAR(h.tanggal_cashflow) = " . (int) $year . "
                AND MONTH(h.tanggal_cashflow) = " . (int) $month . "
              GROUP BY d.id_budget_item) rm",
            'rm.id_budget_item = i.id_budget_item',
            'left',
            false
        );
        $this->db->where_in('i.id_budget_item', $itemIds);

        $rows = $this->db->get()->result_array();
        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(int) $row['id_budget_item']] = $row;
        }

        return $mapped;
    }

    public function saveManualCashflow(array $header, array $details)
    {
        $this->db->trans_start();

        $this->db->insert('tb_budget_cashflow_header', $header);
        $headerId = (int) $this->db->insert_id();

        foreach ($details as $detail) {
            $detail['id_cashflow_header'] = $headerId;
            $this->db->insert('tb_budget_cashflow_detail', $detail);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function updateManualCashflow($headerId, array $header, array $details)
    {
        $this->db->trans_start();

        $this->db
            ->where('id_cashflow_header', (int) $headerId)
            ->update('tb_budget_cashflow_header', $header);

        $this->db->delete('tb_budget_cashflow_detail', ['id_cashflow_header' => (int) $headerId]);

        foreach ($details as $detail) {
            $detail['id_cashflow_header'] = (int) $headerId;
            $this->db->insert('tb_budget_cashflow_detail', $detail);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function deleteHeader($headerId)
    {
        $this->db->delete('tb_budget_cashflow_header', ['id_cashflow_header' => (int) $headerId]);
        return $this->db->affected_rows() > 0;
    }

    public function logImport($fileName, $totalRows, $successRows, $failedRows, $notes, $userId)
    {
        $this->db->insert('tb_budget_import_log', [
            'file_name' => $fileName,
            'total_rows' => (int) $totalRows,
            'success_rows' => (int) $successRows,
            'failed_rows' => (int) $failedRows,
            'notes' => $notes,
            'uploaded_by' => (int) $userId,
        ]);
    }

    public function findItemByCode($itemCode)
    {
        return $this->db
            ->get_where('tb_budget_items', ['item_code' => trim($itemCode)])
            ->row_array();
    }
}

