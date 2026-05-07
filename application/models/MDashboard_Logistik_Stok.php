<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MDashboard_Logistik_Stok extends CI_Model
{

    public function getAllStokLogistik()
    {
        $data = $this->db->query('SELECT
    tls.id_lokasi_gudang,
    tls.surat_jalan,
    tls.evidence,
	tmllg.regional_lokasi_gudang,
	tmllg.kota_lokasi_gudang,
	tmb.nama_bowheer,
	tmlsm.nama_sumber_material,
	tls.no_surat_jalan,
	tmu.nama_user,
	tls.tanggal_upload_stok,
	tls.no_po_logistik,
	tls.no_pr_logistik,
	tmllg2.kota_lokasi_gudang AS kota_lokasi_gudang_pengiriman,
	SUM(tls.jumlah_stok) AS total_jumlah_stok
FROM
	tb_logistik_stok tls
JOIN tb_master_logistik_lokasi_gudang tmllg
    ON
	tls.id_lokasi_gudang = tmllg.id_lokasi_gudang
LEFT JOIN tb_master_logistik_lokasi_gudang tmllg2
    ON
	tls.id_lokasi_gudang_pengiriman = tmllg2.id_lokasi_gudang
JOIN tb_master_bowheer tmb
    ON
	tls.id_bowheer = tmb.id_bowheer
JOIN tb_master_logistik_sumber_material  tmlsm
    ON
	tls.id_sumber_material = tmlsm.id_sumber_material
JOIN tb_master_logistik_kode_item 
    ON
	tls.id_kode_item = tb_master_logistik_kode_item.id_kode_item
JOIN tb_master_user tmu
    ON
	tls.id_user = tmu.id_user
WHERE
	no_surat_jalan != ""
GROUP BY
	no_surat_jalan,
	tmllg.kota_lokasi_gudang
ORDER BY
	CASE
		WHEN no_surat_jalan LIKE "%stock_opname%" THEN 1
		ELSE 0
	END,
	id_logistik_stok DESC;')
            ->result_array();
        return $data;
    }

    public function getAllStokByKategory()
    {
        $data = $this->db->query('SELECT *, SUM(CASE WHEN tmlsm.status_sumber_material LIKE "IN" THEN tls.jumlah_stok
					WHEN tmlsm.status_sumber_material LIKE "OUT" THEN -tls.jumlah_stok
					ELSE 0 END) AS total_jumlah_stok
	FROM tb_logistik_stok tls 
	LEFT JOIN tb_master_logistik_sumber_material tmlsm USING(id_sumber_material)
	RIGHT JOIN tb_master_logistik_kode_item tmlki USING(id_kode_item)
	GROUP BY tmlki.kategori_item')
            ->result_array();
        return $data;
    }
    public function getUniqueKotaGudang()
    {
        $data = $this->db->query('SELECT tb_logistik_stok.id_lokasi_gudang, tb_master_logistik_lokasi_gudang.kota_lokasi_gudang 
                                        FROM tb_logistik_stok 
                                        JOIN tb_master_logistik_lokasi_gudang ON tb_logistik_stok.id_lokasi_gudang = tb_master_logistik_lokasi_gudang.id_lokasi_gudang
                                        GROUP BY tb_master_logistik_lokasi_gudang.kota_lokasi_gudang;')
            ->result_array();
        return $data;
    }

    public function getAllStokByKategoryFilterCity()
    {
        $data = $this->db->query('SELECT 
    lg.regional_lokasi_gudang,
    lg.kota_lokasi_gudang,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Aksesories" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Aksesories,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Closure"
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Closure,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item IN ("FAT / ODP", "FAT") 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FAT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item IN ("FDT | ODC", "FDT") 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FDT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "HDPE" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_HDPE,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Kabel" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Kabel,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "OTB" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_OTB,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Tiang" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Tiang
FROM 
    tb_master_logistik_lokasi_gudang lg
LEFT JOIN 
    tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
LEFT JOIN 
    tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
LEFT JOIN 
    tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
GROUP BY 
    lg.kota_lokasi_gudang
ORDER BY 
    lg.regional_lokasi_gudang, lg.kota_lokasi_gudang;')
            ->result_array();
        return $data;
    }

    public function getAllStokByKategoryFilterRegional()
    {
        $data = $this->db->query('SELECT 
    lg.regional_lokasi_gudang,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Aksesories" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Aksesories,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Closure"
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Closure,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item IN ("FAT / ODP", "FAT") 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FAT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item IN ("FDT | ODC", "FDT") 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FDT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "HDPE" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_HDPE,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Kabel" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Kabel,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "OTB" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_OTB,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Tiang" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Tiang
FROM 
    tb_master_logistik_lokasi_gudang lg
LEFT JOIN 
    tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
LEFT JOIN 
    tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
LEFT JOIN 
    tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
GROUP BY 
    lg.regional_lokasi_gudang
ORDER BY 
    lg.regional_lokasi_gudang;')
            ->result_array();
        return $data;
    }

    public function getUniqueProjectLogistik()
    {
        $data = $this->db->query('SELECT tb_logistik_stok.id_bowheer, tb_master_bowheer.nama_bowheer 
                                        FROM tb_logistik_stok 
                                        JOIN tb_master_bowheer ON tb_logistik_stok.id_bowheer = tb_master_bowheer.id_bowheer
                                        GROUP BY tb_master_bowheer.nama_bowheer;')
            ->result_array();
        return $data;
    }
    public function getUniqueItemLogistik()
    {
        $data = $this->db->query('SELECT tb_logistik_stok.id_kode_item, tb_master_logistik_kode_item.nama_item 
                                        FROM tb_logistik_stok 
                                        JOIN tb_master_logistik_kode_item ON tb_logistik_stok.id_kode_item = tb_master_logistik_kode_item.id_kode_item
                                        GROUP BY tb_master_logistik_kode_item.nama_item;')
            ->result_array();
        return $data;
    }
    public function getUniqueSumberMaterial()
    {
        $data = $this->db->query('SELECT tb_logistik_stok.id_sumber_material, tb_master_logistik_sumber_material.nama_sumber_material 
                                        FROM tb_logistik_stok 
                                        JOIN tb_master_logistik_sumber_material ON tb_logistik_stok.id_sumber_material = tb_master_logistik_sumber_material.id_sumber_material
                                        GROUP BY tb_master_logistik_sumber_material.nama_sumber_material;')
            ->result_array();
        return $data;
    }

    public function getListGudangLokasiUser(): mixed
    {
        $id_user = $this->session->userdata('id_user');
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_lokasi_gudang` WHERE id_user = "' . $id_user . '"')->result_array();
        return $data;
    }

    public function getListGudangLokasiUserAll(): mixed
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_lokasi_gudang`')->result_array();
        return $data;
    }
    public function getMasterProject(): mixed
    {
        $data = $this->db->query('SELECT * FROM `tb_master_bowheer`')->result_array();
        return $data;
    }
    public function getMasterSumberMaterial(): mixed
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_sumber_material`')->result_array();
        return $data;
    }

    public function getSumberMaterialRuleMap(): array
    {
        if (!$this->relationExists('tb_logistik_sumber_material_rule')) {
            return [];
        }

        $rows = $this->db->get('tb_logistik_sumber_material_rule')->result_array();
        $map = [];
        foreach ($rows as $row) {
            $map[(string) ($row['id_sumber_material'] ?? '')] = $row;
        }

        return $map;
    }

    public function getSumberMaterialRuleById($idSumberMaterial): array
    {
        if (!$this->relationExists('tb_logistik_sumber_material_rule')) {
            return [];
        }

        return $this->db
            ->get_where('tb_logistik_sumber_material_rule', ['id_sumber_material' => $idSumberMaterial])
            ->row_array() ?: [];
    }

    public function getSumberMaterialById($idSumberMaterial)
    {
        return $this->db
            ->get_where('tb_master_logistik_sumber_material', ['id_sumber_material' => $idSumberMaterial])
            ->row_array();
    }

    public function generateSuratJalanNumber($idLokasiGudang, $tanggalDokumen = ''): array
    {
        $idLokasiGudang = (int) $idLokasiGudang;
        if ($idLokasiGudang <= 0 || !$this->relationExists('tb_logistik_surat_jalan_counter')) {
            return [];
        }

        $period = $this->resolveSuratJalanPeriod($tanggalDokumen);
        $tahun = $period['tahun'];
        $bulanRomawi = $period['bulan_romawi'];

        $this->db->query("
            INSERT INTO tb_logistik_surat_jalan_counter (id_lokasi_gudang, tahun_counter, last_sequence)
            VALUES (?, ?, 0)
            ON DUPLICATE KEY UPDATE last_sequence = last_sequence
        ", [$idLokasiGudang, $tahun]);

        $this->db->set('last_sequence', 'last_sequence + 1', false);
        $this->db->where('id_lokasi_gudang', $idLokasiGudang);
        $this->db->where('tahun_counter', $tahun);
        $this->db->update('tb_logistik_surat_jalan_counter');

        $counterRow = $this->db
            ->get_where('tb_logistik_surat_jalan_counter', [
                'id_lokasi_gudang' => $idLokasiGudang,
                'tahun_counter' => $tahun,
            ])
            ->row_array();

        $sequence = (int) ($counterRow['last_sequence'] ?? 0);
        if ($sequence <= 0) {
            return [];
        }

        return $this->buildSuratJalanPayload($idLokasiGudang, $tahun, $bulanRomawi, $sequence);
    }

    public function previewSuratJalanNumber($idLokasiGudang, $tanggalDokumen = ''): array
    {
        $idLokasiGudang = (int) $idLokasiGudang;
        if ($idLokasiGudang <= 0 || !$this->relationExists('tb_logistik_surat_jalan_counter')) {
            return [];
        }

        $period = $this->resolveSuratJalanPeriod($tanggalDokumen);
        $tahun = $period['tahun'];
        $bulanRomawi = $period['bulan_romawi'];

        $counterRow = $this->db
            ->get_where('tb_logistik_surat_jalan_counter', [
                'id_lokasi_gudang' => $idLokasiGudang,
                'tahun_counter' => $tahun,
            ])
            ->row_array();

        $nextSequence = ((int) ($counterRow['last_sequence'] ?? 0)) + 1;
        if ($nextSequence <= 0) {
            $nextSequence = 1;
        }

        return $this->buildSuratJalanPayload($idLokasiGudang, $tahun, $bulanRomawi, $nextSequence);
    }

    public function getCurrentStockByGudangItem($idLokasiGudang, $idKodeItem): float
    {
        $row = $this->db->query("
            SELECT
                COALESCE(SUM(
                    CASE
                        WHEN sm.status_sumber_material = 'IN' THEN COALESCE(ls.jumlah_stok, 0)
                        WHEN sm.status_sumber_material = 'OUT' THEN -COALESCE(ls.jumlah_stok, 0)
                        ELSE 0
                    END
                ), 0) AS total_stok
            FROM tb_logistik_stok ls
            INNER JOIN tb_master_logistik_sumber_material sm
                ON sm.id_sumber_material = ls.id_sumber_material
            WHERE ls.id_lokasi_gudang = ?
              AND ls.id_kode_item = ?
        ", [(int) $idLokasiGudang, (int) $idKodeItem])->row_array();

        return (float) ($row['total_stok'] ?? 0);
    }

    private function convertMonthToRoman($month): string
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $map[(int) $month] ?? 'I';
    }

    private function resolveSuratJalanPeriod($tanggalDokumen): array
    {
        $tanggal = trim((string) $tanggalDokumen) !== '' ? trim((string) $tanggalDokumen) : date('Y-m-d');
        $timestamp = strtotime($tanggal);
        if ($timestamp === false) {
            $timestamp = time();
        }

        return [
            'tahun' => (int) date('Y', $timestamp),
            'bulan_romawi' => $this->convertMonthToRoman((int) date('n', $timestamp)),
        ];
    }

    private function buildSuratJalanPayload($idLokasiGudang, $tahun, $bulanRomawi, $sequence): array
    {
        return [
            'nomor_surat_jalan' => sprintf('TEC.%03d/TKM-%02d/SJ/%s/%04d', (int) $sequence, (int) $idLokasiGudang, (string) $bulanRomawi, (int) $tahun),
            'nomor_surat_jalan_year' => (int) $tahun,
            'nomor_surat_jalan_seq' => (int) $sequence,
        ];
    }

    private function relationExists($name)
    {
        $row = $this->db->query("SHOW FULL TABLES LIKE ?", [$name])->row_array();
        return !empty($row);
    }

    public function fieldExists($table, $field)
    {
        return $this->relationExists($table) && in_array($field, $this->db->list_fields($table), true);
    }
    public function getMasterKodeItem(): mixed
    {
        $data = $this->db->query('SELECT * FROM tb_master_logistik_kode_item tmlki join tb_master_bowheer tmb ON tmlki.id_bowheer_pemilik_item = tmb.id_bowheer')->result_array();
        return $data;
    }

    public function getAvailableStockItemsByGudang($idLokasiGudang, $projectItem = ''): array
    {
        $items = $this->buildStockItemsByGudangQuery($idLokasiGudang, $projectItem);
        if (!empty($items) || trim((string) $projectItem) === '') {
            return $items;
        }

        return $this->buildStockItemsByGudangQuery($idLokasiGudang, '');
    }

    private function buildStockItemsByGudangQuery($idLokasiGudang, $projectItem = ''): array
    {
        $params = [(int) $idLokasiGudang];
        $projectSql = '';
        if (trim((string) $projectItem) !== '') {
            $projectSql = ' WHERE LOWER(TRIM(COALESCE(ki.project_item, ""))) = LOWER(TRIM(?)) ';
            $params[] = trim((string) $projectItem);
        }

        return $this->db->query("
            SELECT
                ki.id_kode_item,
                ki.nama_item,
                ki.satuan_item,
                ki.id_bowheer_pemilik_item,
                b.nama_bowheer,
                COALESCE(stok.total_stok, 0) AS total_stok
            FROM tb_master_logistik_kode_item ki
            LEFT JOIN tb_master_bowheer b
                ON b.id_bowheer = ki.id_bowheer_pemilik_item
            LEFT JOIN (
                SELECT
                    ls.id_kode_item,
                    SUM(
                        CASE
                            WHEN sm.status_sumber_material = 'IN' THEN COALESCE(ls.jumlah_stok, 0)
                            WHEN sm.status_sumber_material = 'OUT' THEN -COALESCE(ls.jumlah_stok, 0)
                            ELSE 0
                        END
                    ) AS total_stok
                FROM tb_logistik_stok ls
                INNER JOIN tb_master_logistik_sumber_material sm
                    ON sm.id_sumber_material = ls.id_sumber_material
                WHERE ls.id_lokasi_gudang = ?
                GROUP BY ls.id_kode_item
            ) stok
                ON stok.id_kode_item = ki.id_kode_item
            {$projectSql}
            ORDER BY ki.nama_item ASC
        ", $params)->result_array();
    }

    public function tambahReportStokLogistik($data_array)
    {
        $res = $this->db->insert("tb_logistik_stok", $data_array);
        return $res;
    }

    public function getDetailAreaBySJ($no_surat_jalan)
    {
        $data = $this->db->query('SELECT * FROM `tb_logistik_stok` JOIN tb_master_logistik_lokasi_gudang ON tb_logistik_stok.id_lokasi_gudang = tb_master_logistik_lokasi_gudang.id_lokasi_gudang
	                                    JOIN tb_master_bowheer ON tb_logistik_stok.id_bowheer = tb_master_bowheer.id_bowheer
                                        JOIN tb_master_logistik_sumber_material ON tb_logistik_stok.id_sumber_material = tb_master_logistik_sumber_material.id_sumber_material
                                        JOIN tb_master_logistik_kode_item ON tb_logistik_stok.id_kode_item = tb_master_logistik_kode_item.id_kode_item
                                        JOIN tb_master_user ON tb_logistik_stok.id_user = tb_master_user.id_user
                                        WHERE surat_jalan = "' . $no_surat_jalan . '"
                                        ORDER BY id_logistik_stok DESC
                                        ')->result_array();

        log_message('error', 'query asdasdasdasd : ' . $this->db->last_query());
        return $data;
    }

    public function getOpenPengirimanPabrikBySjGudang($noSuratJalan, $idLokasiGudang)
    {
        return $this->db->query("
            SELECT
                gp.id_pengiriman_pabrik,
                gp.id_pesanan_pabrik,
                gp.nomor_po_pabrik,
                gp.no_surat_jalan,
                gp.id_lokasi_gudang,
                gp.tanggal_pengiriman_pabrik,
                gp.surat_jalan_pabrik,
                gp.surat_jalan_ho,
                gd.id_pengiriman_pabrik_detail,
                gd.id_pesanan_pabrik_detail,
                pd.id_kode_item,
                pd.id_purchase_request_detail,
                pd.qty_item AS qty_kirim,
                COALESCE(gd.qty_diterima, 0) AS qty_diterima,
                GREATEST(pd.qty_item - COALESCE(gd.qty_diterima, 0), 0) AS qty_outstanding_terima,
                pd.harga_item,
                ki.nama_item,
                ki.satuan_item,
                ki.kategori_item,
                ki.id_bowheer_pemilik_item,
                pr.nomor_purchase_request
            FROM tb_logistik_pengiriman_pabrik gp
            INNER JOIN tb_logistik_pengiriman_pabrik_detail gd
                ON gd.id_pengiriman_pabrik = gp.id_pengiriman_pabrik
            INNER JOIN tb_logistik_pesanan_pabrik_detail pd
                ON pd.id_pesanan_pabrik_detail = gd.id_pesanan_pabrik_detail
            LEFT JOIN tb_master_logistik_kode_item ki
                ON ki.id_kode_item = pd.id_kode_item
            LEFT JOIN tb_logistik_purchase_request_detail prd
                ON prd.id_purchase_request_detail = pd.id_purchase_request_detail
            LEFT JOIN tb_logistik_purchase_request pr
                ON pr.id_purchase_request = prd.id_purchase_request
            WHERE gp.no_surat_jalan = ?
              AND gp.id_lokasi_gudang = ?
            ORDER BY ki.nama_item ASC, gd.id_pengiriman_pabrik_detail ASC
        ", [$noSuratJalan, $idLokasiGudang])->result_array();
    }

    public function getOpenPengirimanPabrikOptionsByGudang($idLokasiGudang): array
    {
        return $this->db->query("
            SELECT
                gp.no_surat_jalan,
                MAX(gp.tanggal_pengiriman_pabrik) AS tanggal_pengiriman,
                tujuan.kota_lokasi_gudang AS kota_gudang_tujuan,
                GROUP_CONCAT(DISTINCT NULLIF(gp.nomor_po_pabrik, '') ORDER BY gp.nomor_po_pabrik SEPARATOR ', ') AS po_refs,
                SUM(GREATEST(pd.qty_item - COALESCE(gd.qty_diterima, 0), 0)) AS total_outstanding
            FROM tb_logistik_pengiriman_pabrik gp
            INNER JOIN tb_logistik_pengiriman_pabrik_detail gd
                ON gd.id_pengiriman_pabrik = gp.id_pengiriman_pabrik
            INNER JOIN tb_logistik_pesanan_pabrik_detail pd
                ON pd.id_pesanan_pabrik_detail = gd.id_pesanan_pabrik_detail
            LEFT JOIN tb_master_logistik_lokasi_gudang tujuan
                ON tujuan.id_lokasi_gudang = gp.id_lokasi_gudang
            WHERE gp.id_lokasi_gudang = ?
            GROUP BY gp.no_surat_jalan, tujuan.kota_lokasi_gudang
            HAVING SUM(GREATEST(pd.qty_item - COALESCE(gd.qty_diterima, 0), 0)) > 0
            ORDER BY MAX(gp.tanggal_pengiriman_pabrik) DESC, gp.no_surat_jalan DESC
        ", [(int) $idLokasiGudang])->result_array();
    }

    public function getOpenPengirimanHoBySjGudang($noSuratJalan, $idLokasiGudang)
    {
        return $this->db->query("
            SELECT
                s.id_logistik_stok AS id_stock_pengiriman,
                s.no_surat_jalan,
                s.id_lokasi_gudang AS id_lokasi_gudang_asal,
                s.id_lokasi_gudang_pengiriman,
                s.id_kode_item,
                s.id_bowheer,
                s.jumlah_stok AS qty_kirim,
                COALESCE(r.qty_diterima, 0) AS qty_diterima,
                GREATEST(s.jumlah_stok - COALESCE(r.qty_diterima, 0), 0) AS qty_outstanding_terima,
                s.satuan_stok,
                s.merk_stok,
                s.no_haspel_stok,
                s.no_ref_stok,
                s.no_po_logistik,
                s.no_pr_logistik,
                ki.nama_item,
                ki.id_bowheer_pemilik_item,
                asal.kota_lokasi_gudang AS kota_gudang_asal
            FROM tb_logistik_stok s
            INNER JOIN tb_master_logistik_sumber_material sm
                ON sm.id_sumber_material = s.id_sumber_material
            LEFT JOIN tb_master_logistik_lokasi_gudang asal
                ON asal.id_lokasi_gudang = s.id_lokasi_gudang
            LEFT JOIN tb_master_logistik_kode_item ki
                ON ki.id_kode_item = s.id_kode_item
            LEFT JOIN (
                SELECT
                    no_surat_jalan,
                    id_lokasi_gudang,
                    id_kode_item,
                    COALESCE(NULLIF(no_haspel_stok, ''), '-') AS no_haspel_key,
                    COALESCE(NULLIF(no_ref_stok, ''), '-') AS no_ref_key,
                    SUM(jumlah_stok) AS qty_diterima
                FROM tb_logistik_stok
                WHERE id_sumber_material = 1
                GROUP BY
                    no_surat_jalan,
                    id_lokasi_gudang,
                    id_kode_item,
                    COALESCE(NULLIF(no_haspel_stok, ''), '-'),
                    COALESCE(NULLIF(no_ref_stok, ''), '-')
            ) r
                ON r.no_surat_jalan = s.no_surat_jalan
                AND r.id_lokasi_gudang = s.id_lokasi_gudang_pengiriman
                AND r.id_kode_item = s.id_kode_item
                AND r.no_haspel_key = COALESCE(NULLIF(s.no_haspel_stok, ''), '-')
                AND r.no_ref_key = COALESCE(NULLIF(s.no_ref_stok, ''), '-')
            WHERE s.no_surat_jalan = ?
              AND s.id_lokasi_gudang_pengiriman = ?
              AND sm.status_sumber_material = 'OUT'
              AND UPPER(COALESCE(asal.kota_lokasi_gudang, '')) = 'HO'
            ORDER BY ki.nama_item ASC, s.id_logistik_stok ASC
        ", [$noSuratJalan, $idLokasiGudang])->result_array();
    }

    public function getOpenPengirimanHoOptionsByGudang($idLokasiGudang): array
    {
        return $this->db->query("
            SELECT
                s.no_surat_jalan,
                MAX(s.tanggal_upload_stok) AS tanggal_pengiriman,
                asal.kota_lokasi_gudang AS kota_gudang_asal,
                tujuan.kota_lokasi_gudang AS kota_gudang_tujuan,
                GROUP_CONCAT(DISTINCT NULLIF(s.no_po_logistik, '') ORDER BY s.no_po_logistik SEPARATOR ', ') AS po_refs,
                SUM(GREATEST(s.jumlah_stok - COALESCE(r.qty_diterima, 0), 0)) AS total_outstanding
            FROM tb_logistik_stok s
            INNER JOIN tb_master_logistik_sumber_material sm
                ON sm.id_sumber_material = s.id_sumber_material
            LEFT JOIN tb_master_logistik_lokasi_gudang asal
                ON asal.id_lokasi_gudang = s.id_lokasi_gudang
            LEFT JOIN tb_master_logistik_lokasi_gudang tujuan
                ON tujuan.id_lokasi_gudang = s.id_lokasi_gudang_pengiriman
            LEFT JOIN (
                SELECT
                    no_surat_jalan,
                    id_lokasi_gudang,
                    id_kode_item,
                    COALESCE(NULLIF(no_haspel_stok, ''), '-') AS no_haspel_key,
                    COALESCE(NULLIF(no_ref_stok, ''), '-') AS no_ref_key,
                    SUM(jumlah_stok) AS qty_diterima
                FROM tb_logistik_stok
                WHERE id_sumber_material = 1
                GROUP BY
                    no_surat_jalan,
                    id_lokasi_gudang,
                    id_kode_item,
                    COALESCE(NULLIF(no_haspel_stok, ''), '-'),
                    COALESCE(NULLIF(no_ref_stok, ''), '-')
            ) r
                ON r.no_surat_jalan = s.no_surat_jalan
                AND r.id_lokasi_gudang = s.id_lokasi_gudang_pengiriman
                AND r.id_kode_item = s.id_kode_item
                AND r.no_haspel_key = COALESCE(NULLIF(s.no_haspel_stok, ''), '-')
                AND r.no_ref_key = COALESCE(NULLIF(s.no_ref_stok, ''), '-')
            WHERE s.id_lokasi_gudang_pengiriman = ?
              AND sm.status_sumber_material = 'OUT'
              AND UPPER(COALESCE(asal.kota_lokasi_gudang, '')) = 'HO'
            GROUP BY
                s.no_surat_jalan,
                asal.kota_lokasi_gudang,
                tujuan.kota_lokasi_gudang
            HAVING SUM(GREATEST(s.jumlah_stok - COALESCE(r.qty_diterima, 0), 0)) > 0
            ORDER BY MAX(s.tanggal_upload_stok) DESC, s.no_surat_jalan DESC
        ", [(int) $idLokasiGudang])->result_array();
    }

    public function getOpenPeminjamanMitraBySjGudang($noSuratJalan, $idLokasiGudang)
    {
        return $this->db->query("
            SELECT
                s.id_logistik_stok AS id_stock_peminjaman,
                s.no_surat_jalan,
                s.id_lokasi_gudang,
                s.id_kode_item,
                s.id_bowheer,
                s.jumlah_stok AS qty_peminjaman,
                COALESCE(r.qty_dikembalikan, 0) AS qty_dikembalikan,
                GREATEST(s.jumlah_stok - COALESCE(r.qty_dikembalikan, 0), 0) AS qty_outstanding_pengembalian,
                s.satuan_stok,
                s.merk_stok,
                s.no_haspel_stok,
                s.no_ref_stok,
                ki.nama_item,
                ki.id_bowheer_pemilik_item,
                b.nama_bowheer
            FROM tb_logistik_stok s
            INNER JOIN tb_master_logistik_sumber_material sm
                ON sm.id_sumber_material = s.id_sumber_material
            LEFT JOIN tb_master_logistik_kode_item ki
                ON ki.id_kode_item = s.id_kode_item
            LEFT JOIN tb_master_bowheer b
                ON b.id_bowheer = s.id_bowheer
            LEFT JOIN (
                SELECT
                    COALESCE(r.id_logistik_stok_asal, 0) AS id_logistik_stok_asal,
                    SUM(COALESCE(st.jumlah_stok, 0)) AS qty_dikembalikan
                FROM tb_logistik_stok st
                INNER JOIN tb_logistik_stok_rincian r
                    ON r.id_logistik_stok = st.id_logistik_stok
                WHERE st.id_sumber_material = 12
                GROUP BY COALESCE(r.id_logistik_stok_asal, 0)
            ) r
                ON r.id_logistik_stok_asal = s.id_logistik_stok
            WHERE s.no_surat_jalan = ?
              AND s.id_lokasi_gudang = ?
              AND s.id_sumber_material = 4
            ORDER BY ki.nama_item ASC, s.id_logistik_stok ASC
        ", [$noSuratJalan, (int) $idLokasiGudang])->result_array();
    }

    public function getOpenPeminjamanMitraOptionsByGudang($idLokasiGudang): array
    {
        return $this->db->query("
            SELECT
                s.no_surat_jalan,
                MAX(s.tanggal_upload_stok) AS tanggal_peminjaman,
                MAX(b.nama_bowheer) AS nama_bowheer,
                MAX(rin.nama_mitra) AS nama_mitra,
                SUM(GREATEST(s.jumlah_stok - COALESCE(r.qty_dikembalikan, 0), 0)) AS total_outstanding
            FROM tb_logistik_stok s
            INNER JOIN tb_master_logistik_sumber_material sm
                ON sm.id_sumber_material = s.id_sumber_material
            LEFT JOIN tb_master_bowheer b
                ON b.id_bowheer = s.id_bowheer
            LEFT JOIN tb_logistik_stok_rincian rin
                ON rin.id_logistik_stok = s.id_logistik_stok
            LEFT JOIN (
                SELECT
                    COALESCE(r.id_logistik_stok_asal, 0) AS id_logistik_stok_asal,
                    SUM(COALESCE(st.jumlah_stok, 0)) AS qty_dikembalikan
                FROM tb_logistik_stok st
                INNER JOIN tb_logistik_stok_rincian r
                    ON r.id_logistik_stok = st.id_logistik_stok
                WHERE st.id_sumber_material = 12
                GROUP BY COALESCE(r.id_logistik_stok_asal, 0)
            ) r
                ON r.id_logistik_stok_asal = s.id_logistik_stok
            WHERE s.id_lokasi_gudang = ?
              AND s.id_sumber_material = 4
            GROUP BY s.no_surat_jalan
            HAVING SUM(GREATEST(s.jumlah_stok - COALESCE(r.qty_dikembalikan, 0), 0)) > 0
            ORDER BY MAX(s.tanggal_upload_stok) DESC, s.no_surat_jalan DESC
        ", [(int) $idLokasiGudang])->result_array();
    }

    public function getAllStokByKategoryFilterCityFiltered($tanggal, $statusArray = null, $lokasiArray = null, $bowheerArray = null, $itemArray = null)
    {
        $sql = "SELECT 
    lg.regional_lokasi_gudang,
    lg.kota_lokasi_gudang,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Aksesories' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Aksesories,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Closure'
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Closure,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item IN ('FAT / ODP', 'FAT') 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FAT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item IN ('FDT | ODC', 'FDT') 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FDT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'HDPE' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_HDPE,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Kabel' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Kabel,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'OTB' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_OTB,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Tiang' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Tiang
FROM 
    tb_master_logistik_lokasi_gudang lg
LEFT JOIN 
    tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
LEFT JOIN 
    tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
LEFT JOIN 
    tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
            WHERE 1=1"; // Awal WHERE agar bisa ditambahkan kondisi

        // Tambahkan filter lokasi
        if (!empty($tanggal)) {
            $sql .= " AND ls.tanggal_upload_stok <= '$tanggal 23:59:59'";
        }

        if (!empty($statusArray)) {
            $sql .= " AND sm.nama_sumber_material IN ($statusArray)";
        }

        if (!empty($lokasiArray)) {
            $sql .= " AND lg.id_lokasi_gudang IN ($lokasiArray)";
        }

        if (!empty($bowheerArray)) {
            $sql .= " AND ki.project_item IN ($bowheerArray)";
        }

        if (!empty($itemArray)) {
            $sql .= " AND ki.nama_item IN ($itemArray)";
        }

        // Tambahkan GROUP BY & ORDER BY
        $sql .= " GROUP BY 
    lg.kota_lokasi_gudang
ORDER BY 
    lg.regional_lokasi_gudang, lg.kota_lokasi_gudang;";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query filter city dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getDashboardFiltered($lokasiArray, $bowheerArray, $itemArray, $tanggal, $statusArray = null)
    {
        $sql = "SELECT 
                lg.regional_lokasi_gudang,
                lg.kota_lokasi_gudang,
                ki.nama_item,
    ki.project_item,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Aksesories' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Aksesories,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Closure'
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Closure,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item IN ('FAT / ODP', 'FAT') 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FAT,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item IN ('FDT | ODC', 'FDT') 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FDT,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'HDPE' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_HDPE,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Kabel' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Kabel,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'OTB' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_OTB,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Tiang' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Tiang
            FROM 
                tb_master_logistik_lokasi_gudang lg
            LEFT JOIN 
                tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
            LEFT JOIN 
                tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
            LEFT JOIN 
                tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
            WHERE 1=1"; // Awal WHERE agar bisa ditambahkan kondisi

        // Tambahkan filter lokasi
        if (!empty($lokasiArray)) {
            $sql .= " AND lg.id_lokasi_gudang IN ($lokasiArray)";
        }

        if (!empty($bowheerArray)) {
            $sql .= " AND ki.project_item IN ($bowheerArray)";
        }

        if (!empty($itemArray)) {
            $sql .= " AND ki.nama_item IN ($itemArray)";
        }

        if (!empty($statusArray)) {
            $sql .= " AND sm.nama_sumber_material IN ($statusArray)";
        }

        if (!empty($tanggal)) {
            $sql .= " AND ls.tanggal_upload_stok <= '$tanggal 23:59:59'";
        }

        // Tambahkan GROUP BY & ORDER BY
        $sql .= " GROUP BY lg.kota_lokasi_gudang";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getRincianDashboardFiltered($lokasiArray, $bowheerArray, $itemArray, $tanggal, $statusArray = null)
    {

        $sql = "SELECT 
    ROW_NUMBER() OVER () AS nomor,
    lg.regional_lokasi_gudang,
    lg.kota_lokasi_gudang,
    ki.kategori_item, 
    ki.nama_item, 
    ki.project_item,
    tmb.nama_bowheer,
    COALESCE(SUM(
        CASE 
            WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
            WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
            ELSE 0 
        END
    ), '-') AS jumlah_stok,
    ki.satuan_item
FROM tb_master_logistik_lokasi_gudang lg
CROSS JOIN tb_master_logistik_kode_item ki
LEFT JOIN tb_master_bowheer tmb 
	ON ki.id_bowheer_pemilik_item = tmb.id_bowheer
LEFT JOIN tb_logistik_stok ls 
    ON lg.id_lokasi_gudang = ls.id_lokasi_gudang 
    AND ki.id_kode_item = ls.id_kode_item
LEFT JOIN tb_master_logistik_sumber_material sm 
    ON ls.id_sumber_material = sm.id_sumber_material
    WHERE 1=1 && jumlah_stok != '0'";

        if (!empty($lokasiArray)) {
            $sql .= " AND lg.id_lokasi_gudang IN ($lokasiArray)";
        }

        if (!empty($bowheerArray)) {
            $sql .= " AND ki.project_item IN ($bowheerArray)";
        }

        if (!empty($itemArray)) {
            $sql .= " AND ki.nama_item IN ($itemArray)";
        }

        if (!empty($statusArray)) {
            $sql .= " AND sm.nama_sumber_material IN ($statusArray)";
        }

        if (!empty($tanggal)) {
            $sql .= " AND ls.tanggal_upload_stok <= '$tanggal 23:59:59'";
        }

        $sql .= " GROUP BY ki.nama_item, ki.project_item, lg.kota_lokasi_gudang
ORDER BY lg.regional_lokasi_gudang, lg.kota_lokasi_gudang";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query rincian dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getRincianDashboardFilteredBowheer($lokasiArray, $bowheerArray, $itemArray, $tanggal, $statusArray = null)
    {

        $sql = "SELECT 
    ROW_NUMBER() OVER () AS nomor,
    ki.kategori_item, 
    ki.nama_item, 
    ki.project_item,
    tmb.nama_bowheer,
    COALESCE(SUM(
        CASE 
            WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
            WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
            ELSE 0 
        END
    ), '-') AS jumlah_stok,
    ki.satuan_item
FROM tb_master_logistik_lokasi_gudang lg
CROSS JOIN tb_master_logistik_kode_item ki
LEFT JOIN tb_master_bowheer tmb 
	ON ki.id_bowheer_pemilik_item = tmb.id_bowheer
LEFT JOIN tb_logistik_stok ls 
    ON lg.id_lokasi_gudang = ls.id_lokasi_gudang 
    AND ki.id_kode_item = ls.id_kode_item
LEFT JOIN tb_master_logistik_sumber_material sm 
    ON ls.id_sumber_material = sm.id_sumber_material
    WHERE 1=1 && jumlah_stok != '0'";

        if (!empty($lokasiArray)) {
            $sql .= " AND lg.id_lokasi_gudang IN ($lokasiArray)";
        }

        if (!empty($bowheerArray)) {
            $sql .= " AND ki.project_item IN ($bowheerArray)";
        }

        if (!empty($itemArray)) {
            $sql .= " AND ki.nama_item IN ($itemArray)";
        }

        if (!empty($statusArray)) {
            $sql .= " AND sm.nama_sumber_material IN ($statusArray)";
        }

        if (!empty($tanggal)) {
            $sql .= " AND ls.tanggal_upload_stok <= '$tanggal 23:59:59'";
        }

        $sql .= " GROUP BY ki.kategori_item, ki.project_item
ORDER BY ki.kategori_item;";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query rincian2 dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getInOutHistoryFiltered($lokasiArray, $bowheerArray, $itemArray, $tanggal, $statusArray = null)
    {

        $sql = "SELECT * FROM `tb_logistik_stok` JOIN tb_master_logistik_lokasi_gudang ON tb_logistik_stok.id_lokasi_gudang = tb_master_logistik_lokasi_gudang.id_lokasi_gudang
	                                    JOIN tb_master_bowheer ON tb_logistik_stok.id_bowheer = tb_master_bowheer.id_bowheer
                                        JOIN tb_master_logistik_sumber_material ON tb_logistik_stok.id_sumber_material = tb_master_logistik_sumber_material.id_sumber_material
                                        JOIN tb_master_logistik_kode_item ON tb_logistik_stok.id_kode_item = tb_master_logistik_kode_item.id_kode_item
                                        JOIN tb_master_user ON tb_logistik_stok.id_user = tb_master_user.id_user WHERE 1=1";

        if (!empty($lokasiArray)) {
            $sql .= " AND tb_master_logistik_lokasi_gudang.id_lokasi_gudang IN ($lokasiArray)";
        }

        if (!empty($bowheerArray)) {
            $sql .= " AND project_item IN ($bowheerArray)";
        }

        if (!empty($itemArray)) {
            $sql .= " AND nama_item IN ($itemArray)";
        }

        if (!empty($statusArray)) {
            $sql .= " AND nama_sumber_material IN ($statusArray)";
        }

        if (!empty($tanggal)) {
            $sql .= " AND tanggal_upload_stok <= '$tanggal 23:59:59'";
        }

        $sql .= " ORDER by tanggal_upload_stok DESC";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query rincian2 dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getReportInOutMaterial()
    {
        $data = $this->db->query("SELECT 
    ROW_NUMBER() OVER (ORDER BY tls.id_logistik_stok DESC) AS nomor_urut,
    tls.id_logistik_stok,
    tmllg.regional_lokasi_gudang,
    tmllg.kota_lokasi_gudang,
    tls.no_surat_jalan,
    tls.jumlah_stok,
    tmllg2.kota_lokasi_gudang as kota_pengiriman,
    tmlki.kategori_item,
    tmlki.nama_item,
    tmlki.satuan_item,
    tmb.nama_bowheer,
    tls.merk_stok,
    tls.no_haspel_stok,
    tls.no_ref_stok,
    tls.keterangan_stok,
    tmlsm.nama_sumber_material,
    tmlsm.status_sumber_material,
    tls.tanggal_upload_stok,
    tmu.nama_user,
    tls.no_pr_logistik,
    tls.no_po_logistik
FROM tb_logistik_stok tls
LEFT JOIN tb_master_logistik_lokasi_gudang tmllg ON tls.id_lokasi_gudang = tmllg.id_lokasi_gudang
LEFT JOIN tb_master_logistik_lokasi_gudang tmllg2 ON tls.id_lokasi_gudang_pengiriman = tmllg2.id_lokasi_gudang
LEFT JOIN tb_master_bowheer tmb ON tls.id_bowheer = tmb.id_bowheer
LEFT JOIN tb_master_logistik_sumber_material tmlsm ON tls.id_sumber_material = tmlsm.id_sumber_material
LEFT JOIN tb_master_logistik_kode_item tmlki ON tls.id_kode_item = tmlki.id_kode_item
LEFT JOIN tb_master_user tmu ON tls.id_user = tmu.id_user
ORDER BY tls.id_logistik_stok DESC;")->result_array();

        return $data;
    }

public function getReportStokMaterial($dateStart = null)
{
    // Default filter tanggal menggunakan hari ini jika tidak diisi
    if (empty($dateStart)) {
        $dateStart = date('Y-m-d');
    } else {
        $dateStart = $dateStart;
    }

    $sql = "
        SELECT 
            ROW_NUMBER() OVER (ORDER BY tmllg.kota_lokasi_gudang ASC) AS nomor,
            tls.id_logistik_stok,
            tmllg.regional_lokasi_gudang,
            tmllg.kota_lokasi_gudang,
            tmlki.kategori_item,
            tmlki.nama_item,
            tmlki.satuan_item,
            tmb.nama_bowheer,
            tls.tanggal_upload_stok,
            SUM(
                CASE 
                    WHEN tmlsm.status_sumber_material = 'IN' THEN tls.jumlah_stok
                    WHEN tmlsm.status_sumber_material = 'OUT' THEN -tls.jumlah_stok
                    ELSE 0 
                END
            ) AS total_jumlah_stok
        FROM tb_logistik_stok tls 
        LEFT JOIN tb_master_logistik_sumber_material tmlsm USING(id_sumber_material)
        LEFT JOIN tb_master_logistik_kode_item tmlki USING(id_kode_item)
        LEFT JOIN tb_master_bowheer tmb USING(id_bowheer)  -- Perbaikan dari RIGHT JOIN ke LEFT JOIN
        LEFT JOIN tb_master_logistik_lokasi_gudang tmllg USING(id_lokasi_gudang)  -- Perbaikan dari RIGHT JOIN ke LEFT JOIN
        WHERE DATE(tls.tanggal_upload_stok) <= ?
        GROUP BY 
            tmlki.id_kode_item, 
            tmllg.kota_lokasi_gudang, 
            tmb.nama_bowheer, 
            tmlki.kategori_item
        HAVING total_jumlah_stok <> 0
        ORDER BY tmllg.kota_lokasi_gudang ASC
    ";

    
    $data = $this->db->query($sql, [$dateStart])->result_array();
    log_message('error', 'query download stok filter yang dijalankan : ' . $this->db->last_query());
    return $data;

}

    public function getTransitShipmentRows(): array
    {
        $rows = $this->db->query("
            SELECT
                'HO_TO_AREA' AS shipment_type,
                s.no_surat_jalan,
                s.tanggal_upload_stok AS tanggal_pengiriman,
                COALESCE(asal.kota_lokasi_gudang, 'HO') AS asal_gudang,
                COALESCE(tujuan.kota_lokasi_gudang, '-') AS tujuan_gudang,
                GROUP_CONCAT(DISTINCT COALESCE(ki.kategori_item, '-') ORDER BY ki.kategori_item SEPARATOR ', ') AS kategori_refs,
                GROUP_CONCAT(DISTINCT COALESCE(ki.nama_item, '-') ORDER BY ki.nama_item SEPARATOR ', ') AS item_refs,
                GROUP_CONCAT(DISTINCT COALESCE(b.nama_bowheer, '-') ORDER BY b.nama_bowheer SEPARATOR ', ') AS bowheer_refs,
                GROUP_CONCAT(DISTINCT COALESCE(s.no_pr_logistik, '-') ORDER BY s.no_pr_logistik SEPARATOR ', ') AS pr_refs,
                GROUP_CONCAT(DISTINCT COALESCE(s.no_po_logistik, '-') ORDER BY s.no_po_logistik SEPARATOR ', ') AS po_refs,
                SUM(COALESCE(s.jumlah_stok, 0)) AS total_qty_kirim,
                SUM(COALESCE(r.qty_diterima, 0)) AS total_qty_diterima,
                SUM(GREATEST(COALESCE(s.jumlah_stok, 0) - COALESCE(r.qty_diterima, 0), 0)) AS total_qty_outstanding
            FROM tb_logistik_stok s
            INNER JOIN tb_master_logistik_sumber_material sm
                ON sm.id_sumber_material = s.id_sumber_material
            LEFT JOIN tb_master_logistik_kode_item ki
                ON ki.id_kode_item = s.id_kode_item
            LEFT JOIN tb_master_bowheer b
                ON b.id_bowheer = s.id_bowheer
            LEFT JOIN tb_master_logistik_lokasi_gudang asal
                ON asal.id_lokasi_gudang = s.id_lokasi_gudang
            LEFT JOIN tb_master_logistik_lokasi_gudang tujuan
                ON tujuan.id_lokasi_gudang = s.id_lokasi_gudang_pengiriman
            LEFT JOIN (
                SELECT
                    no_surat_jalan,
                    id_lokasi_gudang,
                    id_kode_item,
                    COALESCE(NULLIF(no_haspel_stok, ''), '-') AS no_haspel_key,
                    COALESCE(NULLIF(no_ref_stok, ''), '-') AS no_ref_key,
                    SUM(jumlah_stok) AS qty_diterima
                FROM tb_logistik_stok
                WHERE id_sumber_material = 1
                GROUP BY
                    no_surat_jalan,
                    id_lokasi_gudang,
                    id_kode_item,
                    COALESCE(NULLIF(no_haspel_stok, ''), '-'),
                    COALESCE(NULLIF(no_ref_stok, ''), '-')
            ) r
                ON r.no_surat_jalan = s.no_surat_jalan
                AND r.id_lokasi_gudang = s.id_lokasi_gudang_pengiriman
                AND r.id_kode_item = s.id_kode_item
                AND r.no_haspel_key = COALESCE(NULLIF(s.no_haspel_stok, ''), '-')
                AND r.no_ref_key = COALESCE(NULLIF(s.no_ref_stok, ''), '-')
            WHERE s.id_sumber_material = 10
            GROUP BY
                s.no_surat_jalan,
                s.tanggal_upload_stok,
                asal.kota_lokasi_gudang,
                tujuan.kota_lokasi_gudang
        ")->result_array();

        foreach ($rows as &$row) {
            $qtyKirim = (float) ($row['total_qty_kirim'] ?? 0);
            $qtyDiterima = (float) ($row['total_qty_diterima'] ?? 0);
            $qtyOutstanding = (float) ($row['total_qty_outstanding'] ?? 0);

            if ($qtyOutstanding <= 0 && $qtyKirim > 0) {
                $status = 'FULL DELIVERED';
            } elseif ($qtyDiterima > 0) {
                $status = 'PARTIAL DELIVERED';
            } else {
                $status = 'PENGIRIMAN';
            }

            $row['status_pengiriman'] = $status;
        }
        unset($row);

        usort($rows, static function ($left, $right) {
            $leftTime = strtotime((string) ($left['tanggal_pengiriman'] ?? '')) ?: 0;
            $rightTime = strtotime((string) ($right['tanggal_pengiriman'] ?? '')) ?: 0;
            return $rightTime <=> $leftTime;
        });

        return $rows;
    }

    public function getTransitShipmentCategoryCards(): array
    {
        return $this->db->query("
            SELECT
                kategori_item,
                SUM(total_qty_outstanding) AS total_qty_outstanding,
                MAX(satuan_item) AS satuan_item
            FROM (
                SELECT
                    ki.kategori_item,
                    ki.satuan_item,
                    GREATEST(COALESCE(s.jumlah_stok, 0) - COALESCE(r.qty_diterima, 0), 0) AS total_qty_outstanding
                FROM tb_logistik_stok s
                INNER JOIN tb_master_logistik_sumber_material sm
                    ON sm.id_sumber_material = s.id_sumber_material
                LEFT JOIN tb_master_logistik_kode_item ki
                    ON ki.id_kode_item = s.id_kode_item
                LEFT JOIN (
                    SELECT
                        no_surat_jalan,
                        id_lokasi_gudang,
                        id_kode_item,
                        COALESCE(NULLIF(no_haspel_stok, ''), '-') AS no_haspel_key,
                        COALESCE(NULLIF(no_ref_stok, ''), '-') AS no_ref_key,
                        SUM(jumlah_stok) AS qty_diterima
                    FROM tb_logistik_stok
                    WHERE id_sumber_material = 1
                    GROUP BY
                        no_surat_jalan,
                        id_lokasi_gudang,
                        id_kode_item,
                        COALESCE(NULLIF(no_haspel_stok, ''), '-'),
                        COALESCE(NULLIF(no_ref_stok, ''), '-')
                ) r
                    ON r.no_surat_jalan = s.no_surat_jalan
                    AND r.id_lokasi_gudang = s.id_lokasi_gudang_pengiriman
                    AND r.id_kode_item = s.id_kode_item
                    AND r.no_haspel_key = COALESCE(NULLIF(s.no_haspel_stok, ''), '-')
                    AND r.no_ref_key = COALESCE(NULLIF(s.no_ref_stok, ''), '-')
                WHERE s.id_sumber_material = 10
            ) transit_rows
            WHERE total_qty_outstanding > 0
            GROUP BY kategori_item
            ORDER BY kategori_item ASC
        ")->result_array();
    }

    public function getHistoryShipmentCategoryCards(): array
    {
        return $this->db->query("
            SELECT
                kategori_item,
                SUM(total_qty_kirim) AS total_qty_kirim,
                MAX(satuan_item) AS satuan_item
            FROM (
                SELECT
                    ki.kategori_item,
                    ki.satuan_item,
                    COALESCE(s.jumlah_stok, 0) AS total_qty_kirim
                FROM tb_logistik_stok s
                INNER JOIN tb_master_logistik_sumber_material sm
                    ON sm.id_sumber_material = s.id_sumber_material
                LEFT JOIN tb_master_logistik_kode_item ki
                    ON ki.id_kode_item = s.id_kode_item
                WHERE s.id_sumber_material = 10
            ) history_rows
            GROUP BY kategori_item
            ORDER BY kategori_item ASC
        ")->result_array();
    }

    public function getHoShipmentDetailBySuratJalan($noSuratJalan): array
    {
        $namaEkspedisiSelect = $this->fieldExists('tb_logistik_stok', 'nama_ekspedisi')
            ? 's.nama_ekspedisi'
            : 'NULL';
        $picEkspedisiSelect = $this->fieldExists('tb_logistik_stok', 'pic_ekspedisi')
            ? 's.pic_ekspedisi'
            : 'NULL';
        $nomorPolisiJoin = '';
        $nomorPolisiSelect = 'NULL AS nomor_polisi';

        if ($this->relationExists('tb_logistik_stok_rincian')) {
            $nomorPolisiFields = $this->db->list_fields('tb_logistik_stok_rincian');
            $nomorPolisiJoin = "
            LEFT JOIN tb_logistik_stok_rincian rin
                ON rin.id_logistik_stok = s.id_logistik_stok";

            if (in_array('nomor_polisi', $nomorPolisiFields, true)) {
                $nomorPolisiSelect = 'rin.nomor_polisi';
            } elseif (in_array('no_polisi', $nomorPolisiFields, true)) {
                $nomorPolisiSelect = 'rin.no_polisi';
            }
        }

        return $this->db->query("
            SELECT
                s.no_surat_jalan,
                s.tanggal_upload_stok AS tanggal_pengiriman,
                COALESCE(asal.kota_lokasi_gudang, 'HO') AS asal_gudang,
                COALESCE(tujuan.kota_lokasi_gudang, '-') AS tujuan_gudang,
                s.no_po_logistik,
                s.no_pr_logistik,
                s.surat_jalan,
                s.evidence,
                {$namaEkspedisiSelect} AS nama_ekspedisi,
                {$picEkspedisiSelect} AS pic_ekspedisi,
                {$nomorPolisiSelect} AS nomor_polisi,
                ki.kategori_item,
                ki.nama_item,
                ki.satuan_item,
                b.nama_bowheer,
                s.merk_stok,
                s.no_haspel_stok,
                s.no_ref_stok,
                COALESCE(s.jumlah_stok, 0) AS qty_kirim,
                COALESCE(r.qty_diterima, 0) AS qty_diterima,
                GREATEST(COALESCE(s.jumlah_stok, 0) - COALESCE(r.qty_diterima, 0), 0) AS qty_outstanding,
                s.keterangan_stok
            FROM tb_logistik_stok s
            INNER JOIN tb_master_logistik_sumber_material sm
                ON sm.id_sumber_material = s.id_sumber_material
            LEFT JOIN tb_master_logistik_kode_item ki
                ON ki.id_kode_item = s.id_kode_item
            LEFT JOIN tb_master_bowheer b
                ON b.id_bowheer = s.id_bowheer
            LEFT JOIN tb_master_logistik_lokasi_gudang asal
                ON asal.id_lokasi_gudang = s.id_lokasi_gudang
            LEFT JOIN tb_master_logistik_lokasi_gudang tujuan
                ON tujuan.id_lokasi_gudang = s.id_lokasi_gudang_pengiriman
            {$nomorPolisiJoin}
            LEFT JOIN (
                SELECT
                    no_surat_jalan,
                    id_lokasi_gudang,
                    id_kode_item,
                    COALESCE(NULLIF(no_haspel_stok, ''), '-') AS no_haspel_key,
                    COALESCE(NULLIF(no_ref_stok, ''), '-') AS no_ref_key,
                    SUM(jumlah_stok) AS qty_diterima
                FROM tb_logistik_stok
                WHERE id_sumber_material = 1
                GROUP BY
                    no_surat_jalan,
                    id_lokasi_gudang,
                    id_kode_item,
                    COALESCE(NULLIF(no_haspel_stok, ''), '-'),
                    COALESCE(NULLIF(no_ref_stok, ''), '-')
            ) r
                ON r.no_surat_jalan = s.no_surat_jalan
                AND r.id_lokasi_gudang = s.id_lokasi_gudang_pengiriman
                AND r.id_kode_item = s.id_kode_item
                AND r.no_haspel_key = COALESCE(NULLIF(s.no_haspel_stok, ''), '-')
                AND r.no_ref_key = COALESCE(NULLIF(s.no_ref_stok, ''), '-')
            WHERE s.id_sumber_material = 10
              AND s.no_surat_jalan = ?
            ORDER BY ki.kategori_item ASC, ki.nama_item ASC, s.id_logistik_stok ASC
        ", [$noSuratJalan])->result_array();
    }

}

