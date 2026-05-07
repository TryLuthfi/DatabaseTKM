<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MLogistik_Pesanan_Pabrik extends CI_Model
{
    public function getMasterPabrikActive()
    {
        return $this->db->query("
            SELECT id_pabrik, nama_pabrik, lokasi_pabrik, jenis_pabrik, pic_pabrik, tlp_pabrik, status_pabrik
            FROM tb_master_logistik_pabrik
            WHERE COALESCE(NULLIF(UPPER(TRIM(status_pabrik)), ''), 'ACTIVE') IN ('ACTIVE', 'AKTIF')
            ORDER BY nama_pabrik ASC
        ")->result_array();
    }

    public function getMasterSystemPembayaran(): array
    {
        if (!$this->relationExists('tb_master_logistik_system_pembayaran')) {
            return [];
        }

        return $this->db->query("
            SELECT id_system_pembayaran, harga_system_pembayaran
            FROM tb_master_logistik_system_pembayaran
            ORDER BY id_system_pembayaran ASC
        ")->result_array();
    }

    public function getMasterJenisPembayaran(): array
    {
        if (!$this->relationExists('tb_master_logistik_jenis_pembayaran')) {
            return [];
        }

        return $this->db->query("
            SELECT id_jenis_pembayaran, detail_jenis_pembayaran
            FROM tb_master_logistik_jenis_pembayaran
            ORDER BY id_jenis_pembayaran ASC
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
                pr.nomor_purchase_request,
                pr.nama_project,
                pr.id_project
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

    public function getApprovedNodinOptions()
    {
        if (!$this->relationExists('tb_logistik_nota_dinas_po') || !$this->relationExists('tb_logistik_nota_dinas_po_detail')) {
            return [];
        }

        $rows = $this->db->query("
            SELECT
                h.*,
                GROUP_CONCAT(DISTINCT pr.nomor_purchase_request ORDER BY pr.nomor_purchase_request SEPARATOR ', ') AS nomor_purchase_request_refs,
                GROUP_CONCAT(DISTINCT COALESCE(pr.id_project, pr.nama_project) ORDER BY COALESCE(pr.id_project, pr.nama_project) SEPARATOR ', ') AS nama_project_refs,
                COUNT(DISTINCT d.id_nota_dinas_po_detail) AS total_item
            FROM tb_logistik_nota_dinas_po h
            INNER JOIN tb_logistik_nota_dinas_po_detail d
                ON d.id_nota_dinas_po = h.id_nota_dinas_po
            LEFT JOIN tb_logistik_purchase_request_detail prd
                ON prd.id_purchase_request_detail = d.id_purchase_request_detail
            LEFT JOIN tb_logistik_purchase_request pr
                ON pr.id_purchase_request = prd.id_purchase_request
            GROUP BY h.id_nota_dinas_po
            ORDER BY h.tanggal_nota_dinas DESC, h.id_nota_dinas_po DESC
        ")->result_array();

        $result = [];
        foreach ($rows as $row) {
            $workflowColumns = [
                'approved_manager_logistik',
                'approved_purchasing',
                'approved_gm_project',
                'approved_gm_finance',
                'approved_direktur',
            ];

            $isFullyApproved = true;
            foreach ($workflowColumns as $column) {
                if (array_key_exists($column, $row) && (int) $row[$column] !== 1) {
                    $isFullyApproved = false;
                    break;
                }
            }

            if (!$isFullyApproved) {
                continue;
            }

            $outstandingItems = $this->getApprovedNodinItems((string) $row['id_nota_dinas_po']);
            if (empty($outstandingItems)) {
                continue;
            }

            $row['total_qty_outstanding_nodin'] = array_sum(array_map(static function ($item) {
                return (float) ($item['qty_outstanding_nodin'] ?? 0);
            }, $outstandingItems));
            $vendorMap = [];
            foreach ($outstandingItems as $item) {
                $vendorId = (string) ($item['id_pabrik'] ?? '');
                if ($vendorId === '') {
                    continue;
                }

                $vendorMap[$vendorId] = [
                    'id_pabrik' => $vendorId,
                    'nama_pabrik' => (string) ($item['nama_pabrik'] ?? ''),
                ];
            }

            $bowheerMap = [];
            foreach ($outstandingItems as $item) {
                $bowheerLabel = trim((string) (($item['bowheer_label'] ?? $item['id_project'] ?? $item['nama_project'] ?? '')));
                if ($bowheerLabel === '') {
                    continue;
                }

                $bowheerMap[$bowheerLabel] = [
                    'label' => $bowheerLabel,
                ];
            }

            $row['total_vendor'] = count($vendorMap);
            $row['vendor_options'] = array_values($vendorMap);
            $row['bowheer_options'] = array_values($bowheerMap);

            $result[] = $row;
        }

        return $result;
    }

    public function getApprovedNodinItems($idNodin, $idPabrik = 0, $bowheerLabel = '', $groupRows = true)
    {
        if (!$this->relationExists('tb_logistik_nota_dinas_po_detail')) {
            return [];
        }

        $supportsNodinDetailRelation = $this->fieldExists('tb_logistik_pesanan_pabrik_detail', 'id_nota_dinas_po_detail');
        $supportsPrDetailRelation = $this->fieldExists('tb_logistik_pesanan_pabrik_detail', 'id_purchase_request_detail');

        if ($supportsNodinDetailRelation) {
            $allocationSelect = "COALESCE(alloc.qty_po_teralokasi, 0) AS qty_po_teralokasi,";
            $allocationJoin = "LEFT JOIN (
                SELECT
                    id_nota_dinas_po_detail,
                    SUM(COALESCE(qty_item, 0)) AS qty_po_teralokasi
                FROM tb_logistik_pesanan_pabrik_detail
                WHERE id_nota_dinas_po_detail IS NOT NULL
                    AND id_nota_dinas_po_detail <> ''
                GROUP BY id_nota_dinas_po_detail
            ) alloc ON alloc.id_nota_dinas_po_detail = d.id_nota_dinas_po_detail";
        } elseif ($supportsPrDetailRelation) {
            $allocationSelect = "COALESCE(alloc.qty_po_teralokasi, 0) AS qty_po_teralokasi,";
            $allocationJoin = "LEFT JOIN (
                SELECT
                    id_purchase_request_detail,
                    SUM(COALESCE(qty_item, 0)) AS qty_po_teralokasi
                FROM tb_logistik_pesanan_pabrik_detail
                WHERE id_purchase_request_detail IS NOT NULL
                    AND id_purchase_request_detail <> ''
                GROUP BY id_purchase_request_detail
            ) alloc ON alloc.id_purchase_request_detail = d.id_purchase_request_detail";
        } else {
            $allocationSelect = "0 AS qty_po_teralokasi,";
            $allocationJoin = "";
        }

        $pabrikFilterSql = $idPabrik > 0 ? " AND d.id_pabrik = " . (int) $idPabrik : "";
        $bowheerFilterSql = '';
        $params = [$idNodin];
        if (trim((string) $bowheerLabel) !== '') {
            $bowheerFilterSql = " AND LOWER(TRIM(COALESCE(pr.id_project, pr.nama_project, ''))) = LOWER(TRIM(?)) ";
            $params[] = trim((string) $bowheerLabel);
        }

        $rows = $this->db->query("
            SELECT
                d.id_nota_dinas_po_detail,
                d.id_nota_dinas_po,
                d.id_purchase_request_detail,
                d.id_kode_item,
                d.id_pabrik,
                d.kebutuhan_project,
                d.outstanding_pr,
                d.qty_po_nodin,
                d.harga_satuan,
                d.keterangan,
                pr.nomor_purchase_request,
                pr.id_project,
                pr.nama_project,
                COALESCE(pr.id_project, pr.nama_project) AS bowheer_label,
                ki.nama_item,
                ki.satuan_item,
                mp.nama_pabrik,
                {$allocationSelect}
                prd.qty_request,
                prd.qty_planning,
                prd.keterangan_planning
            FROM tb_logistik_nota_dinas_po_detail d
            LEFT JOIN tb_logistik_purchase_request_detail prd
                ON prd.id_purchase_request_detail = d.id_purchase_request_detail
            LEFT JOIN tb_logistik_purchase_request pr
                ON pr.id_purchase_request = prd.id_purchase_request
            LEFT JOIN tb_master_logistik_kode_item ki
                ON ki.id_kode_item = d.id_kode_item
            LEFT JOIN tb_master_logistik_pabrik mp
                ON mp.id_pabrik = d.id_pabrik
            {$allocationJoin}
            WHERE d.id_nota_dinas_po = ?
            {$pabrikFilterSql}
            {$bowheerFilterSql}
            ORDER BY pr.nomor_purchase_request ASC, ki.nama_item ASC, d.id_nota_dinas_po_detail ASC
        ", $params)->result_array();

        foreach ($rows as &$row) {
            $row['qty_po_teralokasi'] = (float) ($row['qty_po_teralokasi'] ?? 0);
            $row['qty_outstanding_nodin'] = max((float) ($row['qty_po_nodin'] ?? 0) - $row['qty_po_teralokasi'], 0);
            $row['volume_planning_final'] = (float) ($row['kebutuhan_project'] ?? 0);
            $row['is_selectable'] = $row['qty_outstanding_nodin'] > 0;
        }
        unset($row);

        $rows = array_values(array_filter($rows, static function ($row) {
            return !empty($row['is_selectable']);
        }));

        if (!$groupRows) {
            return $rows;
        }

        $grouped = [];
        foreach ($rows as $row) {
            $groupKey = implode('|', [
                (string) ($row['nomor_purchase_request'] ?? ''),
                (string) ($row['id_kode_item'] ?? ''),
                (string) ($row['id_pabrik'] ?? ''),
                (string) ($row['harga_satuan'] ?? ''),
                trim((string) ($row['keterangan'] ?? '')),
                trim((string) ($row['bowheer_label'] ?? '')),
            ]);

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = $row;
                $grouped[$groupKey]['source_nodin_detail_ids'] = [];
                $grouped[$groupKey]['source_purchase_request_detail_ids'] = [];
            } else {
                $grouped[$groupKey]['qty_request'] = (float) ($grouped[$groupKey]['qty_request'] ?? 0) + (float) ($row['qty_request'] ?? 0);
                $grouped[$groupKey]['qty_planning'] = (float) ($grouped[$groupKey]['qty_planning'] ?? 0) + (float) ($row['qty_planning'] ?? 0);
                $grouped[$groupKey]['kebutuhan_project'] = (float) ($grouped[$groupKey]['kebutuhan_project'] ?? 0) + (float) ($row['kebutuhan_project'] ?? 0);
                $grouped[$groupKey]['outstanding_pr'] = (float) ($grouped[$groupKey]['outstanding_pr'] ?? 0) + (float) ($row['outstanding_pr'] ?? 0);
                $grouped[$groupKey]['qty_po_nodin'] = (float) ($grouped[$groupKey]['qty_po_nodin'] ?? 0) + (float) ($row['qty_po_nodin'] ?? 0);
                $grouped[$groupKey]['qty_po_teralokasi'] = (float) ($grouped[$groupKey]['qty_po_teralokasi'] ?? 0) + (float) ($row['qty_po_teralokasi'] ?? 0);
                $grouped[$groupKey]['qty_outstanding_nodin'] = (float) ($grouped[$groupKey]['qty_outstanding_nodin'] ?? 0) + (float) ($row['qty_outstanding_nodin'] ?? 0);
                $grouped[$groupKey]['volume_planning_final'] = (float) ($grouped[$groupKey]['volume_planning_final'] ?? 0) + (float) ($row['volume_planning_final'] ?? 0);
            }

            $grouped[$groupKey]['source_nodin_detail_ids'][] = (string) ($row['id_nota_dinas_po_detail'] ?? '');
            $grouped[$groupKey]['source_purchase_request_detail_ids'][] = (string) ($row['id_purchase_request_detail'] ?? '');
        }

        foreach ($grouped as &$groupRow) {
            $groupRow['source_nodin_detail_ids'] = array_values(array_filter(array_unique($groupRow['source_nodin_detail_ids'])));
            $groupRow['source_purchase_request_detail_ids'] = array_values(array_filter(array_unique($groupRow['source_purchase_request_detail_ids'])));
            $groupRow['id_nota_dinas_po_detail'] = implode(',', $groupRow['source_nodin_detail_ids']);
            $groupRow['id_purchase_request_detail'] = implode(',', $groupRow['source_purchase_request_detail_ids']);
        }
        unset($groupRow);

        return array_values($grouped);
    }

    public function createPoFromApprovedPr($header, $details)
    {
        $nextDetailId = $this->getNextPoDetailId();
        foreach ($details as $detailIndex => &$detailRow) {
            if (!array_key_exists('id_pesanan_pabrik_detail', $detailRow) || (int) $detailRow['id_pesanan_pabrik_detail'] <= 0) {
                $detailRow['id_pesanan_pabrik_detail'] = $nextDetailId + $detailIndex;
            }
        }
        unset($detailRow);

        $this->db->trans_start();

        $this->db->insert('tb_logistik_pesanan_pabrik', $header);

        if (!empty($details)) {
            $this->db->insert_batch('tb_logistik_pesanan_pabrik_detail', $details);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function ensurePoDetailIdsByNomor($nomorPo)
    {
        $poHeader = $this->db
            ->select('id_pesanan_pabrik')
            ->from('tb_logistik_pesanan_pabrik')
            ->where('nomor_po_pabrik', $nomorPo)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($poHeader['id_pesanan_pabrik'])) {
            return;
        }

        $invalidRows = $this->db
            ->select('id_pesanan_pabrik, id_purchase_request_detail, id_nota_dinas_po_detail, id_kode_item, harga_item, qty_item, volume_planning_snapshot')
            ->from('tb_logistik_pesanan_pabrik_detail')
            ->where('id_pesanan_pabrik', $poHeader['id_pesanan_pabrik'])
            ->group_start()
                ->where('id_pesanan_pabrik_detail', 0)
                ->or_where('id_pesanan_pabrik_detail IS NULL', null, false)
            ->group_end()
            ->get()
            ->result_array();

        if (empty($invalidRows)) {
            return;
        }

        $nextDetailId = $this->getNextPoDetailId();
        foreach ($invalidRows as $rowIndex => $invalidRow) {
            $newId = $nextDetailId + $rowIndex;
            $this->db->where('id_pesanan_pabrik', $invalidRow['id_pesanan_pabrik']);
            $this->db->group_start()
                ->where('id_pesanan_pabrik_detail', 0)
                ->or_where('id_pesanan_pabrik_detail IS NULL', null, false)
                ->group_end();
            $this->db->where('id_kode_item', $invalidRow['id_kode_item']);
            $this->db->where('harga_item', $invalidRow['harga_item']);
            $this->db->where('qty_item', $invalidRow['qty_item']);

            if ($this->fieldExists('tb_logistik_pesanan_pabrik_detail', 'id_purchase_request_detail')) {
                if ($invalidRow['id_purchase_request_detail'] === null || $invalidRow['id_purchase_request_detail'] === '') {
                    $this->db->where('id_purchase_request_detail IS NULL', null, false);
                } else {
                    $this->db->where('id_purchase_request_detail', $invalidRow['id_purchase_request_detail']);
                }
            }

            if ($this->fieldExists('tb_logistik_pesanan_pabrik_detail', 'id_nota_dinas_po_detail')) {
                if ($invalidRow['id_nota_dinas_po_detail'] === null || $invalidRow['id_nota_dinas_po_detail'] === '') {
                    $this->db->where('id_nota_dinas_po_detail IS NULL', null, false);
                } else {
                    $this->db->where('id_nota_dinas_po_detail', $invalidRow['id_nota_dinas_po_detail']);
                }
            }

            if ($this->fieldExists('tb_logistik_pesanan_pabrik_detail', 'volume_planning_snapshot')) {
                if ($invalidRow['volume_planning_snapshot'] === null || $invalidRow['volume_planning_snapshot'] === '') {
                    $this->db->where('volume_planning_snapshot IS NULL', null, false);
                } else {
                    $this->db->where('volume_planning_snapshot', $invalidRow['volume_planning_snapshot']);
                }
            }

            $this->db->limit(1);
            $this->db->update('tb_logistik_pesanan_pabrik_detail', [
                'id_pesanan_pabrik_detail' => $newId,
            ]);
        }
    }

    public function getPoSummaryRows()
    {
        $supportsNodinReference = $this->fieldExists('tb_logistik_pesanan_pabrik_detail', 'id_nota_dinas_po_detail')
            && $this->relationExists('tb_logistik_nota_dinas_po_detail')
            && $this->relationExists('tb_logistik_nota_dinas_po');
        $supportsQtyClosedManual = $this->fieldExists('tb_logistik_pesanan_pabrik_detail', 'qty_closed_manual');
        $qtyClosedExpression = $supportsQtyClosedManual ? 'd.qty_closed_manual' : '0';

        $referenceJoinSql = '';
        $referenceSelectSql = "NULL AS nomor_nota_dinas_refs, NULL AS nomor_purchase_request_refs, NULL AS bowheer_refs,";
        $referenceGroupSql = '';

        if ($supportsNodinReference) {
            $referenceJoinSql = "
                LEFT JOIN (
                    SELECT
                        pd.id_pesanan_pabrik,
                        GROUP_CONCAT(DISTINCT h.nomor_nota_dinas ORDER BY h.nomor_nota_dinas SEPARATOR ', ') AS nomor_nota_dinas_refs,
                        GROUP_CONCAT(DISTINCT pr.nomor_purchase_request ORDER BY pr.nomor_purchase_request SEPARATOR ', ') AS nomor_purchase_request_refs,
                        GROUP_CONCAT(DISTINCT COALESCE(pr.id_project, pr.nama_project) ORDER BY COALESCE(pr.id_project, pr.nama_project) SEPARATOR ', ') AS bowheer_refs
                    FROM tb_logistik_pesanan_pabrik_detail pd
                    LEFT JOIN tb_logistik_nota_dinas_po_detail nd
                        ON nd.id_nota_dinas_po_detail = pd.id_nota_dinas_po_detail
                    LEFT JOIN tb_logistik_nota_dinas_po h
                        ON h.id_nota_dinas_po = nd.id_nota_dinas_po
                    LEFT JOIN tb_logistik_purchase_request_detail prd
                        ON prd.id_purchase_request_detail = pd.id_purchase_request_detail
                    LEFT JOIN tb_logistik_purchase_request pr
                        ON pr.id_purchase_request = prd.id_purchase_request
                    GROUP BY pd.id_pesanan_pabrik
                ) refs ON refs.id_pesanan_pabrik = p.id_pesanan_pabrik
            ";
            $referenceSelectSql = "
                refs.nomor_nota_dinas_refs,
                refs.nomor_purchase_request_refs,
                refs.bowheer_refs,
            ";
            $referenceGroupSql = ",
                    refs.nomor_nota_dinas_refs,
                    refs.nomor_purchase_request_refs,
                    refs.bowheer_refs";
        }

        return $this->db->query("
            SELECT
                p.id_pesanan_pabrik,
                p.id_purchase_request,
                p.nomor_purchase_request,
                {$referenceSelectSql}
                p.id_pabrik,
                p.nomor_po_pabrik,
                p.tanggal_po_pabrik,
                p.purchase_order_document,
                COALESCE(NULLIF(p.status_po, ''), 'APPROVED') AS status_po,
                p.id_user,
                mp.nama_pabrik,
                mp.lokasi_pabrik,
                mp.jenis_pabrik,
                mp.pic_pabrik,
                mp.tlp_pabrik,
                mu.nama_user,
                " . ($this->fieldExists('tb_logistik_pesanan_pabrik', 'id_system_pembayaran') ? 'p.id_system_pembayaran' : 'NULL AS id_system_pembayaran') . ",
                " . ($this->fieldExists('tb_logistik_pesanan_pabrik', 'id_jenis_pembayaran') ? 'p.id_jenis_pembayaran' : 'NULL AS id_jenis_pembayaran') . ",
                " . ($this->fieldExists('tb_logistik_pesanan_pabrik', 'keterangan_po') ? 'p.keterangan_po' : 'NULL AS keterangan_po') . ",
                " . ($this->fieldExists('tb_logistik_pesanan_pabrik', 'waktu_pengiriman_material') ? 'p.waktu_pengiriman_material' : 'NULL AS waktu_pengiriman_material') . ",
                sp.harga_system_pembayaran,
                jp.detail_jenis_pembayaran,
                COUNT(DISTINCT d.id_pesanan_pabrik_detail) AS total_item,
                COALESCE(SUM(d.qty_item), 0) AS total_qty_po,
                COALESCE(SUM(gd.qty_item), 0) AS total_qty_terkirim,
                COALESCE(SUM(COALESCE(gd.qty_diterima, 0)), 0) AS total_qty_diterima,
                (COALESCE(SUM(d.qty_item), 0) - COALESCE(SUM(gd.qty_item), 0) - COALESCE(SUM({$qtyClosedExpression}), 0)) AS total_outstanding,
                COALESCE(SUM(COALESCE(d.harga_item, 0) * COALESCE(d.qty_item, 0)), 0) AS total_nominal_po,
                (COALESCE(SUM(COALESCE(d.harga_item, 0) * COALESCE(d.qty_item, 0)), 0) * 11 / 12) AS total_dpp_po,
                ((COALESCE(SUM(COALESCE(d.harga_item, 0) * COALESCE(d.qty_item, 0)), 0) * 11 / 12) * 0.12) AS total_ppn_po
            FROM tb_logistik_pesanan_pabrik p
            LEFT JOIN tb_master_logistik_pabrik mp
                ON mp.id_pabrik = p.id_pabrik
            LEFT JOIN tb_master_user mu
                ON mu.id_user = p.id_user
            " . ($this->relationExists('tb_master_logistik_system_pembayaran') && $this->fieldExists('tb_logistik_pesanan_pabrik', 'id_system_pembayaran')
                ? "LEFT JOIN tb_master_logistik_system_pembayaran sp
                ON sp.id_system_pembayaran = p.id_system_pembayaran"
                : "LEFT JOIN (SELECT NULL AS id_system_pembayaran, NULL AS harga_system_pembayaran) sp ON 1=0") . "
            " . ($this->relationExists('tb_master_logistik_jenis_pembayaran') && $this->fieldExists('tb_logistik_pesanan_pabrik', 'id_jenis_pembayaran')
                ? "LEFT JOIN tb_master_logistik_jenis_pembayaran jp
                ON jp.id_jenis_pembayaran = p.id_jenis_pembayaran"
                : "LEFT JOIN (SELECT NULL AS id_jenis_pembayaran, NULL AS detail_jenis_pembayaran) jp ON 1=0") . "
            {$referenceJoinSql}
            LEFT JOIN tb_logistik_pesanan_pabrik_detail d
                ON d.id_pesanan_pabrik = p.id_pesanan_pabrik
            LEFT JOIN tb_logistik_pengiriman_pabrik_detail gd
                ON gd.id_pesanan_pabrik_detail = d.id_pesanan_pabrik_detail
            GROUP BY
                p.id_pesanan_pabrik,
                p.id_purchase_request,
                p.nomor_purchase_request,
                p.id_pabrik,
                p.nomor_po_pabrik,
                p.tanggal_po_pabrik,
                p.purchase_order_document,
                p.status_po,
                p.id_user,
                mp.nama_pabrik,
                mp.lokasi_pabrik,
                mp.jenis_pabrik,
                mp.pic_pabrik,
                mp.tlp_pabrik,
                mu.nama_user
                " . ($this->fieldExists('tb_logistik_pesanan_pabrik', 'id_system_pembayaran') ? ', p.id_system_pembayaran' : '') . "
                " . ($this->fieldExists('tb_logistik_pesanan_pabrik', 'id_jenis_pembayaran') ? ', p.id_jenis_pembayaran' : '') . "
                " . ($this->fieldExists('tb_logistik_pesanan_pabrik', 'keterangan_po') ? ', p.keterangan_po' : '') . "
                " . ($this->fieldExists('tb_logistik_pesanan_pabrik', 'waktu_pengiriman_material') ? ', p.waktu_pengiriman_material' : '') . "
                , sp.harga_system_pembayaran
                , jp.detail_jenis_pembayaran
                {$referenceGroupSql}
            ORDER BY p.tanggal_po_pabrik DESC, p.nomor_po_pabrik DESC
        ")->result_array();
    }

    public function getPoPrintHeaderByNomor($nomorPo)
    {
        return $this->getPoHeaderByNomor($nomorPo);
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
            'total_dpp_po' => 0,
            'total_ppn_po' => 0,
            'completed_po' => 0,
            'partial_po' => 0,
        ];

        foreach ($rows as $row) {
            $stats['total_item'] += (int) ($row['total_item'] ?? 0);
            $stats['total_qty_po'] += (float) ($row['total_qty_po'] ?? 0);
            $stats['total_qty_terkirim'] += (float) ($row['total_qty_terkirim'] ?? 0);
            $stats['total_outstanding'] += (float) ($row['total_outstanding'] ?? 0);
            $stats['total_nominal_po'] += (float) ($row['total_nominal_po'] ?? 0);
            $stats['total_dpp_po'] += (float) ($row['total_dpp_po'] ?? 0);
            $stats['total_ppn_po'] += (float) ($row['total_ppn_po'] ?? 0);

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

    public function deletePoByNomor($nomorPo)
    {
        $poHeader = $this->db
            ->select('id_pesanan_pabrik, nomor_po_pabrik')
            ->from('tb_logistik_pesanan_pabrik')
            ->where('nomor_po_pabrik', $nomorPo)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($poHeader['id_pesanan_pabrik'])) {
            return [
                'success' => false,
                'message' => 'PO tidak ditemukan.',
            ];
        }

        $poDetailIds = $this->db
            ->select('id_pesanan_pabrik_detail')
            ->from('tb_logistik_pesanan_pabrik_detail')
            ->where('id_pesanan_pabrik', $poHeader['id_pesanan_pabrik'])
            ->get()
            ->result_array();

        $detailIds = array_values(array_filter(array_map(static function ($row) {
            return (int) ($row['id_pesanan_pabrik_detail'] ?? 0);
        }, $poDetailIds)));

        $hasReceivedShipment = false;
        if (!empty($detailIds) && $this->relationExists('tb_logistik_pengiriman_pabrik_detail')) {
            $receivedShipment = $this->db
                ->select('MAX(COALESCE(qty_diterima, 0)) AS max_qty_diterima', false)
                ->from('tb_logistik_pengiriman_pabrik_detail')
                ->where_in('id_pesanan_pabrik_detail', $detailIds)
                ->get()
                ->row_array();

            $hasReceivedShipment = (float) ($receivedShipment['max_qty_diterima'] ?? 0) > 0;
        }

        if ($hasReceivedShipment && $this->relationExists('tb_logistik_stok') && $this->fieldExists('tb_logistik_stok', 'no_po_logistik')) {
            $stockCount = (int) $this->db
                ->from('tb_logistik_stok')
                ->where('no_po_logistik', $nomorPo)
                ->count_all_results();

            if ($stockCount > 0) {
                return [
                    'success' => false,
                    'message' => 'PO tidak bisa dihapus karena masih ada histori stok yang mengacu ke PO ini.',
                ];
            }
        }

        $shipmentHeaderIds = [];
        if (!empty($detailIds) && $this->relationExists('tb_logistik_pengiriman_pabrik_detail')) {
            $shipmentRows = $this->db
                ->select('DISTINCT id_pengiriman_pabrik', false)
                ->from('tb_logistik_pengiriman_pabrik_detail')
                ->where_in('id_pesanan_pabrik_detail', $detailIds)
                ->get()
                ->result_array();

            $shipmentHeaderIds = array_values(array_filter(array_map(static function ($row) {
                return (int) ($row['id_pengiriman_pabrik'] ?? 0);
            }, $shipmentRows)));
        }

        $this->db->trans_start();

        if (!empty($detailIds) && $this->relationExists('tb_logistik_pengiriman_pabrik_detail')) {
            $this->db->where_in('id_pesanan_pabrik_detail', $detailIds);
            $this->db->delete('tb_logistik_pengiriman_pabrik_detail');
        }

        if (!empty($shipmentHeaderIds) && $this->relationExists('tb_logistik_pengiriman_pabrik')) {
            $this->db->where_in('id_pengiriman_pabrik', $shipmentHeaderIds);
            $this->db->delete('tb_logistik_pengiriman_pabrik');
        }

        $this->db->where('id_pesanan_pabrik', $poHeader['id_pesanan_pabrik']);
        $this->db->delete('tb_logistik_pesanan_pabrik_detail');

        $this->db->where('id_pesanan_pabrik', $poHeader['id_pesanan_pabrik']);
        $this->db->delete('tb_logistik_pesanan_pabrik');

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return [
                'success' => false,
                'message' => 'Gagal menghapus PO.',
            ];
        }

        return [
            'success' => true,
            'message' => 'PO berhasil dihapus.',
        ];
    }

    public function getPoItemsByNomor($nomorPo)
    {
        $supportsNodinDetail = $this->fieldExists('tb_logistik_pesanan_pabrik_detail', 'id_nota_dinas_po_detail')
            && $this->relationExists('tb_logistik_nota_dinas_po_detail')
            && $this->relationExists('tb_logistik_nota_dinas_po');
        $nodinJoinSql = $supportsNodinDetail
            ? "LEFT JOIN tb_logistik_nota_dinas_po_detail nd
                    ON nd.id_nota_dinas_po_detail = pd.id_nota_dinas_po_detail
                LEFT JOIN tb_logistik_nota_dinas_po h
                    ON h.id_nota_dinas_po = nd.id_nota_dinas_po"
            : "";
        $nodinSelectSql = $supportsNodinDetail ? "h.nomor_nota_dinas" : "NULL AS nomor_nota_dinas";
        $nodinGroupSql = $supportsNodinDetail ? ", h.nomor_nota_dinas" : "";

        if ($this->relationExists('v_logistik_po_monitor')) {
            return $this->db->query("
                SELECT
                    m.id_pesanan_pabrik_detail,
                    m.id_purchase_request_detail,
                    m.id_kode_item,
                    m.nama_item,
                    m.satuan_item,
                    m.harga_item,
                    m.qty_po,
                    m.volume_planning_snapshot,
                    m.qty_terkirim,
                    m.qty_diterima,
                    m.outstanding_pengiriman,
                    m.outstanding_penerimaan,
                    m.total_nominal_detail,
                    pr.nomor_purchase_request,
                    {$nodinSelectSql}
                FROM v_logistik_po_monitor m
                LEFT JOIN tb_logistik_pesanan_pabrik_detail pd
                    ON pd.id_pesanan_pabrik_detail = m.id_pesanan_pabrik_detail
                {$nodinJoinSql}
                LEFT JOIN tb_logistik_purchase_request_detail prd
                    ON prd.id_purchase_request_detail = m.id_purchase_request_detail
                LEFT JOIN tb_logistik_purchase_request pr
                    ON pr.id_purchase_request = prd.id_purchase_request
                WHERE m.nomor_po_pabrik = ?
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
                (COALESCE(d.harga_item, 0) * COALESCE(d.qty_item, 0)) AS total_nominal_detail,
                pr.nomor_purchase_request,
                {$nodinSelectSql}
            FROM tb_logistik_pesanan_pabrik p
            LEFT JOIN tb_logistik_pesanan_pabrik_detail d
                ON d.id_pesanan_pabrik = p.id_pesanan_pabrik
            LEFT JOIN tb_master_logistik_kode_item ki
                ON ki.id_kode_item = d.id_kode_item
            " . ($supportsNodinDetail ? "LEFT JOIN tb_logistik_nota_dinas_po_detail nd
                ON nd.id_nota_dinas_po_detail = d.id_nota_dinas_po_detail
            LEFT JOIN tb_logistik_nota_dinas_po h
                ON h.id_nota_dinas_po = nd.id_nota_dinas_po" : "") . "
            LEFT JOIN tb_logistik_purchase_request_detail prd
                ON prd.id_purchase_request_detail = d.id_purchase_request_detail
            LEFT JOIN tb_logistik_purchase_request pr
                ON pr.id_purchase_request = prd.id_purchase_request
            LEFT JOIN tb_logistik_pengiriman_pabrik_detail gd
                ON gd.id_pesanan_pabrik_detail = d.id_pesanan_pabrik_detail
            WHERE p.nomor_po_pabrik = ?
            GROUP BY
                d.id_pesanan_pabrik_detail,
                d.id_kode_item,
                ki.nama_item,
                ki.satuan_item,
                d.harga_item,
                d.qty_item,
                pr.nomor_purchase_request
                {$nodinGroupSql}
            ORDER BY ki.nama_item ASC
        ", [$nomorPo])->result_array();
    }

    public function getPoDeliveriesByNomor($nomorPo)
    {
        if ($this->relationExists('v_logistik_po_delivery')) {
            return $this->db->query("
                SELECT
                    d.*,
                    ki.nama_item
                FROM v_logistik_po_delivery d
                LEFT JOIN tb_logistik_pesanan_pabrik_detail pd
                    ON pd.id_pesanan_pabrik_detail = d.id_pesanan_pabrik_detail
                LEFT JOIN tb_master_logistik_kode_item ki
                    ON ki.id_kode_item = pd.id_kode_item
                WHERE d.nomor_po_pabrik = ?
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

    public function getDefaultHoGudang()
    {
        $row = $this->db
            ->select('id_lokasi_gudang, kota_lokasi_gudang, regional_lokasi_gudang')
            ->from('tb_master_logistik_lokasi_gudang')
            ->group_start()
                ->where('UPPER(kota_lokasi_gudang)', 'HO')
                ->or_where('UPPER(regional_lokasi_gudang)', 'HO')
            ->group_end()
            ->order_by('id_lokasi_gudang', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();

        return $row ?: null;
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

    private function getNextPoDetailId()
    {
        $row = $this->db
            ->select('MAX(id_pesanan_pabrik_detail) AS max_id', false)
            ->from('tb_logistik_pesanan_pabrik_detail')
            ->get()
            ->row_array();

        return ((int) ($row['max_id'] ?? 0)) + 1;
    }
}
