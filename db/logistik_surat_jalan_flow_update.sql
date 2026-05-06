/* =========================================================
   LOGISTIK SURAT JALAN FLOW UPDATE
   Tujuan:
   1. Menyiapkan generator nomor surat jalan per gudang, reset per tahun
   2. Menyimpan rule flow surat jalan per sumber material
   3. Menyimpan rincian dinamis per transaksi stok tanpa membebani keterangan
   4. Menyediakan field tambahan untuk detail dan export excel
   ========================================================= */

/* =========================================================
   1. HEADER TAMBAHAN DI TB_LOGISTIK_STOK
   - nomor_surat_jalan_year: tahun counter aktif
   - nomor_surat_jalan_seq : urutan per gudang per tahun
   - nomor_spk            : sudah dipakai untuk out project / retur project
   - estimasi / ekspedisi : dipakai untuk pengiriman HO / customer
   ========================================================= */
ALTER TABLE tb_logistik_stok
ADD COLUMN nomor_surat_jalan_year SMALLINT(4) NULL AFTER no_surat_jalan,
ADD COLUMN nomor_surat_jalan_seq INT(11) NULL AFTER nomor_surat_jalan_year,
ADD COLUMN nomor_spk VARCHAR(100) NULL AFTER no_pr_logistik,
ADD COLUMN tanggal_estimasi_sampai DATE NULL AFTER id_lokasi_gudang_pengiriman,
ADD COLUMN nama_ekspedisi VARCHAR(150) NULL AFTER tanggal_estimasi_sampai,
ADD COLUMN pic_ekspedisi VARCHAR(150) NULL AFTER nama_ekspedisi;

ALTER TABLE tb_logistik_stok
ADD INDEX idx_sj_gudang_tahun_seq (id_lokasi_gudang, nomor_surat_jalan_year, nomor_surat_jalan_seq),
ADD INDEX idx_stok_nomor_spk (nomor_spk),
ADD INDEX idx_stok_estimasi (tanggal_estimasi_sampai);

/* =========================================================
   2. COUNTER NOMOR SURAT JALAN PER GUDANG PER TAHUN
   - semua sumber material tetap memakai prefix "SJ"
   - urutan dimulai dari 001 per gudang
   - reset per tahun
   - sumber material tetap dibedakan dari field id_sumber_material / rule
   ========================================================= */
CREATE TABLE IF NOT EXISTS tb_logistik_surat_jalan_counter (
    id_surat_jalan_counter BIGINT(20) NOT NULL AUTO_INCREMENT,
    id_lokasi_gudang INT(11) NOT NULL,
    tahun_counter SMALLINT(4) NOT NULL,
    last_sequence INT(11) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_surat_jalan_counter),
    UNIQUE KEY uniq_sj_counter_gudang_tahun (id_lokasi_gudang, tahun_counter),
    KEY idx_sj_counter_tahun (tahun_counter)
);

/* =========================================================
   3. RULE FLOW SURAT JALAN PER SUMBER MATERIAL
   mode_surat_jalan:
   - AUTO                : nomor SJ auto generate
   - REFERENCE_DROPDOWN  : nomor SJ mengikuti SJ asal dari dropdown
   - REFERENCE_MANUAL    : nomor SJ mengikuti SJ asal dari input manual
   - AUTO_WITH_REFERENCE : nomor SJ auto generate, tapi tetap wajib referensi SJ/SPK asal
   ========================================================= */
CREATE TABLE IF NOT EXISTS tb_logistik_sumber_material_rule (
    id_sumber_material INT(11) NOT NULL,
    mode_surat_jalan ENUM('AUTO','REFERENCE_DROPDOWN','REFERENCE_MANUAL','AUTO_WITH_REFERENCE') NOT NULL,
    reference_mode ENUM('NONE','DROPDOWN','MANUAL') NOT NULL DEFAULT 'NONE',
    reference_sumber_material_ids VARCHAR(100) DEFAULT NULL,
    reset_counter_per_year TINYINT(1) NOT NULL DEFAULT 1,
    counter_per_gudang TINYINT(1) NOT NULL DEFAULT 1,
    require_nomor_spk TINYINT(1) NOT NULL DEFAULT 0,
    require_nomor_polisi TINYINT(1) NOT NULL DEFAULT 0,
    require_nama_mitra TINYINT(1) NOT NULL DEFAULT 0,
    require_pic_mitra TINYINT(1) NOT NULL DEFAULT 0,
    require_tanggal_estimasi TINYINT(1) NOT NULL DEFAULT 0,
    require_nama_ekspedisi TINYINT(1) NOT NULL DEFAULT 0,
    require_pic_ekspedisi TINYINT(1) NOT NULL DEFAULT 0,
    require_nomor_sj_asal TINYINT(1) NOT NULL DEFAULT 0,
    tampil_di_detail TINYINT(1) NOT NULL DEFAULT 1,
    tampil_di_excel TINYINT(1) NOT NULL DEFAULT 1,
    catatan_rule TEXT DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_sumber_material)
);

/* =========================================================
   4. RINCIAN DINAMIS PER TRANSAKSI STOK
   - tidak lagi ditaruh di keterangan_stok
   - bisa dipakai untuk detail halaman dan export excel
   - id_logistik_stok_asal dipakai jika referensinya berasal dari transaksi stok internal
   - nomor_surat_jalan_asal dipakai jika referensinya manual / dari email / luar sistem
   ========================================================= */
CREATE TABLE IF NOT EXISTS tb_logistik_stok_rincian (
    id_logistik_stok_rincian BIGINT(20) NOT NULL AUTO_INCREMENT,
    id_logistik_stok INT(11) NOT NULL,
    id_sumber_material INT(11) NOT NULL,
    id_logistik_stok_asal INT(11) DEFAULT NULL,
    id_sumber_material_asal INT(11) DEFAULT NULL,
    nomor_surat_jalan_asal VARCHAR(100) DEFAULT NULL,
    nomor_spk VARCHAR(100) DEFAULT NULL,
    nomor_polisi VARCHAR(50) DEFAULT NULL,
    nama_mitra VARCHAR(150) DEFAULT NULL,
    pic_mitra VARCHAR(150) DEFAULT NULL,
    tanggal_estimasi_sampai DATE DEFAULT NULL,
    nama_ekspedisi VARCHAR(150) DEFAULT NULL,
    pic_ekspedisi VARCHAR(150) DEFAULT NULL,
    catatan_rincian TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_logistik_stok_rincian),
    KEY idx_rincian_stok_header (id_logistik_stok),
    KEY idx_rincian_sumber_material (id_sumber_material),
    KEY idx_rincian_stok_asal (id_logistik_stok_asal),
    KEY idx_rincian_nomor_sj_asal (nomor_surat_jalan_asal),
    KEY idx_rincian_nomor_spk (nomor_spk)
);

/* =========================================================
   5. SEED RULE FLOW BERDASARKAN KEPUTUSAN BISNIS
   Catatan:
   - prefix tetap "SJ"
   - pembeda flow dibaca dari id_sumber_material
   - REFERENCE_DROPDOWN = wajib dropdown dan blok simpan jika kosong
   - REFERENCE_MANUAL   = wajib manual dan blok simpan jika kosong
   - AUTO_WITH_REFERENCE = auto generate, tapi tetap wajib referensi asal
   ========================================================= */
REPLACE INTO tb_logistik_sumber_material_rule (
    id_sumber_material,
    mode_surat_jalan,
    reference_mode,
    reference_sumber_material_ids,
    reset_counter_per_year,
    counter_per_gudang,
    require_nomor_spk,
    require_nomor_polisi,
    require_nama_mitra,
    require_pic_mitra,
    require_tanggal_estimasi,
    require_nama_ekspedisi,
    require_pic_ekspedisi,
    require_nomor_sj_asal,
    tampil_di_detail,
    tampil_di_excel,
    catatan_rule
) VALUES
    (1,  'REFERENCE_DROPDOWN', 'DROPDOWN', '10', 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 'In dari HO, SJ mengikuti pengiriman HO ke gudang tujuan'),
    (2,  'REFERENCE_DROPDOWN', 'DROPDOWN', '10', 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 'In transfer dari area lain, SJ mengikuti pengiriman antar gudang'),
    (3,  'AUTO',               'NONE',     NULL, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 'Pembelian area, SJ auto generate'),
    (4,  'REFERENCE_MANUAL',   'MANUAL',   NULL, 1, 1, 0, 1, 1, 1, 0, 0, 0, 1, 1, 1, 'Peminjaman dari mitra lain, SJ mengikuti dokumen luar/manual'),
    (5,  'AUTO',               'NONE',     NULL, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 'Pengembalian sisa project, SJ auto generate dan wajib nomor SPK'),
    (6,  'REFERENCE_DROPDOWN', 'DROPDOWN', '13', 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 'In dari customer, SJ mengikuti transaksi customer'),
    (7,  'REFERENCE_DROPDOWN', 'DROPDOWN', NULL, 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 'In dari pabrik, SJ mengikuti pengiriman pabrik'),
    (8,  'AUTO',               'NONE',     NULL, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 'Adjustment in, SJ auto generate'),
    (9,  'AUTO',               'NONE',     NULL, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 'Out ke project, SJ auto generate dan wajib nomor SPK'),
    (10, 'AUTO',               'NONE',     NULL, 1, 1, 0, 0, 0, 0, 1, 1, 1, 0, 1, 1, 'Transfer HO ke area lain, SJ auto generate dan wajib estimasi + ekspedisi'),
    (11, 'AUTO',               'NONE',     NULL, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 'Adjustment out, SJ khusus auto generate'),
    (12, 'AUTO_WITH_REFERENCE','DROPDOWN', '4',  1, 1, 0, 1, 1, 1, 0, 0, 0, 1, 1, 1, 'Pengembalian ke mitra lain, SJ auto generate dan wajib pilih referensi SJ peminjaman dari source 4'),
    (13, 'AUTO',               'NONE',     NULL, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 'Out ke customer, SJ auto generate'),
    (14, 'REFERENCE_MANUAL',   'MANUAL',   NULL, 1, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 'Out dari customer, SJ menyesuaikan dokumen luar/manual dan butuh data ekspedisi'),
    (15, 'AUTO',               'NONE',     NULL, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 'Stock opname, SJ khusus auto generate'),
    (16, 'AUTO',               'NONE',     NULL, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 'SO adjustment in, SJ khusus auto generate'),
    (17, 'AUTO',               'NONE',     NULL, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 'SO adjustment out, SJ khusus auto generate');

/* =========================================================
   6. OPTIONAL BACKFILL NILAI HEADER -> RINCIAN
   Jalankan hanya jika ingin memindahkan data existing yang sekarang masih
   tersimpan di header tb_logistik_stok ke tabel rincian baru.
   =========================================================

INSERT INTO tb_logistik_stok_rincian (
    id_logistik_stok,
    id_sumber_material,
    nomor_spk,
    tanggal_estimasi_sampai,
    nama_ekspedisi,
    pic_ekspedisi,
    created_at,
    updated_at
)
SELECT
    s.id_logistik_stok,
    s.id_sumber_material,
    s.nomor_spk,
    s.tanggal_estimasi_sampai,
    s.nama_ekspedisi,
    s.pic_ekspedisi,
    COALESCE(s.CREATED_AT, NOW()),
    NOW()
FROM tb_logistik_stok s
WHERE s.nomor_spk IS NOT NULL
   OR s.tanggal_estimasi_sampai IS NOT NULL
   OR s.nama_ekspedisi IS NOT NULL
   OR s.pic_ekspedisi IS NOT NULL;

   ========================================================= */

/* =========================================================
   7. CATATAN IMPLEMENTASI APLIKASI
   - generator nomor SJ membaca tb_logistik_surat_jalan_counter
   - validasi field dinamis membaca tb_logistik_sumber_material_rule
   - tampilan detail / excel membaca tb_logistik_stok_rincian
   - source 12 wajib cek outstanding qty dari source 4
   ========================================================= */

/* =========================================================
   8. QUERY UPDATE JIKA RULE SOURCE 12 SUDAH TERLANJUR MANUAL
   Jalankan jika data seed lama sudah masuk:
   =========================================================

UPDATE tb_logistik_sumber_material_rule
SET
    reference_mode = 'DROPDOWN',
    catatan_rule = 'Pengembalian ke mitra lain, SJ auto generate dan wajib pilih referensi SJ peminjaman dari source 4'
WHERE id_sumber_material = 12;

   ========================================================= */
