<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MLogistik_Pesanan_Pabrik extends CI_Model
{
    public function getMasterPabrikActive()
    {
        return $this->db->query("
            SELECT id_pabrik, nama_pabrik, lokasi_pabrik, jenis_pabrik, status_pabrik
            FROM tb_master_logistik_pabrik
            WHERE status_pabrik = 'ACTIVE'
            ORDER BY nama_pabrik ASC
        ")->result_array();
    }

    public function getApprovedPurchaseRequests()
    {
        $approvalConditions = ["COALESCE(pr.approved_planning, 0) = 1"];

        if ($this->fieldExists('tb_logistik_purchase_request', 'approved_finance')) {
            $approvalConditions[] = "COALESCE(pr.approved_finance, 0) = 1";
        }

        if ($this->fieldExists('tb_logistik_purchase_request', 'approved_manager_logistik')) {
            $approvalConditions[] = "COALESCE(pr.approved_manager_logistik, 0) = 1";
        } elseif ($this->fieldExists('tb_logistik_purchase_request', 'approved_direktur')) {
            $approvalConditions[] = "COALESCE(pr.approved_direktur, 0) = 1";
        }

        $whereApproval = implode(' AND ', $approvalConditions);

        $supportsPrDetailRelation = $this->fieldExists('tb_logistik_pesanan_pabrik_detail', 'id_purchase_request_detail');
        $supportsVolumePlanning = $this->fieldExists('tb_logistik_purchase_request_detail', 'volume_planning');

        $volumeExpression = $supportsVolumePlanning
            ? "COALESCE(NULLIF(prd.volume_planning, 0), NULLIF(prd.qty_planning, 0), prd.qty_request, 0)"
            : "COALESCE(NULLIF(prd.qty_planning, 0), prd.qty_request, 0)";

        $allocationJoin = $supportsPrDetailRelation
            ? "LEFT JOIN (
                SELECT
                    id_purchase_request_detail,
                    SUM(COALESCE(qty_item, 0)) AS qty_po_teralokasi
                FROM tb_logistik_pesanan_pabrik_detail
                WHERE id_purchase_request_detail IS NOT NULL
                    AND id_purchase_request_detail <> ''
                GROUP BY id_purchase_request_detail
            ) alloc ON alloc.id_purchase_request_detail = prd.id_purchase_request_detail"
            : "";

        $outstandingSelect = $supportsPrDetailRelation
            ? "SUM(GREATEST({$volumeExpression} - COALESCE(alloc.qty_po_teralokasi, 0), 0)) AS total_qty_outstanding_pr"
            : "SUM({$volumeExpression}) AS total_qty_outstanding_pr";

        return $this->db->query("
            SELECT
                pr.id_purchase_request,
                pr.nomor_purchase_request,
                pr.tanggal_pembuatan,
                pr.nama_project,
                pr.id_project,
                lg.kota_lokasi_gudang,
                mu.nama_user,
                {$outstandingSelect}
            FROM tb_logistik_purchase_request pr
            LEFT JOIN tb_master_logistik_lokasi_gudang lg
                ON lg.id_lokasi_gudang = pr.lokasi_project
            LEFT JOIN tb_master_user mu
                ON mu.id_user = pr.pembuat
            LEFT JOIN tb_logistik_purchase_request_detail prd
                ON prd.id_purchase_request = pr.id_purchase_request
            {$allocationJoin}
            WHERE {$whereApproval}
            GROUP BY
                pr.id_purchase_request,
                pr.nomor_purchase_request,
                pr.tanggal_pembuatan,
                pr.nama_project,
                pr.id_project,
                lg.kota_lokasi_gudang,
                mu.nama_user
            HAVING total_qty_outstanding_pr > 0
            ORDER BY pr.tanggal_pembuatan DESC, pr.nomor_purchase_request DESC
        ")->result_array();
    }

    public function getApprovedPurchaseRequestItems($idPurchaseRequest)
    {
        $supportsPrDetailRelation = $this->fieldExists('tb_logistik_pesanan_pabrik_detail', 'id_purchase_request_detail');
        $allocationSelect = $supportsPrDetailRelation
            ? "COALESCE(alloc.qty_po_teralokasi, 0) AS qty_po_teralokasi,"
            : "0 AS qty_po_teralokasi,";
        $allocationJoin = $supportsPrDetailRelation
            ? "LEFT JOIN (
                SELECT
                    id_purchase_request_detail,
                    SUM(COALESCE(qty_item, 0)) AS qty_po_teralokasi
                FROM tb_logistik_pesanan_pabrik_detail
                WHERE id_purchase_request_detail IS NOT NULL
                    AND id_purchase_request_detail <> ''
                GROUP BY id_purchase_request_detail
            ) alloc ON alloc.id_purchase_request_detail = d.id_purchase_request_detail"
            : "";

        $items = $this->db->query("
            SELECT
                d.id_purchase_request_detail,
                d.id_purchase_request,
                d.id_kode_item,
                d.boq,
                d.stok_area,
                d.qty_request,
                d.qty_planning,
                d.keterangan,
                d.keterangan_planning,
                ki.nama_item,
                ki.satuan_item,
                {$allocationSelect}
                pr.nomor_purchase_request
            FROM tb_logistik_purchase_request_detail d
            LEFT JOIN tb_logistik_purchase_request pr
                ON pr.id_purchase_request = d.id_purchase_request
            LEFT JOIN tb_master_logistik_kode_item ki
                ON ki.id_kode_item = d.id_kode_item
            {$allocationJoin}
            WHERE d.id_purchase_request = ?
            ORDER BY ki.nama_item ASC
        ", [$idPurchaseRequest])->result_array();

        $supportsVolumePlanning = $this->fieldExists('tb_logistik_purchase_request_detail', 'volume_planning');
        foreach ($items as &$item) {
            $item['volume_planning_final'] = $supportsVolumePlanning && isset($item['volume_planning']) && $item['volume_planning'] !== null && (float) $item['volume_planning'] > 0
                ? (float) $item['volume_planning']
                : ((float) $item['qty_planning'] > 0 ? (float) $item['qty_planning'] : (float) $item['qty_request']);

            $item['qty_po_teralokasi'] = (float) ($item['qty_po_teralokasi'] ?? 0);
            $item['qty_outstanding_pr'] = max($item['volume_planning_final'] - $item['qty_po_teralokasi'], 0);
            $item['is_selectable'] = $item['qty_outstanding_pr'] > 0;
        }
        unset($item);

        return array_values(array_filter($items, function ($item) {
            return !empty($item['is_selectable']);
        }));
    }

    public function createPoFromApprovedPr($header, $details)
    {
        $this->db->trans_start();

        $this->db->insert('tb_logistik_pesanan_pabrik', $header);

        if (!empty($details)) {
            $this->db->insert_batch('tb_logistik_pesanan_pabrik_detail', $details);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function getPoSummaryRows()
    {
        if ($this->relationExists('v_logistik_po_monitor')) {
            return $this->db->query("
                SELECT
                    id_pesanan_pabrik,
                    id_purchase_request,
                    nomor_purchase_request,
                    id_pabrik,
                    nomor_po_pabrik,
                    tanggal_po_pabrik,
                    purchase_order_document,
                    status_po,
                    id_user,
                    nama_pabrik,
                    nama_user,
                    COUNT(DISTINCT id_pesanan_pabrik_detail) AS total_item,
                    COALESCE(SUM(qty_po), 0) AS total_qty_po,
                    COALESCE(SUM(qty_terkirim), 0) AS total_qty_terkirim,
                    COALESCE(SUM(outstanding_pengiriman), 0) AS total_outstanding,
                    COALESCE(SUM(total_nominal_detail), 0) AS total_nominal_po
                FROM v_logistik_po_monitor
                GROUP BY
                    id_pesanan_pabrik,
                    id_purchase_request,
                    nomor_purchase_request,
                    id_pabrik,
                    nomor_po_pabrik,
                    tanggal_po_pabrik,
                    purchase_order_document,
                    status_po,
                    id_user,
                    nama_pabrik,
                    nama_user
                ORDER BY tanggal_po_pabrik DESC, nomor_po_pabrik DESC
            ")->result_array();
        }

        return $this->db->query("
            SELECT
                p.id_pesanan_pabrik,
                NULL AS id_purchase_request,
                NULL AS nomor_purchase_request,
                p.id_pabrik,
                p.nomor_po_pabrik,
                p.tanggal_po_pabrik,
                p.purchase_order_document,
                'APPROVED' AS status_po,
                p.id_user,
                mp.nama_pabrik,
                mu.nama_user,
                COUNT(DISTINCT d.id_pesanan_pabrik_detail) AS total_item,
                COALESCE(SUM(d.qty_item), 0) AS total_qty_po,
                COALESCE(SUM(gd.qty_item), 0) AS total_qty_terkirim,
                (COALESCE(SUM(d.qty_item), 0) - COALESCE(SUM(gd.qty_item), 0)) AS total_outstanding,
                COALESCE(SUM(COALESCE(d.harga_item, 0) * COALESCE(d.qty_item, 0)), 0) AS total_nominal_po
            FROM tb_logistik_pesanan_pabrik p
            LEFT JOIN tb_master_logistik_pabrik mp
                ON mp.id_pabrik = p.id_pabrik
            LEFT JOIN tb_master_user mu
                ON mu.id_user = p.id_user
            LEFT JOIN tb_logistik_pesanan_pabrik_detail d
                ON d.id_pesanan_pabrik = p.id_pesanan_pabrik
            LEFT JOIN tb_logistik_pengiriman_pabrik_detail gd
                ON gd.id_pesanan_pabrik_detail = d.id_pesanan_pabrik_detail
            GROUP BY
                p.id_pesanan_pabrik,
                p.id_pabrik,
                p.nomor_po_pabrik,
                p.tanggal_po_pabrik,
                p.purchase_order_document,
                p.id_user,
                mp.nama_pabrik,
                mu.nama_user
            ORDER BY p.tanggal_po_pabrik DESC, p.nomor_po_pabrik DESC
        ")->result_array();
    }

    public function getPoDashboardStats()
    {
        $rows = $this->getPoSummaryRows();
        $stats = [
            'total_po' => count($rows),
            'total_item' => 0,
            'total_qty_po' => 0,
            'total_qty_terkirim' => 0,
            'total_outstanding' => 0,
            'total_nominal_po' => 0,
            'completed_po' => 0,
            'partial_po' => 0,
        ];

        foreach ($rows as $row) {
            $stats['total_item'] += (int) ($row['total_item'] ?? 0);
            $stats['total_qty_po'] += (float) ($row['total_qty_po'] ?? 0);
            $stats['total_qty_terkirim'] += (float) ($row['total_qty_terkirim'] ?? 0);
            $stats['total_outstanding'] += (float) ($row['total_outstanding'] ?? 0);
            $stats['total_nominal_po'] += (float) ($row['total_nominal_po'] ?? 0);

            if ((float) ($row['total_outstanding'] ?? 0) <= 0) {
                $stats['completed_po']++;
            } elseif ((float) ($row['total_qty_terkirim'] ?? 0) > 0) {
                $stats['partial_po']++;
            }
        }

        return $stats;
    }

    public function getPoHeaderByNomor($nomorPo)
    {
        $rows = $this->getPoSummaryRows();
        foreach ($rows as $row) {
            if ((string) $row['nomor_po_pabrik'] === (string) $nomorPo) {
                return $row;
            }
        }

        return null;
    }

    public function getPoItemsByNomor($nomorPo)
    {
        if ($this->relationExists('v_logistik_po_monitor')) {
            return $this->db->query("
                SELECT
                    id_pesanan_pabrik_detail,
                    id_purchase_request_detail,
                    id_kode_item,
                    nama_item,
                    satuan_item,
                    harga_item,
                    qty_po,
                    volume_planning_snapshot,
                    qty_terkirim,
                    qty_diterima,
                    outstanding_pengiriman,
                    outstanding_penerimaan,
                    total_nominal_detail
                FROM v_logistik_po_monitor
                WHERE nomor_po_pabrik = ?
                ORDER BY nama_item ASC
            ", [$nomorPo])->result_array();
        }

        return $this->db->query("
            SELECT
                d.id_pesanan_pabrik_detail,
                NULL AS id_purchase_request_detail,
                d.id_kode_item,
                ki.nama_item,
                ki.satuan_item,
                d.harga_item,
                d.qty_item AS qty_po,
                NULL AS volume_planning_snapshot,
                COALESCE(SUM(gd.qty_item), 0) AS qty_terkirim,
                COALESCE(SUM(gd.qty_item), 0) AS qty_diterima,
                (d.qty_item - COALESCE(SUM(gd.qty_item), 0)) AS outstanding_pengiriman,
                (d.qty_item - COALESCE(SUM(gd.qty_item), 0)) AS outstanding_penerimaan,
                (COALESCE(d.harga_item, 0) * COALESCE(d.qty_item, 0)) AS total_nominal_detail
            FROM tb_logistik_pesanan_pabrik p
            LEFT JOIN tb_logistik_pesanan_pabrik_detail d
                ON d.id_pesanan_pabrik = p.id_pesanan_pabrik
            LEFT JOIN tb_master_logistik_kode_item ki
                ON ki.id_kode_item = d.id_kode_item
            LEFT JOIN tb_logistik_pengiriman_pabrik_detail gd
                ON gd.id_pesanan_pabrik_detail = d.id_pesanan_pabrik_detail
            WHERE p.nomor_po_pabrik = ?
            GROUP BY
                d.id_pesanan_pabrik_detail,
                d.id_kode_item,
                ki.nama_item,
                ki.satuan_item,
                d.harga_item,
                d.qty_item
            ORDER BY ki.nama_item ASC
        ", [$nomorPo])->result_array();
    }

    public function getPoDeliveriesByNomor($nomorPo)
    {
        if ($this->relationExists('v_logistik_po_delivery')) {
            return $this->db->query("
                SELECT *
                FROM v_logistik_po_delivery
                WHERE nomor_po_pabrik = ?
                ORDER BY tanggal_pengiriman_pabrik DESC, id_pengiriman_pabrik_detail DESC
            ", [$nomorPo])->result_array();
        }

        $hasNomorPoColumn = $this->fieldExists('tb_logistik_pengiriman_pabrik', 'nomor_po_pabrik');
        $whereSql = $hasNomorPoColumn
            ? "gp.nomor_po_pabrik = ?"
            : "p.nomor_po_pabrik = ?";

        return $this->db->query("
            SELECT
                gp.id_pengiriman_pabrik,
                gp.no_surat_jalan,
                gp.id_lokasi_gudang,
                lg.kota_lokasi_gudang,
                gp.tanggal_pengiriman_pabrik,
                gp.surat_jalan_pabrik,
                gp.surat_jalan_ho,
                gp.id_user,
                gd.id_pengiriman_pabrik_detail,
                gd.id_pesanan_pabrik_detail,
                gd.qty_item AS qty_kirim,
                gd.qty_item AS qty_diterima,
                ki.nama_item
            FROM tb_logistik_pengiriman_pabrik gp
            LEFT JOIN tb_master_logistik_lokasi_gudang lg
                ON lg.id_lokasi_gudang = gp.id_lokasi_gudang
            LEFT JOIN tb_logistik_pengiriman_pabrik_detail gd
                ON gd.id_pengiriman_pabrik = gp.id_pengiriman_pabrik
            LEFT JOIN tb_logistik_pesanan_pabrik_detail pd
                ON pd.id_pesanan_pabrik_detail = gd.id_pesanan_pabrik_detail
            LEFT JOIN tb_logistik_pesanan_pabrik p
                ON p.id_pesanan_pabrik = pd.id_pesanan_pabrik
            LEFT JOIN tb_master_logistik_kode_item ki
                ON ki.id_kode_item = pd.id_kode_item
            WHERE {$whereSql}
            ORDER BY gp.tanggal_pengiriman_pabrik DESC, gd.id_pengiriman_pabrik_detail DESC
        ", [$nomorPo])->result_array();
    }

    public function getAllGudang()
    {
        return $this->db->query("
            SELECT id_lokasi_gudang, kota_lokasi_gudang, regional_lokasi_gudang
            FROM tb_master_logistik_lokasi_gudang
            ORDER BY regional_lokasi_gudang ASC, kota_lokasi_gudang ASC
        ")->result_array();
    }

    public function getMasterSumberMaterialByName($name)
    {
        return $this->db
            ->get_where('tb_master_logistik_sumber_material', ['nama_sumber_material' => $name])
            ->row_array();
    }

    public function getPreferredBowheerId($idLokasiGudang, $idKodeItem)
    {
        $latest = $this->db
            ->select('id_bowheer')
            ->from('tb_logistik_stok')
            ->where('id_lokasi_gudang', (int) $idLokasiGudang)
            ->where('id_kode_item', (int) $idKodeItem)
            ->order_by('tanggal_upload_stok', 'DESC')
            ->order_by('id_logistik_stok', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        if (!empty($latest['id_bowheer'])) {
            return (int) $latest['id_bowheer'];
        }

        $itemOwner = $this->db
            ->select('id_bowheer_pemilik_item')
            ->from('tb_master_logistik_kode_item')
            ->where('id_kode_item', (int) $idKodeItem)
            ->limit(1)
            ->get()
            ->row_array();

        if (!empty($itemOwner['id_bowheer_pemilik_item'])) {
            return (int) $itemOwner['id_bowheer_pemilik_item'];
        }

        $fallback = $this->db
            ->select('id_bowheer')
            ->from('tb_master_bowheer')
            ->order_by('id_bowheer', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();

        return (int) ($fallback['id_bowheer'] ?? 0);
    }

    private function relationExists($name)
    {
        $row = $this->db->query("SHOW FULL TABLES LIKE ?", [$name])->row_array();
        return !empty($row);
    }

    public function getOutstandingPrItemMap($idPurchaseRequest)
    {
        $items = $this->getApprovedPurchaseRequestItems($idPurchaseRequest);
        $map = [];

        foreach ($items as $item) {
            $detailId = (string) ($item['id_purchase_request_detail'] ?? '');
            if ($detailId === '') {
                continue;
            }

            $map[$detailId] = $item;
        }

        return $map;
    }

    private function fieldExists($table, $field)
    {
        return in_array($field, $this->db->list_fields($table), true);
    }
}
