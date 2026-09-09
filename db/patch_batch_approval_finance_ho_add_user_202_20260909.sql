-- Add temporary Finance HO user id 202 to every MyRep city mapping.
-- CityMapping role columns store NIK values, so this patch resolves NIK from tb_master_user_new.id = 202.

ALTER TABLE `tb_myrep_pic_mapping_city`
  ADD COLUMN IF NOT EXISTS `finance_ho` VARCHAR(255) NULL AFTER `sitac_ho`;

SET @finance_ho_user_id := 202;
SET @finance_ho_nik := (
  SELECT TRIM(COALESCE(`nik`, ''))
  FROM `tb_master_user_new`
  WHERE `id` = @finance_ho_user_id
  LIMIT 1
);

UPDATE `tb_myrep_pic_mapping_city`
SET
  `finance_ho` = CASE
    WHEN @finance_ho_nik = '' THEN `finance_ho`
    WHEN `finance_ho` IS NULL OR TRIM(`finance_ho`) = '' THEN @finance_ho_nik
    WHEN FIND_IN_SET(@finance_ho_nik, REPLACE(`finance_ho`, ' ', '')) > 0 THEN `finance_ho`
    ELSE CONCAT(TRIM(BOTH ',' FROM `finance_ho`), ',', @finance_ho_nik)
  END,
  `updated_at` = NOW()
WHERE `is_active` = 1;

INSERT INTO `tb_myrep_role_permission`
  (`page_key`, `action_key`, `role_key`, `is_allowed`, `is_active`, `effective_start`, `effective_end`, `created_at`, `updated_at`, `submitted_at`)
VALUES
  ('Batch_Approval_MyRep', 'VIEW', 'FINANCE_HO', 1, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'APPROVAL', 'FINANCE_HO', 1, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'TAMBAH', 'FINANCE_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'EDIT', 'FINANCE_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'HAPUS', 'FINANCE_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'APPROVAL_DAILY', 'FINANCE_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'APPROVAL_FOTO_COMPLY', 'FINANCE_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `is_allowed` = VALUES(`is_allowed`),
  `is_active` = 1,
  `updated_at` = NOW(),
  `submitted_at` = NOW();

SELECT
  @finance_ho_user_id AS finance_ho_user_id,
  @finance_ho_nik AS finance_ho_nik,
  COUNT(*) AS mapped_active_city_count
FROM `tb_myrep_pic_mapping_city`
WHERE `is_active` = 1
  AND @finance_ho_nik <> ''
  AND FIND_IN_SET(@finance_ho_nik, REPLACE(COALESCE(`finance_ho`, ''), ' ', '')) > 0;
