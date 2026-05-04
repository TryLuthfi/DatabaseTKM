/* =========================================================
   LOGISTIK PO UPDATE
   Tujuan:
   1. Menyambungkan PO dengan PR
   2. Mendukung partial delivery / outstanding
   3. Menambahkan status PO dan close manual
   4. Merapikan relasi pengiriman pabrik
   ========================================================= */

/* =========================================================
   1. HEADER PO
   ========================================================= */
ALTER TABLE tb_logistik_pesanan_pabrik
ADD COLUMN id_purchase_request VARCHAR(20) NULL AFTER id_pabrik,
ADD COLUMN nomor_purchase_request VARCHAR(100) NULL AFTER id_purchase_request,
ADD COLUMN status_po ENUM('DRAFT','SUBMITTED','APPROVED','PARTIAL_DELIVERY','COMPLETED','CLOSED','CANCELLED')
    NOT NULL DEFAULT 'DRAFT' AFTER purchase_order_document,
ADD COLUMN catatan_po TEXT NULL AFTER status_po,
ADD COLUMN closed_by VARCHAR(100) NULL AFTER catatan_po,
ADD COLUMN closed_at DATETIME NULL AFTER closed_by;

/* =========================================================
   2. DETAIL PO
   - qty_item adalah qty pesanan final
   - volume_planning_snapshot menyimpan acuan dari PR saat PO dibuat
   ========================================================= */
ALTER TABLE tb_logistik_pesanan_pabrik_detail
ADD COLUMN id_purchase_request_detail VARCHAR(20) NULL AFTER id_pesanan_pabrik,
ADD COLUMN volume_planning_snapshot INT(11) DEFAULT NULL AFTER id_purchase_request_detail,
ADD COLUMN qty_closed_manual INT(11) NOT NULL DEFAULT 0 AFTER qty_item,
ADD COLUMN alasan_close_detail TEXT NULL AFTER qty_closed_manual;

/* =========================================================
   3. HEADER PENGIRIMAN
   Schema lama belum punya relasi langsung ke PO header.
   Model sekarang juga mengasumsikan nomor_po_pabrik ada di sini.
   ========================================================= */
ALTER TABLE tb_logistik_pengiriman_pabrik
ADD COLUMN id_pesanan_pabrik VARCHAR(11) NULL AFTER id_pengiriman_pabrik,
ADD COLUMN nomor_po_pabrik VARCHAR(50) NULL AFTER id_pesanan_pabrik,
ADD COLUMN status_penerimaan ENUM('DRAFT','IN_TRANSIT','RECEIVED','CLOSED')
    NOT NULL DEFAULT 'DRAFT' AFTER surat_jalan_ho,
ADD COLUMN catatan_pengiriman TEXT NULL AFTER status_penerimaan;

/* =========================================================
   4. DETAIL PENGIRIMAN
   Tambah penerimaan real jika nanti qty kirim != qty terima
   ========================================================= */
ALTER TABLE tb_logistik_pengiriman_pabrik_detail
ADD COLUMN qty_diterima INT(11) DEFAULT NULL AFTER qty_item;

/* =========================================================
   5. BACKFILL RELASI HEADER PENGIRIMAN -> PO
   Mengisi id_pesanan_pabrik dan nomor_po_pabrik dari detail pengiriman
   ========================================================= */
UPDATE tb_logistik_pengiriman_pabrik tp
JOIN (
    SELECT
        tpd.id_pengiriman_pabrik,
        MAX(tppd.id_pesanan_pabrik) AS id_pesanan_pabrik,
        MAX(tpp.nomor_po_pabrik) AS nomor_po_pabrik
    FROM tb_logistik_pengiriman_pabrik_detail tpd
    LEFT JOIN tb_logistik_pesanan_pabrik_detail tppd
        ON tppd.id_pesanan_pabrik_detail = tpd.id_pesanan_pabrik_detail
    LEFT JOIN tb_logistik_pesanan_pabrik tpp
        ON tpp.id_pesanan_pabrik = tppd.id_pesanan_pabrik
    GROUP BY tpd.id_pengiriman_pabrik
) src
    ON src.id_pengiriman_pabrik = tp.id_pengiriman_pabrik
SET
    tp.id_pesanan_pabrik = src.id_pesanan_pabrik,
    tp.nomor_po_pabrik = src.nomor_po_pabrik
WHERE tp.id_pesanan_pabrik IS NULL
   OR tp.nomor_po_pabrik IS NULL;

/* qty_diterima awal = qty_item lama */
UPDATE tb_logistik_pengiriman_pabrik_detail
SET qty_diterima = qty_item
WHERE qty_diterima IS NULL;

/* status default untuk PO lama */
UPDATE tb_logistik_pesanan_pabrik
SET status_po = 'APPROVED'
WHERE status_po = 'DRAFT';

/* status default untuk pengiriman lama */
UPDATE tb_logistik_pengiriman_pabrik
SET status_penerimaan = 'RECEIVED'
WHERE status_penerimaan = 'DRAFT';

/* =========================================================
   6. INDEX PENDUKUNG
   ========================================================= */
ALTER TABLE tb_logistik_pesanan_pabrik
ADD INDEX idx_po_nomor (nomor_po_pabrik),
ADD INDEX idx_po_pr (id_purchase_request),
ADD INDEX idx_po_status (status_po);

ALTER TABLE tb_logistik_pesanan_pabrik_detail
ADD INDEX idx_po_detail_header (id_pesanan_pabrik),
ADD INDEX idx_po_detail_pr (id_purchase_request_detail);

ALTER TABLE tb_logistik_pengiriman_pabrik
ADD INDEX idx_pengiriman_po (nomor_po_pabrik),
ADD INDEX idx_pengiriman_header_po (id_pesanan_pabrik);

ALTER TABLE tb_logistik_pengiriman_pabrik_detail
ADD INDEX idx_pengiriman_detail_header (id_pengiriman_pabrik),
ADD INDEX idx_pengiriman_detail_po (id_pesanan_pabrik_detail);

/* =========================================================
   7. VIEW MONITORING PO
   Query ini menggantikan pendekatan lama yang membaca qty dari header.
   Outstanding dihitung dari detail dan pengiriman.
   ========================================================= */
DROP VIEW IF EXISTS v_logistik_po_monitor;

CREATE VIEW v_logistik_po_monitor AS
SELECT
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
    mu.nama_user,
    d.id_pesanan_pabrik_detail,
    d.id_purchase_request_detail,
    d.id_kode_item,
    ki.nama_item,
    ki.satuan_item,
    d.harga_item,
    d.qty_item AS qty_po,
    d.volume_planning_snapshot,
    d.qty_closed_manual,
    COALESCE(SUM(gd.qty_item), 0) AS qty_terkirim,
    COALESCE(SUM(gd.qty_diterima), 0) AS qty_diterima,
    (d.qty_item - COALESCE(SUM(gd.qty_item), 0) - d.qty_closed_manual) AS outstanding_pengiriman,
    (d.qty_item - COALESCE(SUM(gd.qty_diterima), 0) - d.qty_closed_manual) AS outstanding_penerimaan,
    (d.qty_item * COALESCE(d.harga_item, 0)) AS total_nominal_detail
FROM tb_logistik_pesanan_pabrik p
LEFT JOIN tb_master_logistik_pabrik mp
    ON mp.id_pabrik = p.id_pabrik
LEFT JOIN tb_master_user mu
    ON mu.id_user = p.id_user
LEFT JOIN tb_logistik_pesanan_pabrik_detail d
    ON d.id_pesanan_pabrik = p.id_pesanan_pabrik
LEFT JOIN tb_master_logistik_kode_item ki
    ON ki.id_kode_item = d.id_kode_item
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
    mu.nama_user,
    d.id_pesanan_pabrik_detail,
    d.id_purchase_request_detail,
    d.id_kode_item,
    ki.nama_item,
    ki.satuan_item,
    d.harga_item,
    d.qty_item,
    d.volume_planning_snapshot,
    d.qty_closed_manual;

/* =========================================================
   8. VIEW DETAIL PENGIRIMAN PER PO
   ========================================================= */
DROP VIEW IF EXISTS v_logistik_po_delivery;

CREATE VIEW v_logistik_po_delivery AS
SELECT
    gp.id_pengiriman_pabrik,
    gp.id_pesanan_pabrik,
    gp.nomor_po_pabrik,
    gp.no_surat_jalan,
    gp.id_lokasi_gudang,
    lg.kota_lokasi_gudang,
    gp.tanggal_pengiriman_pabrik,
    gp.surat_jalan_pabrik,
    gp.surat_jalan_ho,
    gp.status_penerimaan,
    gp.id_user,
    gd.id_pengiriman_pabrik_detail,
    gd.id_pesanan_pabrik_detail,
    gd.qty_item AS qty_kirim,
    gd.qty_diterima
FROM tb_logistik_pengiriman_pabrik gp
LEFT JOIN tb_master_logistik_lokasi_gudang lg
    ON lg.id_lokasi_gudang = gp.id_lokasi_gudang
LEFT JOIN tb_logistik_pengiriman_pabrik_detail gd
    ON gd.id_pengiriman_pabrik = gp.id_pengiriman_pabrik;

/* =========================================================
   9. CONTOH QUERY MONITOR OUTSTANDING PO
   =========================================================
   SELECT
       nomor_po_pabrik,
       nama_pabrik,
       nama_item,
       qty_po,
       qty_terkirim,
       outstanding_pengiriman,
       total_nominal_detail,
       status_po
   FROM v_logistik_po_monitor
   ORDER BY tanggal_po_pabrik DESC, nomor_po_pabrik DESC;
   ========================================================= */
