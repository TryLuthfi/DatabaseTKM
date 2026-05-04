<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MLogistik_Purchase_Request extends CI_Model
{
    private $columnCache = [];
    private $nodinStageLibrary = [
        'approved_manager_logistik' => ['label' => 'Manager Logistik', 'column' => 'approved_manager_logistik'],
        'approved_purchasing' => ['label' => 'Purchasing', 'column' => 'approved_purchasing'],
        'approved_gm_project' => ['label' => 'General Manager Project', 'column' => 'approved_gm_project'],
        'approved_gm_finance' => ['label' => 'General Manager Finance', 'column' => 'approved_gm_finance'],
        'approved_direktur' => ['label' => 'Direktur', 'column' => 'approved_direktur'],
    ];

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

        $nodin = $this->getLatestNodinByPurchaseRequest((string) ($row['id_purchase_request'] ?? ''));
        $row['nodin_data'] = $nodin;
        $row['nodin_status_label'] = !empty($nodin['workflow_status_label']) ? $nodin['workflow_status_label'] : 'Belum dibuat';
        $row['nodin_status_tone'] = !empty($nodin['workflow_status_tone']) ? $nodin['workflow_status_tone'] : 'waiting';
        $row['is_nodin_fully_approved'] = !empty($nodin['is_fully_approved']);

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

    public function getLatestNodinByPurchaseRequest($idPurchaseRequest)
    {
        if (!$this->relation_exists('tb_logistik_nota_dinas_po')) {
            return null;
        }

        if ($this->relation_exists('tb_logistik_nota_dinas_po_detail')) {
            $row = $this->db->query("
                SELECT h.*
                FROM tb_logistik_nota_dinas_po h
                INNER JOIN tb_logistik_nota_dinas_po_detail d
                    ON d.id_nota_dinas_po = h.id_nota_dinas_po
                INNER JOIN tb_logistik_purchase_request_detail prd
                    ON prd.id_purchase_request_detail = d.id_purchase_request_detail
                WHERE prd.id_purchase_request = ?
                ORDER BY h.tanggal_nota_dinas DESC, h.id_nota_dinas_po DESC
                LIMIT 1
            ", [$idPurchaseRequest])->row_array();

            if (!empty($row)) {
                return $this->decorate_nodin_row($row);
            }
        }

        $row = $this->db
            ->from('tb_logistik_nota_dinas_po')
            ->where('id_purchase_request', $idPurchaseRequest)
            ->order_by('tanggal_nota_dinas', 'DESC')
            ->order_by('id_nota_dinas_po', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        return $this->decorate_nodin_row($row);
    }

    public function getNodinById($idNodin)
    {
        if (!$this->relation_exists('tb_logistik_nota_dinas_po')) {
            return null;
        }

        $row = $this->db
            ->from('tb_logistik_nota_dinas_po')
            ->where('id_nota_dinas_po', $idNodin)
            ->limit(1)
            ->get()
            ->row_array();

        return $this->decorate_nodin_row($row);
    }

    public function getNodinDetailRows($idNodin)
    {
        if (!$this->relation_exists('tb_logistik_nota_dinas_po_detail')) {
            return [];
        }

        $hasIdPabrik = $this->has_column('tb_logistik_nota_dinas_po_detail', 'id_pabrik');
        $hasVendorText = $this->has_column('tb_logistik_nota_dinas_po_detail', 'vendor_pabrik');
        $vendorSelect = $hasIdPabrik
            ? "p.nama_pabrik AS vendor_pabrik"
            : ($hasVendorText ? "d.vendor_pabrik AS vendor_pabrik" : "NULL AS vendor_pabrik");
        $vendorJoin = $hasIdPabrik
            ? "LEFT JOIN tb_master_logistik_pabrik p
                ON p.id_pabrik = d.id_pabrik"
            : '';

        return $this->db->query("
            SELECT
                d.*,
                ki.nama_item,
                ki.satuan_item,
                {$vendorSelect}
            FROM tb_logistik_nota_dinas_po_detail d
            LEFT JOIN tb_master_logistik_kode_item ki
                ON ki.id_kode_item = d.id_kode_item
            {$vendorJoin}
            WHERE d.id_nota_dinas_po = ?
            ORDER BY ki.nama_item ASC, d.id_nota_dinas_po_detail ASC
        ", [$idNodin])->result_array();
    }

    public function getNodinPurchaseRequestIds($idNodin)
    {
        if (!$this->relation_exists('tb_logistik_nota_dinas_po_detail')) {
            return [];
        }

        $rows = $this->db->query("
            SELECT DISTINCT prd.id_purchase_request
            FROM tb_logistik_nota_dinas_po_detail d
            INNER JOIN tb_logistik_purchase_request_detail prd
                ON prd.id_purchase_request_detail = d.id_purchase_request_detail
            WHERE d.id_nota_dinas_po = ?
            ORDER BY prd.id_purchase_request ASC
        ", [$idNodin])->result_array();

        return array_values(array_filter(array_map(static function ($row) {
            return (string) ($row['id_purchase_request'] ?? '');
        }, $rows)));
    }

    public function getNodinSummaryRows()
    {
        if (!$this->relation_exists('tb_logistik_nota_dinas_po') || !$this->relation_exists('tb_logistik_nota_dinas_po_detail')) {
            return [];
        }

        $rows = $this->db->query("
            SELECT
                h.*,
                COUNT(DISTINCT d.id_nota_dinas_po_detail) AS total_item,
                COUNT(DISTINCT prd.id_purchase_request) AS total_pr,
                SUM(COALESCE(d.qty_po_nodin, 0)) AS total_qty_nodin,
                SUM(COALESCE(d.qty_po_nodin, 0) * COALESCE(d.harga_satuan, 0)) AS total_nominal_nodin,
                GROUP_CONCAT(DISTINCT pr.nomor_purchase_request ORDER BY pr.nomor_purchase_request SEPARATOR ', ') AS nomor_purchase_request_refs,
                GROUP_CONCAT(DISTINCT COALESCE(pr.nama_project, pr.id_project) ORDER BY COALESCE(pr.nama_project, pr.id_project) SEPARATOR ', ') AS nama_project_refs
            FROM tb_logistik_nota_dinas_po h
            LEFT JOIN tb_logistik_nota_dinas_po_detail d
                ON d.id_nota_dinas_po = h.id_nota_dinas_po
            LEFT JOIN tb_logistik_purchase_request_detail prd
                ON prd.id_purchase_request_detail = d.id_purchase_request_detail
            LEFT JOIN tb_logistik_purchase_request pr
                ON pr.id_purchase_request = prd.id_purchase_request
            GROUP BY h.id_nota_dinas_po
            ORDER BY h.tanggal_nota_dinas DESC, h.id_nota_dinas_po DESC
        ")->result_array();

        return array_map([$this, 'decorate_nodin_row'], $rows);
    }

    public function saveNodin($header, $details, $existingNodinId = null)
    {
        if (!$this->relation_exists('tb_logistik_nota_dinas_po') || !$this->relation_exists('tb_logistik_nota_dinas_po_detail')) {
            return false;
        }

        $header = $this->filterPayloadByColumns('tb_logistik_nota_dinas_po', $header);
        $supportsIdPabrik = $this->has_column('tb_logistik_nota_dinas_po_detail', 'id_pabrik');
        $supportsVendorText = $this->has_column('tb_logistik_nota_dinas_po_detail', 'vendor_pabrik');
        $normalizedDetails = [];

        foreach ($details as $detail) {
            if (!$supportsIdPabrik) {
                unset($detail['id_pabrik']);
            }

            if (!$supportsVendorText) {
                unset($detail['vendor_pabrik']);
            }

            $normalizedDetails[] = $this->filterPayloadByColumns('tb_logistik_nota_dinas_po_detail', $detail);
        }

        $this->db->trans_start();

        if ($existingNodinId) {
            $header['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id_nota_dinas_po', $existingNodinId)->update('tb_logistik_nota_dinas_po', $header);
            $this->db->delete('tb_logistik_nota_dinas_po_detail', ['id_nota_dinas_po' => $existingNodinId]);
            $nodinId = $existingNodinId;
        } else {
            $this->db->insert('tb_logistik_nota_dinas_po', $header);
            $nodinId = (string) $header['id_nota_dinas_po'];
        }

        if (!empty($normalizedDetails)) {
            $this->db->insert_batch('tb_logistik_nota_dinas_po_detail', $normalizedDetails);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $nodinId : false;
    }

    public function approveNodin($idNodin, $column)
    {
        if (!$this->relation_exists('tb_logistik_nota_dinas_po') || !$this->has_column('tb_logistik_nota_dinas_po', $column)) {
            return false;
        }

        $payload = [
            $column => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db
            ->where('id_nota_dinas_po', $idNodin)
            ->update('tb_logistik_nota_dinas_po', $payload);
    }

    public function deleteNodin($idNodin)
    {
        if (!$this->relation_exists('tb_logistik_nota_dinas_po') || !$this->relation_exists('tb_logistik_nota_dinas_po_detail')) {
            return false;
        }

        $this->db->trans_start();
        $this->db->delete('tb_logistik_nota_dinas_po_detail', ['id_nota_dinas_po' => $idNodin]);
        $this->db->delete('tb_logistik_nota_dinas_po', ['id_nota_dinas_po' => $idNodin]);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function get_nodin_workflow()
    {
        if (!$this->relation_exists('tb_logistik_nota_dinas_po')) {
            return [];
        }

        $workflow = [];
        foreach ($this->nodinStageLibrary as $stageKey => $stage) {
            if ($this->has_column('tb_logistik_nota_dinas_po', $stageKey)) {
                $workflow[] = $stage;
            }
        }

        return $workflow;
    }

    public function decorate_nodin_row($row)
    {
        if (empty($row)) {
            return null;
        }

        $workflow = $this->get_nodin_workflow();
        $progress = $this->resolve_workflow_progress($row, $workflow);

        $row['workflow_stages'] = $workflow;
        $row['workflow_progress'] = $progress['completed'];
        $row['workflow_total'] = count($workflow);
        $row['workflow_current_label'] = $progress['current_label'];
        $row['workflow_status_label'] = $progress['status_label'];
        $row['workflow_status_tone'] = $progress['status_tone'];
        $row['is_fully_approved'] = !empty($workflow) && $progress['completed'] >= count($workflow);

        return $row;
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
            if (!$this->relation_exists($table)) {
                $this->columnCache[$key] = false;
                return $this->columnCache[$key];
            }

            $fields = $this->db->list_fields($table);
            $this->columnCache[$key] = in_array($column, $fields, true);
        }

        return $this->columnCache[$key];
    }

    private function get_table_columns($table)
    {
        if (!$this->relation_exists($table)) {
            return [];
        }

        return $this->db->list_fields($table);
    }

    private function filterPayloadByColumns($table, array $payload)
    {
        $fields = $this->get_table_columns($table);
        if (empty($fields)) {
            return $payload;
        }

        return array_intersect_key($payload, array_flip($fields));
    }

    private function relation_exists($name)
    {
        $row = $this->db->query("SHOW FULL TABLES LIKE ?", [$name])->row_array();
        return !empty($row);
    }
}
