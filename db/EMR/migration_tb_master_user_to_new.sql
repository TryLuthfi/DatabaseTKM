-- Migration helper: keep legacy code working while source of truth is tb_master_user_new
-- Date: 2026-05-20

-- 1) Optional backup legacy table before cutover
-- RENAME TABLE tb_master_user TO tb_master_user_legacy;

-- 2) Ensure legacy table name is free, then create compatibility VIEW
DROP VIEW IF EXISTS tb_master_user;

CREATE VIEW tb_master_user AS
SELECT
  n.id AS id_user,
  n.nama_karyawan AS nama_user,
  n.username_user,
  n.password_user,
  n.id_level,
  NULL AS id_jabatan,
  COALESCE(NULLIF(n.lokasi_kantor, ''), NULLIF(n.homebase, ''), 'HO') AS lokasi_user,
  NULL AS under_sm,
  NULL AS under_pm,
  n.status_user,
  n.telegram_user_id
FROM tb_master_user_new n;

-- 3) Quick checks
SELECT COUNT(*) AS total_legacy_shape FROM tb_master_user;
SELECT COUNT(*) AS total_new FROM tb_master_user_new;

-- 4) High-risk gaps (must refactor in app code)
-- - id_jabatan is NULL in compatibility view; old join to tb_jabatan will not work correctly.
-- - under_sm / under_pm are NULL; old hierarchy logic must be replaced.
-- - nama_user and lokasi_user are derived aliases from new columns.
