-- Urutan eksekusi import CSV -> staging -> distribusi ke tabel utama
-- 1) Pastikan struktur staging terbaru
SOURCE db/patch_stg_myrep_cutoff_import_full_add_po.sql;

-- 2) Import CSV ke staging permanen (tanpa LOCAL INFILE)
-- Karena LOCAL INFILE disabled (#4166), lakukan import CSV via tool client:
--   - phpMyAdmin: pilih tabel stg_myrep_cutoff_import_full -> Import -> CSV
--   - atau HeidiSQL / DBeaver import wizard ke tabel yang sama
-- Setelah import selesai, pastikan jumlah row > 0:
--   SELECT COUNT(*) AS total_staging_rows FROM stg_myrep_cutoff_import_full;
--
-- Optional jika server mengizinkan (bukan LOCAL):
-- TRUNCATE TABLE stg_myrep_cutoff_import_full;
-- LOAD DATA INFILE '/path/yang-diizinkan-server/file.csv'
-- INTO TABLE stg_myrep_cutoff_import_full
-- FIELDS TERMINATED BY ','
-- ENCLOSED BY '"'
-- LINES TERMINATED BY '\n'
-- IGNORE 1 LINES;

-- 3) Pre-check staging sebelum distribusi
SELECT COUNT(*) AS total_staging_rows
FROM stg_myrep_cutoff_import_full;

SELECT
  UPPER(TRIM(COALESCE(cutoff_group, ''))) AS cutoff_group,
  UPPER(TRIM(COALESCE(status_current, ''))) AS status_current,
  COUNT(*) AS total_rows
FROM stg_myrep_cutoff_import_full
GROUP BY UPPER(TRIM(COALESCE(cutoff_group, ''))), UPPER(TRIM(COALESCE(status_current, '')))
ORDER BY cutoff_group, status_current;

-- 4) Distribusikan ke tabel-tabel target (cluster, bak, valsal, batch, drm, rfs, po)
SOURCE db/execute_import_cutoff_myrep_from_staging.sql;

-- 5) Post-check hasil distribusi di aplikasi
SELECT status_current, COUNT(*) AS total_rows
FROM tb_myrep_cluster
GROUP BY status_current
ORDER BY FIELD(status_current, 'BAK', 'VALSAL', 'RELEASED', 'DRM', 'RFS', 'ATP', 'DONE', 'DRAFT'), status_current;
