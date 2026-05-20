-- Opsi 1 (Direkomendasikan): import cepat via LOAD DATA
-- Jalankan di client MySQL yang mengizinkan LOCAL INFILE.
-- Sesuaikan path file jika berbeda.

SET NAMES utf8mb4;
SET @old_sql_mode = @@sql_mode;
SET sql_mode = REPLACE(@@sql_mode, 'NO_ZERO_DATE', '');

LOAD DATA LOCAL INFILE 'D:/XAMPP/htdocs/DatabaseTKM/tb_logistik_stok_rapi.csv'
INTO TABLE tb_logistik_stok
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\r\n'
IGNORE 1 LINES
(
  no_surat_jalan,
  id_lokasi_gudang,
  id_bowheer,
  id_sumber_material,
  id_kode_item,
  jumlah_stok,
  satuan_stok,
  merk_stok,
  no_haspel_stok,
  no_ref_stok,
  keterangan_stok,
  tanggal_upload_stok,
  id_user,
  surat_jalan,
  evidence,
  no_pr_logistik,
  nomor_spk,
  no_po_logistik,
  id_lokasi_gudang_pengiriman,
  tanggal_estimasi_sampai,
  nama_ekspedisi,
  pic_ekspedisi,
  status_approve_sm,
  CREATED_AT,
  ref_module,
  ref_id,
  ref_detail_id,
  ref_number,
  is_system_generated,
  approved_by,
  approved_at,
  adjustment_reason
)
SET
  merk_stok = NULLIF(merk_stok, ''),
  no_haspel_stok = NULLIF(no_haspel_stok, ''),
  no_ref_stok = NULLIF(no_ref_stok, ''),
  keterangan_stok = NULLIF(keterangan_stok, ''),
  surat_jalan = NULLIF(surat_jalan, ''),
  evidence = NULLIF(evidence, ''),
  no_pr_logistik = NULLIF(no_pr_logistik, ''),
  nomor_spk = NULLIF(nomor_spk, ''),
  no_po_logistik = NULLIF(no_po_logistik, ''),
  id_lokasi_gudang_pengiriman = NULLIF(id_lokasi_gudang_pengiriman, ''),
  tanggal_estimasi_sampai = NULLIF(tanggal_estimasi_sampai, ''),
  nama_ekspedisi = NULLIF(nama_ekspedisi, ''),
  pic_ekspedisi = NULLIF(pic_ekspedisi, ''),
  ref_module = NULLIF(ref_module, ''),
  ref_id = NULLIF(ref_id, ''),
  ref_detail_id = NULLIF(ref_detail_id, ''),
  ref_number = NULLIF(ref_number, ''),
  approved_by = NULLIF(approved_by, ''),
  approved_at = NULLIF(approved_at, ''),
  adjustment_reason = NULLIF(adjustment_reason, '');

SET sql_mode = @old_sql_mode;

-- Verifikasi cepat setelah import
SELECT COUNT(*) AS total_rows_imported FROM tb_logistik_stok WHERE no_surat_jalan LIKE 'STOK_AWAL_%';

SELECT no_surat_jalan, COUNT(*) AS jumlah_baris, SUM(jumlah_stok) AS total_qty
FROM tb_logistik_stok
WHERE no_surat_jalan LIKE 'STOK_AWAL_%'
GROUP BY no_surat_jalan
ORDER BY no_surat_jalan;
