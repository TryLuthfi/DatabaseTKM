<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MLogistik_Purchase_Request extends CI_Model
{
    private $columnCache = [];

    public function get_all_purchase_request($tipe)
    {
        $where = ($tipe == 'ho') ? 'WHERE tmu.lokasi_user = "HO"' : 'WHERE tmu.lokasi_user != "HO"';
        $data = $this->db->query("
            SELECT
                tlp.*,
                tmb.nama_bowheer as nama_project,
                tmu.nama_user as nama_pembuat,
                tmu.lokasi_user as lokasi_user_pembuat,
                tmlg.kota_lokasi_gudang,
                tlp.nama_project as nama_projects
            FROM
                tb_logistik_purchase_request tlp
            LEFT JOIN tb_master_bowheer tmb ON
                tmb.nama_bowheer = tlp.id_project
            LEFT JOIN tb_master_user tmu ON
                tmu.id_user = tlp.pembuat
            LEFT JOIN tb_master_logistik_lokasi_gudang tmlg ON
                tmlg.id_lokasi_gudang = tlp.lokasi_project
            $where
        ")->result_array();
        return $data;
    }

    public function get_detail_purchase_request($id)
    {
        $data = $this->db->query("
            SELECT
                tlpr.*,
                tlprd.*,
                tmlki.nama_item,
                tmlki.satuan_item,
                tmu.nama_user as nama_pembuat,
                tmu.lokasi_user as lokasi_user_pembuat,
                tmlg.kota_lokasi_gudang
            FROM
                tb_logistik_purchase_request_detail tlprd
            LEFT JOIN tb_logistik_purchase_request tlpr on
                tlprd.id_purchase_request = tlpr.id_purchase_request
            LEFT JOIN tb_master_logistik_kode_item tmlki ON
                tlprd.id_kode_item = tmlki.id_kode_item
            LEFT JOIN tb_master_user tmu ON
                tmu.id_user = tlpr.pembuat
            LEFT JOIN tb_master_logistik_lokasi_gudang tmlg ON
                tmlg.id_lokasi_gudang = tlpr.lokasi_project
            WHERE
                tlpr.id_purchase_request = '" . $id . "'
        ")->result_array();
        return $data;
    }

    public function get_all_gudang()
    {
        $data = $this->db->query("
            SELECT
                *
            FROM
                tb_master_logistik_lokasi_gudang
        ")->result_array();
        return $data;
    }

    public function update_purchase_request_detail($id, $data)
    {
        $this->db->where('id_purchase_request_detail', $id);
        return $this->db->update('tb_logistik_purchase_request_detail', $data);
    }

    public function get_material_options($projectItem, $idLokasiGudang = null)
    {
        $stockSelect = '0';
        if (!empty($idLokasiGudang)) {
            $idLokasiGudang = (int) $idLokasiGudang;
            $stockSelect = "COALESCE((
                SELECT SUM(
                    CASE
                        WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok
                        WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok
                        ELSE 0
                    END
                )
                FROM tb_logistik_stok ls
                LEFT JOIN tb_master_logistik_sumber_material sm
                    ON sm.id_sumber_material = ls.id_sumber_material
                WHERE ls.id_kode_item = tmlki.id_kode_item
                    AND ls.id_lokasi_gudang = " . $idLokasiGudang . "
            ), 0)";
        }

        $sql = "
            SELECT
                tmlki.id_kode_item,
                tmlki.kategori_item,
                tmlki.nama_item,
                tmlki.satuan_item,
                tmlki.project_item,
                tmlki.id_bowheer_pemilik_item,
                tmb.nama_bowheer AS nama_kepemilikan_item,
                {$stockSelect} AS stok_area_tersedia
            FROM tb_master_logistik_kode_item tmlki
            LEFT JOIN tb_master_bowheer tmb
                ON tmlki.id_bowheer_pemilik_item = tmb.id_bowheer
            WHERE tmlki.project_item = ?
            ORDER BY tmlki.kategori_item ASC, tmlki.nama_item ASC
        ";

        return $this->db->query($sql, [$projectItem])->result_array();
    }

    public function decorate_purchase_request_rows($rows)
    {
        return array_map([$this, 'decorate_purchase_request_row'], $rows);
    }

    public function decorate_purchase_request_row($row)
    {
        if (empty($row)) {
            return $row;
        }

        $origin = $this->resolve_origin($row);
        $workflow = $this->build_workflow($origin);
        $progress = $this->resolve_workflow_progress($row, $workflow);

        $row['origin_pr'] = $origin;
        $row['origin_pr_label'] = $origin === 'AREA' ? 'PR Area' : 'PR Planning';
        $row['workflow_stages'] = $workflow;
        $row['workflow_progress'] = $progress['completed'];
        $row['workflow_total'] = count($workflow);
        $row['workflow_current_label'] = $progress['current_label'];
        $row['workflow_status_label'] = $progress['status_label'];
        $row['workflow_status_tone'] = $progress['status_tone'];
        $row['is_fully_approved'] = $progress['completed'] >= count($workflow);
        $row['hardcopy_uploaded'] = !empty($row['hardcopy_file']);

        return $row;
    }

    public function resolve_available_approval_columns()
    {
        $candidates = [
            'approved_sm',
            'approved_rpm',
            'approved_planning',
            'approved_manager_konstruksi',
            'approved_finance',
            'approved_gm',
            'approved_manager_logistik',
            'approved_direktur',
        ];

        $available = [];
        foreach ($candidates as $column) {
            if ($this->has_column('tb_logistik_purchase_request', $column)) {
                $available[] = $column;
            }
        }

        return $available;
    }

    private function resolve_origin($row)
    {
        if ($this->has_column('tb_logistik_purchase_request', 'asal_pr') && !empty($row['asal_pr'])) {
            return strtoupper((string) $row['asal_pr']) === 'AREA' ? 'AREA' : 'PLANNING';
        }

        return strtoupper((string) ($row['lokasi_user_pembuat'] ?? '')) === 'HO' ? 'PLANNING' : 'AREA';
    }

    private function build_workflow($origin)
    {
        $available = $this->resolve_available_approval_columns();

        $stageLibrary = [
            'approved_sm' => ['label' => 'SM', 'column' => 'approved_sm'],
            'approved_rpm' => ['label' => 'RPM', 'column' => 'approved_rpm'],
            'approved_planning' => ['label' => 'Planning', 'column' => 'approved_planning'],
            'approved_manager_konstruksi' => ['label' => 'Manager Konstruksi', 'column' => 'approved_manager_konstruksi'],
            'approved_finance' => ['label' => 'Finance', 'column' => 'approved_finance'],
            'approved_gm' => ['label' => 'GM', 'column' => 'approved_gm'],
            'approved_manager_logistik' => ['label' => 'Manager Logistik', 'column' => 'approved_manager_logistik'],
            'approved_direktur' => ['label' => 'Direktur', 'column' => 'approved_direktur'],
        ];

        $desired = $origin === 'AREA'
            ? ['approved_sm', 'approved_rpm', 'approved_planning', 'approved_manager_konstruksi', 'approved_finance', 'approved_gm', 'approved_manager_logistik']
            : ['approved_planning', 'approved_manager_konstruksi', 'approved_finance', 'approved_gm', 'approved_manager_logistik'];

        $workflow = [];
        foreach ($desired as $stageKey) {
            if (in_array($stageKey, $available, true)) {
                $workflow[] = $stageLibrary[$stageKey];
            }
        }

        if (empty($workflow)) {
            $fallback = $origin === 'AREA'
                ? ['approved_planning', 'approved_finance', 'approved_direktur']
                : ['approved_planning', 'approved_finance', 'approved_direktur'];

            foreach ($fallback as $stageKey) {
                if (isset($stageLibrary[$stageKey]) && in_array($stageKey, $available, true)) {
                    $workflow[] = $stageLibrary[$stageKey];
                }
            }
        }

        return $workflow;
    }

    private function resolve_workflow_progress($row, $workflow)
    {
        $completed = 0;
        $currentLabel = 'Belum diproses';

        foreach ($workflow as $stage) {
            $isApproved = isset($row[$stage['column']]) && (int) $row[$stage['column']] === 1;
            if ($isApproved) {
                $completed++;
                continue;
            }

            $currentLabel = 'Waiting ' . $stage['label'];
            return [
                'completed' => $completed,
                'current_label' => $currentLabel,
                'status_label' => $currentLabel,
                'status_tone' => 'waiting',
            ];
        }

        if (!empty($workflow)) {
            return [
                'completed' => $completed,
                'current_label' => 'Approved',
                'status_label' => 'Approved',
                'status_tone' => 'approved',
            ];
        }

        return [
            'completed' => 0,
            'current_label' => 'Flow belum terdefinisi',
            'status_label' => 'Flow belum terdefinisi',
            'status_tone' => 'waiting',
        ];
    }

    private function has_column($table, $column)
    {
        $key = $table . '.' . $column;
        if (!array_key_exists($key, $this->columnCache)) {
            $fields = $this->db->list_fields($table);
            $this->columnCache[$key] = in_array($column, $fields, true);
        }

        return $this->columnCache[$key];
    }
}
