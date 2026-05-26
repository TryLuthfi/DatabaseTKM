-- Cleanup: reset override MyRep yang terlalu lebar di User Detail Access (Per Page)
-- Date   : 2026-05-26
--
-- Tujuan:
-- - Centang modul MyRepublik hanya menjadi akses dasar VIEW/TAMBAH.
-- - EDIT/HAPUS/APPROVAL kembali mengikuti SuperAdmin_MyRep_Config + SuperAdmin_MyRep_CityMapping.
-- - Override manual untuk action non-dasar dihapus dari tb_user_page_permission.
--
-- Catatan:
-- Jalankan SELECT audit dulu. Jika angkanya masuk akal, lanjutkan START TRANSACTION s/d COMMIT.

SELECT
  module_key,
  action_key,
  is_allowed,
  COUNT(*) AS total_override
FROM tb_user_page_permission
WHERE module_key = 'MyRepublik'
  AND action_key IN ('EDIT', 'HAPUS', 'APPROVAL', 'APPROVAL_DAILY', 'APPROVAL_FOTO_COMPLY')
  AND is_active = 1
GROUP BY module_key, action_key, is_allowed
ORDER BY module_key, action_key, is_allowed;

START TRANSACTION;

CREATE TABLE IF NOT EXISTS tb_user_page_permission_backup_20260526 AS
SELECT *
FROM tb_user_page_permission
WHERE module_key = 'MyRepublik'
  AND action_key IN ('EDIT', 'HAPUS', 'APPROVAL', 'APPROVAL_DAILY', 'APPROVAL_FOTO_COMPLY')
  AND is_active = 1;

DELETE FROM tb_user_page_permission
WHERE module_key = 'MyRepublik'
  AND action_key IN ('EDIT', 'HAPUS', 'APPROVAL', 'APPROVAL_DAILY', 'APPROVAL_FOTO_COMPLY')
  AND is_active = 1;

COMMIT;

-- Cek ulang: harus 0 untuk override MyRep non-dasar aktif.
SELECT
  module_key,
  action_key,
  is_allowed,
  COUNT(*) AS total_override
FROM tb_user_page_permission
WHERE module_key = 'MyRepublik'
  AND action_key IN ('EDIT', 'HAPUS', 'APPROVAL', 'APPROVAL_DAILY', 'APPROVAL_FOTO_COMPLY')
  AND is_active = 1
GROUP BY module_key, action_key, is_allowed
ORDER BY module_key, action_key, is_allowed;
