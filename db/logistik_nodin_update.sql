/* =========================================================
   LOGISTIK NODIN UPDATE
   Tujuan:
   1. Menambahkan tahap Nota Dinas (NODIN) sebelum PO pabrik
   2. Menyimpan snapshot item usulan PO dari PR approved
   3. Menyediakan approval NODIN bertahap sampai direktur
   ========================================================= */

CREATE TABLE IF NOT EXISTS tb_logistik_nota_dinas_po (
    id_nota_dinas_po VARCHAR(20) NOT NULL,
    id_purchase_request VARCHAR(20) DEFAULT NULL,
    nomor_nota_dinas VARCHAR(100) NOT NULL,
    tanggal_nota_dinas DATE NOT NULL,
    ditujukan_kepada VARCHAR(255) DEFAULT NULL,
    dibuat_oleh INT(11) NOT NULL,
    tujuan_penerbitan_po TEXT NOT NULL,
    approved_manager_logistik TINYINT(1) NOT NULL DEFAULT 0,
    approved_purchasing TINYINT(1) NOT NULL DEFAULT 0,
    approved_gm_project TINYINT(1) NOT NULL DEFAULT 0,
    approved_gm_finance TINYINT(1) NOT NULL DEFAULT 0,
    approved_direktur TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_nota_dinas_po),
    KEY idx_nodin_pr (id_purchase_request),
    KEY idx_nodin_nomor (nomor_nota_dinas)
);

CREATE TABLE IF NOT EXISTS tb_logistik_nota_dinas_po_detail (
    id_nota_dinas_po_detail VARCHAR(20) NOT NULL,
    id_nota_dinas_po VARCHAR(20) NOT NULL,
    id_purchase_request_detail VARCHAR(20) NOT NULL,
    id_kode_item INT(11) NOT NULL,
    id_pabrik INT(11) DEFAULT NULL,
    kebutuhan_project DECIMAL(18,2) NOT NULL DEFAULT 0,
    outstanding_pr DECIMAL(18,2) NOT NULL DEFAULT 0,
    qty_po_nodin DECIMAL(18,2) NOT NULL DEFAULT 0,
    harga_satuan DECIMAL(18,2) NOT NULL DEFAULT 0,
    keterangan TEXT DEFAULT NULL,
    PRIMARY KEY (id_nota_dinas_po_detail),
    KEY idx_nodin_detail_header (id_nota_dinas_po),
    KEY idx_nodin_detail_pr (id_purchase_request_detail),
    KEY idx_nodin_detail_item (id_kode_item),
    KEY idx_nodin_detail_pabrik (id_pabrik)
);

/* Opsional foreign key, aktifkan jika struktur existing sudah konsisten
ALTER TABLE tb_logistik_nota_dinas_po
    ADD CONSTRAINT fk_nodin_pr
        FOREIGN KEY (id_purchase_request) REFERENCES tb_logistik_purchase_request (id_purchase_request);

ALTER TABLE tb_logistik_nota_dinas_po_detail
    ADD CONSTRAINT fk_nodin_detail_header
        FOREIGN KEY (id_nota_dinas_po) REFERENCES tb_logistik_nota_dinas_po (id_nota_dinas_po);

ALTER TABLE tb_logistik_nota_dinas_po_detail
    ADD CONSTRAINT fk_nodin_detail_pr
        FOREIGN KEY (id_purchase_request_detail) REFERENCES tb_logistik_purchase_request_detail (id_purchase_request_detail);
*/

/* =========================================================
   UPDATE untuk database existing agar mendukung 1 NODIN > 1 PR
   Jalankan bila tabel sudah terlanjur ada:

ALTER TABLE tb_logistik_nota_dinas_po
    MODIFY COLUMN id_purchase_request VARCHAR(20) NULL;

   Catatan:
   - Kolom header id_purchase_request dipertahankan sebagai referensi utama / legacy.
   - Relasi PR yang sebenarnya dibaca dari tb_logistik_nota_dinas_po_detail -> tb_logistik_purchase_request_detail.
   ========================================================= */
